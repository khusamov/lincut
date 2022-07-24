<?php

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;

use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;

class RefController extends AbstractRestfulController {

    /**
     * Получить справочник из базы данных Win CAD.
     */
    public function getList() {
        $result = new JsonModel();
        
        $ref = (string) $this->params()->fromQuery("ref");
        
        $refmap = array(
            "order-status" => array("table" => "TaskStatus", "fieldmap" => array("id" => "ID", "text" => "Name"))
        );
        
        if (!$refmap[$ref]) throw new \ErrorException("Not found ref = $ref");
        
        $ref = $refmap[$ref];
        
        $adapterSqlsrvWcad = $this->getServiceLocator()->get("db/wcad");
        
        $tableRef = new TableGateway($ref["table"], $adapterSqlsrvWcad);
        
        $select = $tableRef->getSql()->select();
        $data = $tableRef->selectWith($select)->toArray();
        
        // Переименовывание полей таблицы
        
        $_data = array();
        foreach ($data as $row) {
            $_data[] = array(
                "id" => $row[$ref["fieldmap"]["id"]],
                "text" => $this->strconv($row[$ref["fieldmap"]["text"]])
            );
        }
        $data = $_data;
        
        $result->success = true;
        $result->data = $data;
        return $result;
    }
    
    private function strconv($str) {
        return iconv("windows-1251", "utf-8", $str);
    }
    
}


