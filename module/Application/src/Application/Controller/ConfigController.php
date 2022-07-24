<?php

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;
use Zend\Config\Config;

class ConfigController extends AbstractRestfulController {
    
    /**
     * Метод GET для RESTfull.
     * Если запрашивается один ресурс.
     */
    public function get($id) {
        $result = new JsonModel();
        
        $config = $this->getServiceLocator()->get("config");
        
        $lincut = new Config($config["lincut"]);
        $dbwcad = new Config($config["db"]["adapters"]["db/wcad"]);
        
        $data = array(
            "wkhtmltopdf_path" => $lincut->wkhtmltopdf->path,
            "restriction_mode" => $lincut->restriction->mode,
            "restriction_up_time" => $lincut->restriction->uptime,
            "restriction_up_count" => $lincut->restriction->upcount,
            "saw" => $lincut->saw,
            "waste" => $lincut->waste,
            "db_wcad_servername" => $dbwcad->servername,
            "db_wcad_database" => $dbwcad->database,
            "db_wcad_username" => $dbwcad->username,
            "db_wcad_password" => $dbwcad->password
        );
        
        // Возвращаем результат операции
        $result->success = true;
        $result->data = $data;
        return $result;
    }
    
    /**
     * Обновить запись.
     * Метод PUT для RESTfull.
     */
    public function update($id, $data) {
        $result = new JsonModel();
        $lincutConfigPath = $_SERVER["DOCUMENT_ROOT"] . "/lincut.config.php";
        
        // Создаем в памяти новый конфигурационный файл с новыми настройками
        
        $lincutConfigNew = new Config(array(), true);
        
        $lincutConfigNew->lincut = array();
        $lincutConfigNew->lincut->wkhtmltopdf = array();
        $lincutConfigNew->lincut->restriction = array();
        if (array_key_exists("wkhtmltopdf_path", $data)) $lincutConfigNew->lincut->wkhtmltopdf->path = $data["wkhtmltopdf_path"];
        if (array_key_exists("restriction_mode", $data)) $lincutConfigNew->lincut->restriction->mode = $data["restriction_mode"];
        if (array_key_exists("restriction_up_time", $data)) $lincutConfigNew->lincut->restriction->uptime = $data["restriction_up_time"];
        if (array_key_exists("restriction_up_count", $data)) $lincutConfigNew->lincut->restriction->upcount = $data["restriction_up_count"];
        if (array_key_exists("saw", $data)) $lincutConfigNew->lincut->saw = $data["saw"];
        if (array_key_exists("waste", $data)) $lincutConfigNew->lincut->waste = $data["waste"];
        
        $dbwcad = "db/wcad";
        $lincutConfigNew->db = array();
        $lincutConfigNew->db->adapters = array();
        $lincutConfigNew->db->adapters->{$dbwcad} = array();
        if (array_key_exists("db_wcad_servername", $data)) $lincutConfigNew->db->adapters->{$dbwcad}->servername = $data["db_wcad_servername"];
        if (array_key_exists("db_wcad_database", $data)) $lincutConfigNew->db->adapters->{$dbwcad}->database = $data["db_wcad_database"];
        if (array_key_exists("db_wcad_username", $data)) $lincutConfigNew->db->adapters->{$dbwcad}->username = $data["db_wcad_username"];
        if (array_key_exists("db_wcad_password", $data)) $lincutConfigNew->db->adapters->{$dbwcad}->password = $data["db_wcad_password"];
        
        // Открываем существующий файл настроек, если он имеется в наличии
        
        $lincutConfig = array();
        if (is_file($lincutConfigPath)) {
            $lincutConfig = \Zend\Config\Factory::fromFile($lincutConfigPath);
        }
        
        $lincutConfig = new Config($lincutConfig, true);
        $lincutConfig->merge($lincutConfigNew);
        
        // Сохраняем настройки
        
        $writer = new \Zend\Config\Writer\PhpArray();
        $writer->toFile($lincutConfigPath, $lincutConfig);
        
        // Возвращаем результат операции
        
        $result->success = true;
        return $result;
    }
    
}


