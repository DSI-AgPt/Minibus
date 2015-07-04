<?php
namespace Minibus\Model\Process\DataTransfer;

use Minibus\Model\Entity\Execution;
use Zend\Log\Logger;
use Minibus\Controller\Process\Service\Connection\EndpointConnectionBuilder;
use Zend\Config\Config;
use Minibus\Model\Io\Sftp\SftpClient;
use Minibus\Model\Process\Conversion\IConverter;
use Minibus\Model\Io\Rest\Client;

interface DataTransferAgentInterface
{

    public function run();

    /**
     *
     * @param string $executionMode            
     */
    public function setExecutionMode($executionMode);

    /**
     *
     * @return string
     */
    public function getExecutionMode();

    /**
     *
     * @param IConverter $converter            
     */
    public function setConverter(IConverter $converter);

    /**
     *
     * @param \Minibus\Model\Entity\Execution $execution            
     */
    public function setExecution(Execution $execution);

    /**
     *
     * @param Logger $logger            
     */
    public function setLogger(Logger $logger);

    /**
     *
     * @param string $logFilePath            
     */
    public function setLogFilePath($logFilePath);

    /**
     *
     * @return \Zend\Log\Logger
     */
    public function getLogger();

    /**
     *
     * @param EndPointConnection $endPointConnection            
     */
    public function setEndPointConnection(EndPointConnection $endPointConnection);

    /**
     *
     * @return EndPointConnection
     */
    public function getEndPointConnection();

    /**
     *
     * @return \Minibus\Model\Entity\Execution
     */
    public function getExecution();

    /**
     *
     * @return string
     */
    public function getConnectionType();

    /**
     *
     * @param Config $parameters            
     */
    public function setConnectionParameters(Config $parameters);

    /**
     *
     * @return Config
     */
    public function getConnectionParameters();

    /**
     *
     * @return bool
     */
    public function hasConnection();

    /**
     * Redirige les messages d'erreur vers le fichier de log de l'exécution en cours
     */
    public function redirectErrorMessages();

    /**
     * Inject la liste des id d'objets déjà concernés par des alertes
     */
    public function setIdObjectsAlertList(array $idObjectsAlertList);

    /**
     * Inject la liste des verrous
     */
    public function setLocks(array $locks);

    /**
     * Tente d'acquérir les verrous
     */
    public function acquireLocks();

    /**
     *
     * @param array $processIdentifiers            
     */
    public function setProcessIdentifiers(array $processIdentifiers);
}