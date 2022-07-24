<?php

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;

use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Expression;

use Zend\Json\Json;

class JobOrdersController extends AbstractRestfulController {
    
    /**
     * Получить список заказов, принадлежащих сменному заданию (extra param = job_id).
     */
    public function getList() {
        $result = new JsonModel();
        
        // Берем список заказов из таблицы job_order, а затем сделать выборку из WCAD выборку заказов 
        
        $adapterPgsql = $this->getServiceLocator()->get("db/lincut");
        $tableJobOrders = new TableGateway("job_order", $adapterPgsql);
        
        $adapterSqlsrv = $this->getServiceLocator()->get("db/wcad");
        
        $page = (int) $this->params()->fromQuery("page", 1);
	     $start = (int) $this->params()->fromQuery("start", 0);
	     $limit = (int) $this->params()->fromQuery("limit", 25);
	     $filter = $this->params()->fromQuery("filter", "[]");
	     $filter = Json::decode($filter, Json::TYPE_ARRAY);
	     // extra params
	     $jobId = (int) $this->params()->fromQuery("job_id"); // Номер сменного задания
	     
	     // Подготовливаем условие where для выборки нужных заказов
	     
	     $selectJobOrders = $tableJobOrders->getSql()->select()->where(array("job_id" => $jobId));
	     $jobOrders = $tableJobOrders->selectWith($selectJobOrders)->toArray();
	     
	     $where = array();
	     foreach ($jobOrders as $jobOrder) {
	         $where[] = "Task.ID = " . $jobOrder["order_id"];
	     }
	     $where = implode(" or ", $where);
	     
	     if ($where) {
	         
    	     // Подсчитываем общее количество строк в таблице
    	     
    	     $count = $tableJobOrders->getSql()->select()->columns(array("value" => new Expression("count(*)")))->where(array("job_id" => $jobId));
    	     $count = $tableJobOrders->selectWith($count)->toArray();
    	     $total = $count[0]["value"];
    	     $total = (int) $total;
    	     
    	     // Запрос данных
    	     
            $startlimit = $page * $limit;
            
            $taskStatement = $adapterSqlsrv->createStatement("
            		select * from (
            		select top ($limit) * from (
            
                    select top ($startlimit)
                    Task.ID as TaskID,
                    Task.AccountNum as TaskAccountNum,
                    Client.Name as ClientName,
                    Task.Date as TaskDate,
                    Task.DateComplite as TaskDateComplite,
                    TaskStatus.Name as TaskStatus
                    from Task
                    left join TaskStatus on TaskStatus.ID=Task.State
                    left join Client on Client.ID=Task.idClient
                    where $where
                    order by TaskDate, TaskDateComplite, TaskStatus, TaskAccountNum
            
            	   ) as orders order by TaskDate desc, TaskDateComplite desc, TaskStatus desc, TaskAccountNum desc
            	   ) as orders order by TaskDate asc, TaskDateComplite asc, TaskStatus asc, TaskAccountNum asc
            ");
            
            $resultSet = new ResultSet();
            $resultSet->initialize($taskStatement->execute());
    	     
    	     /* 	     
    	     
    	     $startlimit = $page * $limit;
    	     
    	     echo $table->getSql()->select()->quantifier(new Expression("top(?)", array($startlimit)))
    	     ->getSqlString($adapter->getPlatform()) . PHP_EOL . PHP_EOL;
    	     
    	      */
    	     
            // Подготовка данных
            
            $data = array();
            foreach ($resultSet as $row) {
                $data[] = array(
                    "TaskID" => $row->TaskID,
                    "TaskAccountNum" => $this->strconv($row->TaskAccountNum),
                    "ClientName" => $this->strconv($row->ClientName),
                    "TaskDate" => $row->TaskDate,
                    "TaskDateComplite" => $row->TaskDateComplite,
                    "TaskStatus" => $this->strconv($row->TaskStatus)
                );
            }
            
	     } else { // Нет данных
	         $total = 0;
	         $data = array();
	     }
	     
        // Возвращаем результат операции
        $result->success = true;
        $result->total = $total;
        $result->page = $page;
        $result->start = $start;
        $result->limit = $limit;
        $result->data = $data;
        return $result;
    }
    
    private function strconv($str) {
        return iconv("windows-1251", "utf-8", $str);
    }
    
    /**
     * Создать новую запись.
     * Должен быть определен номер задания (job_id).
     * Метод POST для RESTfull.
     */
    public function create($data) {
        $result = new JsonModel();
        
        $adapter = $this->getServiceLocator()->get("db/lincut");
        
        $table = new TableGateway("job_order", $adapter);
        
        // Подготовка новой записи
        $data = array(
            "job_id" => (int) $this->getRequest()->getQuery("job_id"),
            "order_id" => $data["TaskID"]
        );
        
        // Вставляем строку в таблицу
        $table->insert($data);
        
        // Пока не ясно как получить номер новой строки в PostgreSQL иным способом,
        // кроме как обращаться в соответствующей последовательности
        // http://zendframework.ru/forum/index.php?topic=7082.0
        //$id = $table->lastInsertValue;
        $id = (int) $adapter->getDriver()->getConnection()->getLastGeneratedValue("job_order_id_seq1");
        
        // Возвращаем результат операции
        $result->success = true;
        $result->data = array_merge(array("id" => $id), $data);
        return $result;
    }
    
}
