<?php

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;

use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Insert;

class MapcutController extends AbstractActionController {
    
    /**
     * Выполнить первый этап оптимизации: разбор деталей по заготовкам.
     * @return \Zend\View\Model\JsonModel
     */
    public function optimizePhase1Action() {
        $result = new JsonModel();
        
        $jobId = (int) $this->params()->fromQuery("job_id");
        
        $adapterPgsqlLincut = $this->getServiceLocator()->get("db/lincut");
        $adapterSqlsrvWcad = $this->getServiceLocator()->get("db/wcad");
        
        $tableJobOrders = new TableGateway("job_order", $adapterPgsqlLincut);
        $tableJobMaterials = new TableGateway("job_material", $adapterPgsqlLincut);
        $tableJobMaterialDetails = new TableGateway("job_material_detail", $adapterPgsqlLincut);
        
        // Чистим материалы и детали у выбранного сменного задания
        // Каскадно удаляются детали, закрепленные за каждым материалом
        
        $tableJobMaterials->delete(array("job_id" => $jobId));
        
        // Из основной базы программы берем список заказов
        
        $selectJobOrders = $tableJobOrders->getSql()->select()->where(array("job_id" => $jobId));
        $jobOrders = $tableJobOrders->selectWith($selectJobOrders)->toArray();
         
        // Цикл по заказам
        // Из базы WinCAD берем список деталей по всем заказам
         
        $where = $this->where($jobOrders, "Task.ID", "order_id", " or ");
        
        $sql = "
            select 
                WriteMater.ID as wcad_detail_id,
                WriteMater.SizeX as wcad_detail_length,
                WriteMater.nCount * Project.nCount as wcad_detail_count,
                Material.ID as wcad_material_id,
                Material.Length as wcad_material_length,
                cast(WriteMater.idColor as varchar) + 
                cast(WriteMater.idColorBase as varchar) + 
                cast(WriteMater.idColorExt as varchar) as wcad_material_color
            
            from Task 
                left join Project on Project.idTask=Task.id 
                left join WriteMater on WriteMater.idProject=Project.id
                left join Material on Material.id=WriteMater.idMaterial 
                left join MaterGroup on MaterGroup.ID = WriteMater.idMaterGroup
                left join Object on Object.ID = WriteMater.idObj
                left join Seg on Seg.ID = WriteMater.idSeg
                left join Profile on Profile.ID = WriteMater.idProfile
                left join ProfileGroup on ProfileGroup.ID = Profile.idProfileGroup
                left join v_ObjectProp as v_ObjectProp_Side on v_ObjectProp_Side.idObject = Object.id and v_ObjectProp_Side.Name = 'TypeOpen_Draw'
                left join v_ObjectProp as v_ObjectProp_Heigth on v_ObjectProp_Heigth.idObject = Object.id and v_ObjectProp_Heigth.Name = 'Handle_Height'
                left join Contur on Contur.id = Object.idContur
                left join Color as Color_IN on Color_IN.ID = WriteMater.idColor
                left join Color as Color_BASE on Color_BASE.ID = WriteMater.idColorBase
                left join Color as Color_OUT on Color_OUT.ID = WriteMater.idColorExt
            
            where MaterGroup.ID in (2, 18) and ($where)
        ";
         
        $detailsStatement = $adapterSqlsrvWcad->createStatement($sql);
        $resultSet = new ResultSet();
        $details = $resultSet->initialize($detailsStatement->execute())->toArray();
        
        // Адаптация типов данных
        // Длина деталей из базы приходит в виде строки, надо конвертировать в число
        
        foreach ($details as $index => $detail) {
            $details[$index]["wcad_detail_length"] = (float) $detail["wcad_detail_length"];
        }
        
        // Раскрываем каждую деталь по ее количеству
        // В итоге вместо одной детали появится столько, сколько записано в поле wcad_detail_count
        
        $_details = array();
        foreach ($details as $detail) {
            $count = (int) $detail["wcad_detail_count"];
            for ($i = 1; $i <= $count; $i++) {
                unset($detail["wcad_detail_count"]);
                $_details[] = $detail;
            }
        }	     
	     $details = $_details;
        
        // Группируем детали по материалам (заготовкам) и их раскраскам
        
	     $materials = array();
	     foreach ($details as $detail) {
	         $wcad_material_id = $detail["wcad_material_id"];
            $index = $wcad_material_id . $detail["wcad_material_color"]; // Детали группируем по раскраскам
	         if (!$materials[$index]) {
	             $materials[$index] = array(
                    "wcad_material_id" => $wcad_material_id,
                    "wcad_material_length" => $detail["wcad_material_length"],
	                 "details" => array()
	             );
	         }
            unset($detail["wcad_material_id"]);
            unset($detail["wcad_material_length"]);
	         $materials[$index]["details"][] = $detail;
	     }
        
        // Записываем в базу программы данные о материалах и деталей для раскроя
	     
        foreach ($materials as $index => $material) {
            $tableJobMaterials->insert(array(
                "job_id" => $jobId,
                "wcad_material_id" => $material["wcad_material_id"],
                "wcad_material_length" => $material["wcad_material_length"]
            ));
            $materialId = (int) $adapterPgsqlLincut->getDriver()->getConnection()->getLastGeneratedValue("job_material_id_seq");
            $materials[$index]["id"] = $materialId;
            foreach ($material["details"] as $detail) {
                $tableJobMaterialDetails->insert(array(
                    "job_material_id" => $materialId,
                    "wcad_detail_id" => $detail["wcad_detail_id"],
                    "wcad_detail_length" => $detail["wcad_detail_length"]
                ));
            }
        }
        
        // Возвращаем результат
        
        $data = array();
        foreach ($materials as $material) $data[] = $material["id"];
        
        $result->success = true;
        $result->data = $data;
        
        return $result;
    }
    
    private function where($sqlresult, $key2, $key, $cond) {
        $result = array();
        foreach ($sqlresult as $row) {
            $keyvalue = $row[$key];
            $result[] = "$key2 = $keyvalue";
        }
        $result = implode($cond, $result);
        return $result;
    }
    
    /**
     * Выполнить второй этап оптимизации: раскрой материала.
     * Запрос на раскрой конкретного материала по его номеру из базы данных db/lincut.
     * @return \Zend\View\Model\JsonModel
     */
    public function optimizePhase2Action() {
        $result = new JsonModel();
        $config = $this->getServiceLocator()->get("config");
        
        $materialId = (int) $this->params()->fromQuery("material_id");
        $materialCount = (int) $this->params()->fromQuery("material_count");
        
        $adapterPgsqlLincut = $this->getServiceLocator()->get("db/lincut");
        
        $tableJobMaterials = new TableGateway("job_material", $adapterPgsqlLincut);
        $tableJobMaterialDetails = new TableGateway("job_material_detail", $adapterPgsqlLincut);
        $tableMapOperations = new TableGateway("map_operation", $adapterPgsqlLincut);
        $tableMapOperationDetails = new TableGateway("map_operation_detail", $adapterPgsqlLincut);
        
        // Получаем материал, который надо раскроить
        
        $selectJobMaterial = $tableJobMaterials->getSql()->select()->where(array("id" => $materialId));
        $jobMaterial = $tableJobMaterials->selectWith($selectJobMaterial)->toArray();
        $jobMaterial = $jobMaterial[0];
        
        // Получаем детали
        
        $selectJobMaterialDetails = $tableJobMaterialDetails->getSql()->select()->where(array("job_material_id" => $materialId));
        $jobMaterialDetails = $tableJobMaterialDetails->selectWith($selectJobMaterialDetails)->toArray();
        
        $_jobMaterialDetails = array();
        foreach ($jobMaterialDetails as $jobMaterialDetail) {
            $_jobMaterialDetails[$jobMaterialDetail["id"]] = $jobMaterialDetail["wcad_detail_length"];
        }
        $jobMaterialDetails = $_jobMaterialDetails;
        
        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
        // Cutter
        // Запускаем процедуру раскроя
        
        $waste = $config["lincut"]["waste"]; // Отходы с краев хлыста
        $saw = $config["lincut"]["saw"]; // Ширина пила
        
        $jobMaterials = array(
            $materialId => $jobMaterial["wcad_material_length"] - $waste * 2
        );
        
        require_once 'cutter/cutter_v20.php';
        $cutter = new \Cutter();
        
        $cutter->SetStore($jobMaterials);
        $cutter->SetUnits($jobMaterialDetails);
        $cutter->SetCutterWidth($saw); 
        
        $restrictionMode = mb_strtolower($config["lincut"]["restriction"]["mode"]);
        
        if ($restrictionMode == "all" || $restrictionMode == "ontime") {
            $uptime = ((int) $config["lincut"]["restriction"]["uptime"]) / $materialCount;
            if ($uptime < 1) $uptime = 1;
            if ($uptime > 3) $uptime = 3;
            $cutter->SetLimitOnTime($uptime);
        }
        
        if ($restrictionMode == "all" || $restrictionMode == "oncount") {
            $upcount = (int) $config["lincut"]["restriction"]["upcount"];
            if ($uptime < 5000) $uptime = 5000;
            if ($uptime > 500000) $uptime = 500000;
            $cutter->SetLimitOnCount($uptime);
        }
        
        $cutter->FindTheBest();
        
        // Записываем в базу результат раскроя - список операций по данному материалу
        
        $connection = $adapterPgsqlLincut->getDriver()->getConnection();
        foreach ($cutter->a_best_store as $materialIndex => $materialLength) {
            $tableMapOperations->insert(array("job_material_id" => $materialId));
            $mapOperationId = (int) $connection->getLastGeneratedValue("map_operation_id_seq");
            foreach ($cutter->a_best_result[$materialIndex] as $detailId => $detailLength) {
                $tableMapOperationDetails->insert(array(
                    "map_operation_id" => $mapOperationId,
                    "job_material_detail_id" => $detailId
                ));
            }
        }
        
        // Cutter
        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
        
        $result->success = true;
        
        return $result;
    }
    
    /**
     * Показать карту раскроя в HTML-виде.
     * @return \Zend\View\Model\ViewModel
     */
    public function viewmapAction() {
        $result = new ViewModel();
        $this->layout("layout/viewmap");
        $config = $this->getServiceLocator()->get("config");
        
        $waste = $config["lincut"]["waste"]; // Отходы (сколько выбросить с одного конца хлыста)
        $saw = $config["lincut"]["saw"]; // Ширина пила
        
        $jobId = (int) $this->params()->fromQuery("job_id");
        $format = (string) $this->params()->fromQuery("format", "html");
        
        $this->layout()->format = $format;
        
        $adapterPgsqlLincut = $this->getServiceLocator()->get("db/lincut");
        $adapterSqlsrvWcad = $this->getServiceLocator()->get("db/wcad");
        
        // Получить список операций
        
        $sql = "
            select
                map_operation.id,
                job_material.wcad_material_id,
                job_material.wcad_material_length
            from map_operation
            left join job_material on job_material.id = map_operation.job_material_id
            left join job on job.id =  job_material.job_id
            where job.id = $jobId
        ";
        
        $operationsStatement = $adapterPgsqlLincut->createStatement($sql);
        $resultSet = new ResultSet();
        $operations = $resultSet->initialize($operationsStatement->execute())->toArray();
        
        // Получить список деталей для каждой операции в отдельности
        
        $whereDetails = null;
        
        foreach ($operations as $index => $operation) {
            
            // Получить детали по операции
            
            $operationId = $operation["id"];
            
            $sql = "
                select 
                    job_material_detail.wcad_detail_id, 
                    job_material_detail.wcad_detail_length
                from map_operation_detail
                left join job_material_detail on map_operation_detail.job_material_detail_id = job_material_detail.id
                where map_operation_detail.map_operation_id = $operationId
            ";
            
            $detailsStatement = $adapterPgsqlLincut->createStatement($sql);
            $resultSet = new ResultSet();
            $details = $resultSet->initialize($detailsStatement->execute())->toArray();
            
            // Расчет суммы длин деталей
            $lengthTotal = 0;
            foreach ($details as $detail) $lengthTotal += $detail["wcad_detail_length"];
            
            // Расчет суммы длин деталей + ширина пила между деталями
            $lengthTotalWithSaw = $lengthTotal + $saw * (count($details) - 1);
            
            // Расчет остатка
            $lengthTail = $operation["wcad_material_length"] - $waste * 2 - $lengthTotalWithSaw;
            $lengthTailPercent = round($lengthTail / $operation["wcad_material_length"] * 100, 1);
            
            // Запись данных
            $operations[$index]["detailLenghtTotal"] = $lengthTotal;
            $operations[$index]["detailLenghtTotalWithSaw"] = $lengthTotalWithSaw;
            $operations[$index]["detailLenghtTotalTail"] = $lengthTail;
            $operations[$index]["detailLenghtTotalTailPercent"] = $lengthTailPercent;
            $operations[$index]["details"] = $details;
            
            // Условие where для последующей выборки всех деталей сменного задания
            $whereDetails .= ($whereDetails ? " or " : "") . $this->where($details, "WriteMater.ID", "wcad_detail_id", " or ");
            
        }
        
        // Получить расширенные данные по материалам из базы WinCAD
        
        $whereOperations = $this->where($operations, "Material.ID", "wcad_material_id", " or ");
        
        $sql = "
            select 
                Material.ID as wcad_material_id, 
                Material.Length as wcad_material_length,
                Material.Art as wcad_material_art,
                Material.Name as wcad_material_name
            from Material
            where $whereOperations
        ";
        
        $operationsStatement = $adapterSqlsrvWcad->createStatement($sql);
        $resultSet = new ResultSet();
        $materials = $resultSet->initialize($operationsStatement->execute())->toArray();
        
        $_materials = array();
        foreach ($materials as &$material) {
            $material["wcad_material_art"] = $this->strconv($material["wcad_material_art"]);
            $material["wcad_material_name"] = $this->strconv($material["wcad_material_name"]);
            $_materials[$material["wcad_material_id"]] = $material;
        }
        $materials = $_materials;
        
        // Получить расширенные данные по деталям из базы WinCAD
        
        $sql = "
            select 
            
                WriteMater.ID as wcad_detail_id,
                Material.ID as wcad_material_id,
                Task.AccountNum as wcad_order_id,
                Project.Num as wcad_product_id,
                WriteMater.SizeX as wcad_detail_length,
                WriteMater.AngleLeft as wcad_detail_angle_left,
                WriteMater.AngleRight as wcad_detail_angle_right,
                WriteMater.nCount * Project.nCount as wcad_detail_count,
                Material.Art as wcad_material_art,
                Material.Name as wcad_material_name,
                Material.Length as wcad_material_length,
                Contur.Num as wcad_contur_num,
                Color_IN.Name as wcad_color_in_name,
                Color_BASE.Name as wcad_color_base_name,
                Color_OUT.Name as wcad_color_out_name,
                
                case when
                     (v_ObjectProp_Side.Val in(1, 5, 33, 37) and Seg.nSeg = 1) or
                     (v_ObjectProp_Side.Val in(2, 6, 34, 38) and Seg.nSeg = 3) or
                     (v_ObjectProp_Side.Val = 4 and Seg.nSeg = 2) or
                     (v_ObjectProp_Side.Val = 8 and Seg.nSeg = 0)  
                     then cast(v_ObjectProp_Heigth.Val as varchar) else ''
                end as wcad_distance_to_handle, -- Расстояние до ручки
                           
                case 
                    when Object.nClass in(21, 24, 40) then 'верт'
                    when Object.nClass in(23, 41, 42) then 'гориз'
                    when Object.nClass = 47           then ''
                    else case when Seg.nSeg = 0       then 'Н'
                    when Seg.nSeg = 1       then 'П'
                    when Seg.nSeg = 2       then 'В'
                    when Seg.nSeg = 3       then 'Л' end
                    end
                as wcad_size, -- Сторона/Ориентация
                
                case when
                     (v_ObjectProp_Side.Val in(1, 5, 33, 37) and Seg.nSeg = 1) 
                     or
                     (v_ObjectProp_Side.Val in(2, 6, 34, 38) and Seg.nSeg = 3)
                     or
                     (v_ObjectProp_Side.Val = 4 and Seg.nSeg = 2)
                     or
                     (v_ObjectProp_Side.Val = 8 and Seg.nSeg = 0)  
                     then    
                       (select Material2.MachineCode
                       from WriteMater as WriteMater2 
                       left join Material as Material2 on Material2.id=WriteMater2.idMaterial
                       left join Object as Object2 on Object2.ID = WriteMater2.idObj
                       left join Seg as Seg2 on Seg2.ID = WriteMater2.idSeg
                       left join Contur as Contur2 on Contur2.id = Object2.idContur
                       where Material2.MachineCode <> ''
                       and Contur2.id = Contur.id
                       and Object2.id = Object.id      
                       ) 
                else '' end wcad_machine_code -- Тип ручки
                
            from Task 
                        
            left join Project on Project.idTask=Task.id 
            left join WriteMater on WriteMater.idProject=Project.id
            left join Material on Material.id=WriteMater.idMaterial 
            left join MaterGroup on MaterGroup.ID = WriteMater.idMaterGroup
            left join Object on Object.ID = WriteMater.idObj
            left join Seg on Seg.ID = WriteMater.idSeg
            left join Profile on Profile.ID = WriteMater.idProfile
            left join ProfileGroup on ProfileGroup.ID = Profile.idProfileGroup
            left join v_ObjectProp as v_ObjectProp_Side on v_ObjectProp_Side.idObject = Object.id and v_ObjectProp_Side.Name = 'TypeOpen_Draw'
            left join v_ObjectProp as v_ObjectProp_Heigth on v_ObjectProp_Heigth.idObject = Object.id and v_ObjectProp_Heigth.Name = 'Handle_Height'
            left join Contur on Contur.id = Object.idContur
            left join Color as Color_IN on Color_IN.ID = WriteMater.idColor
            left join Color as Color_BASE on Color_BASE.ID = WriteMater.idColorBase
            left join Color as Color_OUT on Color_OUT.ID = WriteMater.idColorExt
            
            where MaterGroup.ID in (2, 18) and ($whereDetails)
        ";
        
        $detailsStatement = $adapterSqlsrvWcad->createStatement($sql);
        $resultSet = new ResultSet();
        $details = $resultSet->initialize($detailsStatement->execute())->toArray();
        
        // Подготовка массива с деталями:
        // Сделать ключами номера деталей и сменить кодировку текстов.
        
        $_details = array();
        foreach ($details as &$detail) {
            $detail["wcad_material_art"] = $this->strconv($detail["wcad_material_art"]);
            $detail["wcad_material_name"] = $this->strconv($detail["wcad_material_name"]);
            $detail["wcad_order_id"] = $this->strconv($detail["wcad_order_id"]);
            $detail["wcad_machine_code"] = $this->strconv($detail["wcad_machine_code"]);
            $detail["wcad_color_in_name"] = $this->strconv($detail["wcad_color_in_name"]);
            $detail["wcad_color_base_name"] = $this->strconv($detail["wcad_color_base_name"]);
            $detail["wcad_color_out_name"] = $this->strconv($detail["wcad_color_out_name"]);
            $_details[$detail["wcad_detail_id"]] = $detail;
        }
        $details = $_details;
        
        // Подготовка массива с итогами
        
        $summary = array();
        foreach ($operations as $operationIndex => $operation) {
            $operation["index"] = $operationIndex;
            
            // Группируем не только по номеру материала, но и по раскраскам хлыстов
            $colorin = $details[$operation["details"][0]["wcad_detail_id"]]["wcad_color_in_name"];
            $colorbase = $details[$operation["details"][0]["wcad_detail_id"]]["wcad_color_base_name"];
            $colorout = $details[$operation["details"][0]["wcad_detail_id"]]["wcad_color_out_name"];
            
            $materialId = $operation["wcad_material_id"];
            $materialAndColorId = $materialId . md5($colorin . $colorbase . $colorout);
            
            if (!$summary[$materialAndColorId]) {
                $materialLength = $operation["wcad_material_length"];
                $materialArt = $materials[$operation["wcad_material_id"]]["wcad_material_art"];
                $materialName = $materials[$operation["wcad_material_id"]]["wcad_material_name"];
                $summary[$materialAndColorId] = array(
                    "art" => $materialArt,
                    "name" => $materialName,
                    "length" => $materialLength,
                    "wcad_color_in_name" => $colorin,
                    "wcad_color_base_name" => $colorbase,
                    "wcad_color_out_name" => $colorout,
                    "operations" => array()
                );
            }
            $summary[$materialAndColorId]["operations"][] = $operation;
        }
        
        // Результат на рендеринг
        
        $this->layout()->title = "Сменное задание № $jobId";
        
        $result->job = array("id" => $jobId);
        $result->saw = $saw;
        $result->waste = $waste;
        $result->operations = $operations;
        $result->details = $details; // расширенные данные о деталях
        $result->materials = $materials; // расширенные данные о материалах
        $result->summary = $summary;
        
        return $result;
    }
    
    private function strconv($str) {
        return iconv("windows-1251", "utf-8", $str);
    }
    
    /**
     * Получить файл PDF с картой раскроя.
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function getpdfmapAction() {
        $config = $this->getServiceLocator()->get("config");
        
        $jobId = (int) $this->params()->fromQuery("job_id");
        
        $filename = "Сменное задание № {$jobId}.pdf";
        
        
        /* 
        $mapcutModel = $this->forward()->dispatch("Application\Controller\Mapcut", array(
            "action" => "viewmap",
            "job_id" => $jobId
        ));
        
        //
        // http://zendframework.ru/forum/index.php?topic=7160.new#new
        //$layout = $this->layout();
        //$layout->addChild($mapcutModel, "content");
        //$content = $this->getServiceLocator()->get("ViewRenderer")->render($layout);
        //
        
        //$layout = $this->layout();
        //$layout->content = $this->getServiceLocator()->get("ViewRenderer")->render($mapcutModel);
        //$content = $this->getServiceLocator()->get("ViewRenderer")->render($layout);
        
        
        $content = $this->getServiceLocator()->get("ViewRenderer")->render($mapcutModel);
         */
        
        
        /* 
        require_once "mpdf/MPDF57/mpdf.php";
        $mpdf = new \mPDF("utf-8", "A4", "8", "", 10, 10, 7, 7, 10, 10);
        //$mpdf->charset_in = 'cp1251';
        $stylesheet = file_get_contents($_SERVER["DOCUMENT_ROOT"] . "/css/mapcut.css");
        $stylesheet2 = file_get_contents($_SERVER["DOCUMENT_ROOT"] . "/css/mapcut.pdf.css");
        $mpdf->WriteHTML($stylesheet, 1);
        $mpdf->WriteHTML($stylesheet2, 1);
        $mpdf->list_indent_first_level = 0; 
        $mpdf->WriteHTML($content, 2); 
        $mpdf->Output($filename, "I");
        //$mpdf->Output($filename, "D");
         */
        
        
        /* 
        define('DOMPDF_ENABLE_AUTOLOAD', false);
        require_once 'vendor/dompdf/dompdf/dompdf_config.inc.php';
        $dompdf = new \DOMPDF();
        
        $stylesheet = file_get_contents($_SERVER["DOCUMENT_ROOT"] . "/css/mapcut.dompdf.css");
        $stylesheet = "<style>$stylesheet</style>";
        
        $dompdf->load_html($stylesheet . $stylesheet2 . $content);
        $dompdf->render();
        $dompdf->stream("hello.pdf", array("Attachment" => false));
         */
        
        
        /* 
        ini_set("memory_limit", "-1");
        ini_set("max_execution_time", 60*3);
        
        require_once("dompdf/dompdf_config.inc.php");
        
        
        //define('DOMPDF_ENABLE_AUTOLOAD', false);
        //require_once 'vendor/dompdf/dompdf/dompdf_config.inc.php';
        $dompdf = new \DOMPDF();
        
        $stylesheet = file_get_contents($_SERVER["DOCUMENT_ROOT"] . "/css/mapcut.dompdf.css");
        $stylesheet = "<style>$stylesheet</style>";
        
        $dompdf->load_html(
            '<html><meta http-equiv="content-type" content="text/html; charset=utf-8" /><body>'.
            $stylesheet . $content
            . '</body></html>'
        );
        $dompdf->render();
        $dompdf->stream($filename, array("Attachment" => false));
         */
        
        
        
        
        //
        // Вариант с программой wkhtmltopdf
        // http://wkhtmltopdf.org/
        //
        
        $destDirectory = $_SERVER["DOCUMENT_ROOT"] . "/mapcuts";
        if (!is_dir($destDirectory)) mkdir($destDirectory, 0777, true);
        
        $exe = $config["lincut"]["wkhtmltopdf"]["path"];
        $host = $_SERVER["HTTP_HOST"];
        $url = "http://$host/application/mapcut/viewmap/?job_id=$jobId&format=pdf";
        $dest = "$destDirectory/mapcut{$jobId}.pdf";
        
        $output = array();
        exec("$exe \"$url\" $dest", $output);
        
        // Подготовка результата
        
        $content = file_get_contents($dest);
        $contentLength = mb_strlen($content);
        
        
        $response = $this->getResponse();
        
        $response->getHeaders()->addHeaderLine("Content-Disposition", "attachment; filename=$filename");
        $response->getHeaders()->addHeaderLine("Content-Length", $contentLength);
        $response->getHeaders()->addHeaderLine("Content-Type", "application/x-pdf");
        
        $response->getHeaders()->addHeaderLine("Cache-Control", "must-revalidate");
        $response->getHeaders()->addHeaderLine("Pragma", "public");
        $response->getHeaders()->addHeaderLine("Content-Description", "File Transfer");
        $response->getHeaders()->addHeaderLine("Content-Transfer-Encoding", "binary");
        
        // TODO Надо соединить ZF2 и этот способ отправки файлов http://habrahabr.ru/post/151795/ 
        
        $response->setContent($content);
        return $response; 
    }
    
}
