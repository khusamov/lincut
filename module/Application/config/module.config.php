<?php

return array(
    
    'controllers' => array(
        'invokables' => array(
            'Application\Controller\Index' => 'Application\Controller\IndexController',
            'Application\Controller\Orders' => 'Application\Controller\OrdersController',
            'Application\Controller\Jobs' => 'Application\Controller\JobsController',
            'Application\Controller\JobOrders' => 'Application\Controller\JobOrdersController',
            'Application\Controller\Mapcut' => 'Application\Controller\MapcutController',
            'Application\Controller\Ref' => 'Application\Controller\RefController',
            'Application\Controller\Config' => 'Application\Controller\ConfigController'
        ),
    ),
    
    'router' => array(
        'routes' => array(
            
            'home' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Index',
                        'action'     => 'index',
                    ),
                ),
            ),
            
            // The following is a route to simplify getting started creating
            // new controllers and actions without needing to create a new
            // module. Simply drop new controllers in, and you can access them
            // using the path /application/:controller/:action
            'application' => array(
                'type'    => 'Literal',
                'options' => array(
                    'route'    => '/application',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Application\Controller',
                        'controller'    => 'Index',
                        'action'        => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'default' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '/[:controller[/:action]]/',
                            'constraints' => array(
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ),
                            'defaults' => array(
                            ),
                        ),
                    ),
                ),
            ),
            
            // Маршрут для обработки REST-запросов от клиента на Sencha Ext JS 4
            'rest' => array(
                'type'    => 'Literal',
                'options' => array(
                    'route'    => '/application/rest',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Application\Controller',
                        'controller'    => 'Index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'default' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '/:controller/[:id]',
                            'constraints' => array(
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ),
                            'defaults' => array(
                            ),
                        ),
                    ),
                ),
            ), 
            
        ),
    ),
    
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map' => array(
            'layout/layout'           => __DIR__ . '/../view/layout/layout.phtml',
            'application/index/index' => __DIR__ . '/../view/application/index/index.phtml',
            'error/404'               => __DIR__ . '/../view/error/404.phtml',
            'error/index'             => __DIR__ . '/../view/error/index.phtml',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
        'strategies' => array(
        	   'ViewJsonStrategy'
        )
    ),
    
    'service_manager' => array(
        'abstract_factories' => array(
            'Zend\Db\Adapter\AdapterAbstractServiceFactory',
            'Zend\Cache\Service\StorageCacheAbstractServiceFactory',
        ),
    ),
    
    'db' => array('adapters' => array(
        // Подключение к базе PostgreSQL, где хранятся параметры программы lincut
        'db/lincut' => array(
            'driver' => 'Pgsql',
            'hostname' => 'lincut.loc',
            'database' => 'lincut',
            'username' => 'postgres',
            'password' => ''
        ),
        // Подключение к базе MS SQL 2008, где хранятся таблицы программы WinCAD 5 (значения по умолчанию)
        // Хранится в файле public/lincut.config.php (кроме опций driver и options)
        'db/wcad' => array(
            'driver' => 'Sqlsrv',
            'options' => array('CharacterSet' => 'UTF-8'),
            'servername' => 'WINDOWS8\SQLEXPRESS',
            'database' => 'WCAD',
            'username' => 'sa',
            'password' => '123'
        ),
    )),
    
    'caches' => array(
        'cache' => array(
            'adapter' => 'filesystem',
            //'ttl'     => 86400,
        )
    ),
    
    // Настройки программы lincut (значения по умолчанию)
    // Хранится в файле public/lincut.config.php
    'lincut' => array(
         'waste' => 40,
        	'saw' => 12,
         'wkhtmltopdf' => array(
             'path' => "C:\\\"Program Files\"\\wkhtmltopdf\\bin\\wkhtmltopdf.exe"
         ),
        'restriction' => array(
            'mode' => 'all',
            'uptime' => 10,
            'upcount' => 10000
        )
    )
    
);


