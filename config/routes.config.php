<?php
return array(
    'home' => array(
        'type' => 'Zend\Mvc\Router\Http\Literal',
        'options' => array(
            'route' => '/',
            'defaults' => array(
                'controller' => 'Minibus\Controller\Index',
                'action' => 'browse'
            )
        )
    ),
    'browse' => array(
        'type' => 'Zend\Mvc\Router\Http\Literal',
        'options' => array(
            'route' => '/browse',
            'defaults' => array(
                'controller' => 'Minibus\Controller\Index',
                'action' => 'browse'
            )
        )
    ),
    'acquisition' => array(
        'type' => 'Zend\Mvc\Router\Http\Literal',
        'options' => array(
            'route' => '/acquisition',
            'defaults' => array(
                'controller' => 'Minibus\Controller\Index',
                'action' => 'acquisition'
            )
        )
    ),
    'export' => array(
        'type' => 'Zend\Mvc\Router\Http\Literal',
        'options' => array(
            'route' => '/export',
            'defaults' => array(
                'controller' => 'Minibus\Controller\Index',
                'action' => 'export'
            )
        )
    ),
    'alerts' => array(
        'type' => 'Zend\Mvc\Router\Http\Literal',
        'options' => array(
            'route' => '/alerts',
            'defaults' => array(
                'controller' => 'Minibus\Controller\Index',
                'action' => 'alerts'
            )
        )
    ),
    'rest_alerts' => array(
        'type' => 'segment',
        'options' => array(
            'route' => '/rest/alerts',
            'verb' => 'get,put',
            'defaults' => array(
                'controller' => 'Minibus\Controller\AlertRest',
                'format' => 'json'
            )
        ),
        'may_terminate' => true,
        'child_routes' => array(
            'rest_alerts_structure' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/:type/structure',
                    'defaults' => array(
                        'controller' => 'Minibus\Controller\AlertStructureRest',
                        'format' => 'json'
                    ),
                    'constraints' => array(
                        'type' => '[a-z_]+'
                    )
                )
            )
        )
    ),
    'rest_alerts_delete' => array(
        'type' => 'segment',
        'options' => array(
            'route' => '/rest/alerts/:id',
            'verb' => 'delete',
            'defaults' => array(
                'controller' => 'Minibus\Controller\AlertRest',
                'format' => 'json',
                'id' => null
            )
        )
    ),
    'configuration' => array(
        'type' => 'Zend\Mvc\Router\Http\Literal',
        'options' => array(
            'route' => '/configuration',
            'defaults' => array(
                'controller' => 'Minibus\Controller\Index',
                'action' => 'configuration'
            )
        )
    ),
    'rest_configuration' => array(
        'type' => 'segment',
        'options' => array(
            'route' => '/rest/configuration',
            'verb' => 'get,put,post',
            'defaults' => array(
                'controller' => 'Minibus\Controller\ConfigurationRest',
                'format' => 'json'
            )
        )
    ),
    'rest_data' => array(
        'type' => 'segment',
        'options' => array(
            'route' => '/rest/data/:type[/:annee_scolaire]',
            'verb' => 'get',
            'defaults' => array(
                'controller' => 'Minibus\Controller\DataRest',
                'format' => 'json'
            ),
            'constraints' => array(
                'annee_scolaire' => '[0-9]+',
                'type' => '[a-z_]+'
            )
        )
    ),
    'rest_process' => array(
        'type' => 'segment',
        'options' => array(
            'route' => '/rest/process[/:mode/:type/:endpoint/:annee]',
            'verb' => 'get,put',
            'defaults' => array(
                'controller' => 'Minibus\Controller\ProcessRest',
                'format' => 'json'
            ),
            'constraints' => array(
                'mode' => '[a-z_]+',
                'type' => '[a-z_]+',
                'endpoint' => '[a-z_]+',
                'annee' => '[0-9]+'
            )
        ),
        'may_terminate' => true,
        'child_routes' => array(
            'rest_process_execution' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/execution',
                    'defaults' => array(
                        'controller' => 'Minibus\Controller\ExecutionRest',
                        'format' => 'json'
                    ),
                    'constraints' => array()
                )
            )
        )
    ),
    
    'rest_running_process' => array(
        'type' => 'segment',
        'options' => array(
            'route' => '/rest/process/running/:mode/:annee',
            'verb' => 'get',
            'defaults' => array(
                'controller' => 'Minibus\Controller\ProcessRest',
                'format' => 'json'
            ),
            'constraints' => array(
                'mode' => '[a-z_]+',
                'annee' => '[0-9]+'
            )
        )
    ),
    'rest_process_force_execution' => array(
        'type' => 'segment',
        'options' => array(
            'route' => '/rest/process/execute/:mode/:type/:endpoint/:annee',
            'verb' => 'post',
            'defaults' => array(
                'controller' => 'Minibus\Controller\ProcessRest',
                'format' => 'json'
            ),
            'constraints' => array(
                'mode' => '[a-z_]+',
                'type' => '[a-z_]+',
                'endpoint' => '[a-z_]+',
                'annee' => '[0-9]+'
            )
        )
    ),
    'rest_log' => array(
        'type' => 'segment',
        'options' => array(
            'route' => '/rest/log/:id[/lines/:nblines]',
            'verb' => 'get,put,post,delete',
            'defaults' => array(
                'controller' => 'Minibus\Controller\LogRest',
                'format' => 'json'
            ),
            'constraints' => array(
                'id' => '[a-z0-9]+',
                'nblines' => '[0-9al]+'
            )
        )
    ),
    'execution' => array(
        'type' => 'segment',
        'options' => array(
            'route' => '/execution',
            'verb' => 'post',
            'defaults' => array(
                'controller' => 'Minibus\Controller\Execution',
                'format' => 'json'
            )
        )
    ),
    'redirection-liens-alertes' => array(
        'type' => 'segment',
        'options' => array(
            'route' => '/alerts-redirection/process/:mode/:process[/execution/:execution]',
            'defaults' => array(
                'controller' => 'Minibus\Controller\AlertRedirection',
                'action' => 'redirect'
            ),
            'constraints' => array(
                'process' => '[a-z\-]+',
                'mode' => '[a-z]+',
                'execution' => '[0-9]+'
            )
        )
    )
);