<?php

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;

use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Expression;

class JobsController extends AbstractRestfulController {
    
    /**
     * Метод GET для RESTfull.
     * Если запрашивается один ресурс.
     */
    public function get($id) {
        $result = new JsonModel();
        
        $adapter = $this->getServiceLocator()->get("db/lincut");
        $table = new TableGateway("job", $adapter);
        
        $data = $table->select(array("id" => $id))->toArray();
        $data = $data[0];
	     $data = $this->convertPostgres2Extdata($data);
        
        // Возвращаем результат операции
        $result->success = true;
        $result->data = $data;
        return $result;
    }
    
    /**
     * Метод GET для RESTfull.
     * Если запрашивается список ресурсов.
     */
    public function getList() {
        $result = new JsonModel();
        
        $adapter = $this->getServiceLocator()->get("db/lincut");
        $table = new TableGateway("job", $adapter);
        
        $page = (int) $this->params()->fromQuery("page", 1);
	     $start = (int) $this->params()->fromQuery("start", 0);
	     $limit = (int) $this->params()->fromQuery("limit", 25);
	     
	     // Подсчитываем общее количество строк в таблице
	     
	     $count = $table->getSql()->select()->columns(array("value" => new Expression("count(*)")));
	     $count = $table->selectWith($count)->toArray();
	     $total = $count[0]["value"];
	     $total = (int) $total;
	     
	     // Запрос данных
	     
	     /* 	     
	     
	     $startlimit = $page * $limit;
	     
	     echo $table->getSql()->select()->quantifier(new Expression("top(?)", array($startlimit)))
	     ->getSqlString($adapter->getPlatform()) . PHP_EOL . PHP_EOL;
	     
	      */
	     
	     $select = $table->getSql()->select()->limit($limit)->offset($start);
	     $data = $table->selectWith($select)->toArray();
	     $data = $this->convertPostgres2ExtdataList($data);
	     
        // Возвращаем результат операции
        $result->success = true;
        $result->total = $total;
        $result->page = $page;
        $result->start = $start;
        $result->limit = $limit;
        $result->data = $data;
        return $result;
    }
    
    /**
     * Создать новую запись.
     * Метод POST для RESTfull.
     */
    public function create($data) {
        $result = new JsonModel();
        
        $adapter = $this->getServiceLocator()->get("db/lincut");
        
        $table = new TableGateway("job", $adapter);
        
        // Вставляем строку в таблицу
        $table->insert($this->convertExtdata2Postgres($data));
        
        // Пока не ясно как получить номер новой строки в PostgreSQL иным способом,
        // кроме как обращаться в соответствующей последовательности
        // http://zendframework.ru/forum/index.php?topic=7082.0
        //$id = $table->lastInsertValue;
        $id = (int) $adapter->getDriver()->getConnection()->getLastGeneratedValue("job_job_id_seq");
        
        // Возвращаем результат операции
        $result->success = true;
        $result->data = array_merge(array("id" => $id), $data);
        return $result;
    }
    
    /**
     * Обновить запись.
     * Метод PUT для RESTfull.
     */
    public function update($id, $data) {
        $result = new JsonModel();
        
        $adapter = $this->getServiceLocator()->get("db/lincut");
        
        $table = new TableGateway("job", $adapter);
        
        // Обновляем строку в таблицу
        $table->update($this->convertExtdata2Postgres($data), array("id" => $id));
        
        // Возвращаем результат операции
        $result->success = true;
        $result->data = $data;
        return $result;
    }
    
    /**
     * Метод DELETE для RESTfull.
     */
    public function delete($jobId) {
        $result = new JsonModel();
        
        $adapter = $this->getServiceLocator()->get("db/lincut");
        
        $jobTable = new TableGateway("job", $adapter);
        
        $jobTable->delete(array("id" => $jobId));
        
        // Удаляем также карту раскроя в виде PDF-файла
        
        $destDirectory = $_SERVER["DOCUMENT_ROOT"] . "/mapcuts";
        $dest = "$destDirectory/mapcut{$jobId}.pdf";
        if (is_file($dest)) unlink($dest);
        
        // Возвращаем результат операции
        $result->success = true;
        return $result;
    }
    
    private function convertExtdata2Postgres($data) {
        $data["map_ready"] = new Expression($data["map_ready"] ? "true" : "false");
        return $data;
    }
    
    /**
     * PostgreSQL выдает значение поля boolean в виде одной буквы f или t.
     * Для Ext JS нужно ее конвертировать в тип boolean для JSON.
     * @param array $data
     * @return boolean
     */
    private function convertPostgres2Extdata($data) {
        $data["map_ready"] = $data["map_ready"] == "f" ? false : true;
        return $data;
    }
    
    private function convertPostgres2ExtdataList($data) {
        $result = array();
        foreach ($data as $item) {
            $result[] = $this->convertPostgres2Extdata($item);
        }
        return $result;
    }
    
}
