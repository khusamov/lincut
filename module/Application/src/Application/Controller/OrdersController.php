<?php

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;

use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\ResultSet\ResultSet;

use Zend\Json\Json;

class OrdersController extends AbstractRestfulController {

    public function getList() {
        $json = new JsonModel();
        
        $page = (int) $this->params()->fromQuery("page", 1);
        $start = (int) $this->params()->fromQuery("start", 0);
        $limit = (int) $this->params()->fromQuery("limit", 25);
        $filter = (array) $this->params()->fromQuery("filter", array());
        $sort = (string) $this->params()->fromQuery("sort");
        
        $cachekey = md5("orders" . $page . $start . $limit . $sort . serialize($filter));
        
        $sort = Json::decode($sort, Json::TYPE_ARRAY);
        $sort = $sort ? $sort : array();
        
        
        
        // Восстанавливаем из кеша
        $cache = $this->getServiceLocator()->get("cache");
        $dataResults = $cache->getItem($cachekey);
        if ($dataResults == null) {
            // Если нет кеша, то создаем результат заново
            
        
            $adapter = $this->getServiceLocator()->get("db/wcad");
            
            // Карта полей для построения общего where
            
            $fnDateConvert = function($value) {
                $date = \DateTime::createFromFormat('Y-m-d', $value);
                return $date->format("d.m.Y");
            };
            
            // http://php.ru/forum/viewtopic.php?f=13&t=48424
            //print_r($_GET["filter"]);
            
            $where = $this->filter($filter, array(
                "TaskID" => "Task.ID", 
                "TaskAccountNum" => "Task.AccountNum", 
                "ClientName" => "Client.Name", 
                "TaskDate" => array(
                    "to" => "Task.Date",
                    "convert" => $fnDateConvert
                ),
                "TaskDateComplite" => array(
                    "to" => "Task.DateComplite",
                    "convert" => $fnDateConvert
                ),
                "TaskStatus" => "TaskStatus.ID", 
            ));
            
            // Подсчитываем общее количество строк в таблице
            
            $sql = "
                select count(*) as value
                from Task
                left join TaskStatus on TaskStatus.ID = Task.State
                left join Client on Client.ID = Task.idClient
                $where
            ";
            
            $sql = iconv("utf-8", "windows-1251", $sql);
            
            $taskStatement = $adapter->createStatement($sql);
            $total = $taskStatement->execute();
            $totalSet = new ResultSet();
            $totalSet->initialize($total);
            $total = $totalSet->toArray();
            $total = $total[0]["value"];
            $total = (int) $total;
            
            
            // Запрос данных
            
            //$orderby = $this->sort($sort);
            
            //$startlimit = $page * $limit;
            
            /* ИСХОДНЫЙ ВАРИАНТ $sql = "
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
        	    		$where
        	    		order by TaskDate asc, TaskDateComplite asc, TaskStatus asc, TaskAccountNum asc
                   
            	   ) as orders order by TaskDate desc, TaskDateComplite desc, TaskStatus desc, TaskAccountNum desc
            	   ) as orders order by TaskDate asc, TaskDateComplite asc, TaskStatus asc, TaskAccountNum asc
        	  "; */
            
            
            
            $sql = $this->limitForSqlsrv($limit, $page, $sort, "TaskID", "
                select 
                	Task.ID as TaskID,
                	Task.AccountNum as TaskAccountNum,
                	Client.Name as ClientName,
                	Task.Date as TaskDate,
                	Task.DateComplite as TaskDateComplite,
                	TaskStatus.Name as TaskStatus
                from Task
                left join TaskStatus on TaskStatus.ID = Task.State
                left join Client on Client.ID = Task.idClient
                $where
            "); 
            
            
            
            $sql = iconv("utf-8", "windows-1251", $sql);
            
            //print_r($sql.PHP_EOL.PHP_EOL.PHP_EOL);
            
            
            
            /* $sql = $this->limitForSqlsrv("
                
                select top ($startlimit)
                	Task.ID as TaskID,
                	Task.AccountNum as TaskAccountNum,
                	Client.Name as ClientName,
                	Task.Date as TaskDate,
                	Task.DateComplite as TaskDateComplite,
                	TaskStatus.Name as TaskStatus
                from Task
                left join TaskStatus on TaskStatus.ID = Task.State
                left join Client on Client.ID = Task.idClient
                $where
                
            ", $limit, $sort, "TaskID");  */
            
            
            
            
            /* $sql = $this->limitForSqlsrv("
                
                select top ($startlimit)
                	Task.ID as TaskID,
                	Task.AccountNum as TaskAccountNum,
                	Client.Name as ClientName,
                	Task.Date as TaskDate,
                	Task.DateComplite as TaskDateComplite,
                	TaskStatus.Name as TaskStatus
                from Task
                left join TaskStatus on TaskStatus.ID = Task.State
                left join Client on Client.ID = Task.idClient
                
            ", $limit, "TaskID") . "
                $where
                $orderby";*/
            
            /* $sql = "
            		select * from (
            		select top ($limit) * from (
            
        	    		select top ($startlimit)
            	    		Task.ID as TaskID,
            	    		Task.AccountNum as TaskAccountNum,
            	    		Client.Name as ClientName,
            	    		CONVERT(varchar, Task.Date, 120) as TaskDate,
            	    		CONVERT(varchar, Task.DateComplite, 120) as TaskDateComplite,
            	    		TaskStatus.Name as TaskStatus
        	    		from Task
        	    		left join TaskStatus on TaskStatus.ID=Task.State
        	    		left join Client on Client.ID=Task.idClient
        	    		order by TaskDate, TaskDateComplite, TaskStatus, TaskAccountNum
                   $where
            
            	   ) as orders order by TaskDate desc, TaskDateComplite desc, TaskStatus desc, TaskAccountNum desc
            	   ) as orders order by TaskDate asc, TaskDateComplite asc, TaskStatus asc, TaskAccountNum asc
        	  "; */
            
            //print_r($sql);
            
            /* $sql = "
            		select * from (
            		select top ($limit) * from (
            
        	    		select top ($startlimit)
            	    		Task.ID as TaskID,
            	    		Task.AccountNum as TaskAccountNum,
            	    		Client.Name as ClientName,
            	    		CONVERT(varchar, Task.Date, 120) as TaskDate,
            	    		CONVERT(varchar, Task.DateComplite, 120) as TaskDateComplite,
            	    		TaskStatus.ID as 'TaskStatus.ID'
        	    		from Task
        	    		left join TaskStatus on TaskStatus.ID = Task.State
        	    		left join Client on Client.ID = Task.idClient
        	    		order by TaskDate, TaskDateComplite, 'TaskStatus.ID', TaskAccountNum
                   $where
            
            	   ) as orders order by TaskDate desc, TaskDateComplite desc, 'TaskStatus.ID' desc, TaskAccountNum desc
            	   ) as orders order by TaskDate asc, TaskDateComplite asc, 'TaskStatus.ID' asc, TaskAccountNum asc
        	  "; */
            
            //print_r($sql);
            
            $taskStatement = $adapter->createStatement($sql);
        
            $resultSet = new ResultSet();
            $resultSet = $resultSet->initialize($taskStatement->execute())->toArray();
            
            // Подготовка данных
            
            $data = array();
            foreach ($resultSet as $row) {
            	$data[] = array(
            	    "TaskID" => $row["TaskID"],
            	    "TaskAccountNum" => $this->strconv($row["TaskAccountNum"]),
            	    "ClientName" => $this->strconv($row["ClientName"]),
            	    "TaskDate" => $row["TaskDate"],
            	    "TaskDateComplite" => $row["TaskDateComplite"],
            	    "TaskStatus" => $this->strconv($row["TaskStatus"])
                );
            }
            
            
            // Кеширование
            
            $dataResults = array(
                "total" => $total,
                "data" => $data
            );
            $cache->setItem($cachekey, serialize($dataResults));
        } else {
            $dataResults = unserialize($dataResults);
        }
        $total = $dataResults["total"];
        $data = $dataResults["data"];
        
        
        
        
        $json->success = true;
        $json->page = $page;
        $json->start = $start;
        $json->limit = $limit;
        $json->total = $total;
        $json->data = $data;
        
        return $json;	
    }
    
    private function strconv($str) {
        return iconv("windows-1251", "utf-8", $str);
    }
    
    /**
     * Имитация mysql-инструкции limit для MS SQL сервера.
     * @param int $limit
     * @param int $page
     * @param array $sort
     * @param string $primary
     * @param string $sql
     * @return string
     */
    private function limitForSqlsrv($limit, $page, $sort, $primary, $sql) {
        $sort = count($sort) ? $sort : array(array("property" => $primary));
        $orderby = $this->sort($sort);
        $startlimit = ($page - 1) * $limit;
        $endlimit = $startlimit + $limit;
        $numberer = "select *, row_number() over ($orderby) as rownumber from ($sql) as noname";
        return "select * from ($numberer) as noname where rownumber between $startlimit and $endlimit";
    }
    /* private function limitForSqlsrv($sql, $limit, $sort, $primary) {
        $sort = count($sort) ? $sort : array(array("property" => $primary));
        $normal = $this->sort($sort);
        $inverse = $this->sort($sort, true);
        return "select * from (select top ($limit) * from ($sql $normal) as noname $inverse) as noname $normal";
    } */
    /* private function limitForSqlsrv($sql, $limit, $primary) {
        return "select * from (select * from (select top ($limit) * from ($sql order by $primary) as noname order by $primary desc) as noname order by $primary asc)";
    } */

    /**
     * Разбирает сортировку от Ext JS.
     * Генерирует либо пустую строку либо sql-инструкцию "order by *".
     * @param array $sort
     * @return string
     */
    private function sort($sort, $inverse = false) {
        $result = array();
        if (is_array($sort)) foreach ($sort as $item) {
            $direction = $item["direction"] ? mb_strtolower($item["direction"]) : "asc";
            $direction = $inverse ? ($direction == "asc" ? "desc" : "asc") : $direction;
            $field = $item["property"];
            $result[] = "$field $direction";
        }
        return count($result) ? "order by " . implode(", ", $result) : "";
    }

    /**
     * Разбирает фильтр от Ext JS.
     * Генерирует либо пустую строку либо sql-инструкцию "where *".
     * @param array $filter
     * @return string
     */
    private function filter($filter, $fieldmap) {
       $result = array();
       $comparisons = array("lt" => "<", "gt" => ">", "eq" => "=");
       if (is_array($filter)) foreach ($filter as $item) {
           $from = $item["field"];
           $data = $item["data"];
           $value = $data["value"];
           
           $to = $fieldmap[$from];
           if (!$to) $to = $from;
           if (!is_array($to)) $to = array("to" => $to);
           if (!$to["convert"]) $to["convert"] = function($value) { return $value; };
           
           $convert = $to["convert"];
           $field = $to["to"];
           
           switch ($data["type"]) {
               case "string":
           	       $cond = "$field like '%$value%'";
                   break; 
           	   case "list":
           	       $list = array();
           	       foreach (explode(",", $value) as $one) {
           	           $one = (int) $one;
           	           $list[] = "$field = $one";
           	       }
           	       $cond = implode(" or ", $list);
           	       break;
           	   case "date":
           	       $comparison = $comparisons[$data["comparison"]];
           	       $value = $convert($value);
           	       $cond = "$field $comparison '$value'";
           	       break;
           }
           $result[] = "($cond)";
       }
       return count($result) ? "where " . implode(" and ", $result) : "";
    }
    
}
