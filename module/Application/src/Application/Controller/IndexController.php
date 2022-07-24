<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;

use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\ResultSet\ResultSet;
use Zend\Config\Factory;

class IndexController extends AbstractActionController {
    
    /**
     * Рабочий стол программы.
     * По сути основная точка входа.
     * @see \Zend\Mvc\Controller\AbstractActionController::indexAction()
     */
    public function indexAction() {
        $view = new ViewModel();
        $this->layout("layout/lincut");
        return $view;
    }
	
    // TODO этот метод удалить!
    /* public function ordersAction() {
        $json = new JsonModel();

        $page = (int) $this->params()->fromQuery("page", 1);
        $start = (int) $this->params()->fromQuery("start", 0);
        $limit = (int) $this->params()->fromQuery("limit", 25);

        
        $adapter = new \Zend\Db\Adapter\Adapter(array(
        		'driver' => 'Sqlsrv',
        		'servername' => 'WINDOWS8\SQLEXPRESS',
        		'database' => 'WCAD',
        		'username' => 'sa',
        		'password' => '123',
        		'options' => array('CharacterSet' => 'UTF-8')
        ));
		
        

        // Подсчитываем общее количество строк в таблице
        
        $taskStatement = $adapter->createStatement("
            select count(*) as value
            from Task
            left join TaskStatus on TaskStatus.ID=Task.State
            left join Client on Client.ID=Task.idClient
        ");
        $total = $taskStatement->execute();
        $totalSet = new ResultSet();
        $totalSet->initialize($total);
        $total = $totalSet->toArray();
        $total = $total[0]["value"];
        $total = (int) $total;
        
        
		  // Запрос данных
		  
        $startlimit = $page * $limit;
        
        $taskStatement = $adapter->createStatement("
            select * from (
            select top ($limit) * from (
            
                select top ($startlimit)
                    Task.ID as TaskID,
                    Task.AccountNum as TaskAccountNum,
                    Client.Name as ClientName,
                    CONVERT(varchar, Task.Date, 104) as TaskDate,
                    CONVERT(varchar, Task.DateComplite, 104) as TaskDateComplite,
                    TaskStatus.Name as TaskStatus
                from Task
                left join TaskStatus on TaskStatus.ID=Task.State
                left join Client on Client.ID=Task.idClient
                order by TaskDate, TaskDateComplite, TaskStatus, TaskAccountNum
            
            ) as orders order by TaskDate desc, TaskDateComplite desc, TaskStatus desc, TaskAccountNum desc
            ) as orders order by TaskDate asc, TaskDateComplite asc, TaskStatus asc, TaskAccountNum asc
        "); 
        
        $result = $taskStatement->execute();
        
        $resultSet = new ResultSet();
        $resultSet->initialize($result);
        
        // Подготовка данных 
        
        $data = array();
        foreach ($resultSet as $row) {
            $data[] = array(
                "TaskID" => $row->TaskID,
                "TaskAccountNum" => $row->TaskAccountNum,
                "ClientName" => $this->strconv($row->ClientName),
                "TaskDate" => $row->TaskDate,
                "TaskDateComplite" => $row->TaskDateComplite,
                "TaskStatus" => $this->strconv($row->TaskStatus)
            );
        }
        
        $json->success = true;
        $json->total = $total;
        $json->page = $page;
        $json->start = $start;
        $json->limit = $limit;
        $json->data = $data;
        
        return $json;
    }
    
    private function strconv($str) {
        return iconv("windows-1251", "utf-8", $str);
    } */
    
}
