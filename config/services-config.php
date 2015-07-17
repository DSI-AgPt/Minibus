<?php
return array(
    'abstract_factories' => array(
        'Minibus\Controller\Process\AbstractFactory\AbstractDataTransferFactory',
        'Zend\Cache\Service\StorageCacheAbstractServiceFactory',
        'Zend\Log\LoggerAbstractServiceFactory',
        'Minibus\Model\Browse\Factory\AbstractFormatterFactory'
    ),
    // TODO move to abstract factories
    'factories' => array(
        'entitymanager' => 'DoctrineORMModule\Service\EntityManagerFactory',
        'Minibus\Model\Io\Rest\Client' => function ($serviceManager)
        {
            $httpClient = $serviceManager->get('HttpClient');
            $enableRestClientSslVerification = false;
            $httpRestJsonClient = new Minibus\Model\Io\Rest\Client($httpClient, $enableRestClientSslVerification);
            return $httpRestJsonClient;
        },
        'HttpClient' => function ($serviceManager)
        {
            $httpClient = new Zend\Http\Client();
            $httpClient->setAdapter('Zend\Http\Client\Adapter\Curl');
            return $httpClient;
        }
    ),
    'invokables' => array(
        'configuration-handler' => 'Minibus\Model\Configuration\Service\ConfigurationHandler',
        'process-state-handler' => 'Minibus\Model\Process\Service\ProcessStateHandler',
        'log-handler' => 'Minibus\Controller\Log\Service\LogHandler',
        'alert-handler' => 'Minibus\Controller\Alert\Service\AlertHandler',
        'alert-data-handler' => 'Minibus\Controller\Alert\Service\AlertDataHandler',
        'endpoint-connection-builder' => 'Minibus\Controller\Process\Service\Connection\EndpointConnectionBuilder',
        'process-updater' => 'Minibus\Model\Process\Service\ProcessUpdater',
        'process-execution-handler' => 'Minibus\Controller\Process\Service\ProcessExecutionHandler',
        'process-sheduler' => 'Minibus\Controller\Process\Service\Sheduler',
        'datatypes-handler' => 'Minibus\Model\Configuration\Service\DataTypesHandler',
        'hash-calculator' => 'Minibus\Model\Process\Service\Hash\HashCalculator',
        'zfc-user-redirection' => 'Minibus\Controller\Auth\Service\ZfcUserRedirectionListener',
        'file-auth-service' => 'Minibus\Controller\Auth\Service\FileAuthService',
        'scp_client' => 'Minibus\Model\Io\Scp\ScpClient',
        'pgp_decrypt' => 'Minibus\Model\Io\Crypt\PgpDecrypt'
    ),
    'aliases' => array(
        'translator' => 'MvcTranslator',
        'json_rest_client' => 'Minibus\Model\Io\Rest\Client'
    )
);
  