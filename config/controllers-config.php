<?php
return array(
    'invokables' => array(
        'Minibus\Controller\Index' => 'Minibus\Controller\IndexController',
        'Minibus\Controller\ConfigurationRest' => 'Minibus\Controller\ConfigurationRestController',
        'Minibus\Controller\ProcessRest' => 'Minibus\Controller\ProcessRestController',
        'Minibus\Controller\ExecutionRest' => 'Minibus\Controller\ExecutionRestController',
        'Minibus\Controller\LogRest' => 'Minibus\Controller\LogRestController',
        'Minibus\Controller\Execution' => 'Minibus\Controller\ExecutionController',
        'Minibus\Controller\DataRest' => 'Minibus\Controller\DataRestController',
        'Minibus\Controller\AlertRest' => 'Minibus\Controller\AlertRestController',
        'Minibus\Controller\AlertStructureRest' => 'Minibus\Controller\AlertStructureRestController',
        'Minibus\Controller\AlertRedirection' => 'Minibus\Controller\AlertRedirectionController'
    )
);