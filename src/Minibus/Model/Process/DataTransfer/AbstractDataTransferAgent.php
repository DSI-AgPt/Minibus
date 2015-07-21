<?php
namespace Minibus\Model\Process\DataTransfer;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Minibus\Model\Entity\Execution;
use Zend\Log\Logger;
use Zend\ServiceManager\ServiceLocatorInterface;
use Minibus\Controller\Process\Exception\ProcessException;
use Minibus\Controller\Process\Service\Connection\EndpointConnectionBuilder;
use Zend\Config\Config;
use Minibus\Model\Process\Conversion\IConverter;
use Minibus\Model\Io\Scp\ScpClient;

abstract class AbstractDataTransferAgent implements DataTransferAgentInterface, ServiceLocatorAwareInterface
{
    
    use \Minibus\Util\Traits\EntityManagerTrait;

    const MODE_SYNC = "sync";

    const MODE_RESYNC = "resync";

    const MODE_CONTROL = "control";

    /**
     *
     * @var IConverter
     */
    private $converter;

    /**
     *
     * @var string
     */
    private $executionMode = self::MODE_SYNC;

    /**
     *
     * @var array
     */
    private static $fileLocks = array();

    /**
     *
     * @var ServiceLocatorInterface $serviceLocator
     */
    protected $serviceLocator;

    /**
     *
     * @var \Minibus\Model\Entity\Execution
     */
    protected $execution;

    /**
     *
     * @var \Minibus\Model\Entity\Execution
     */
    protected static $_execution;

    /**
     *
     * @var \Zend\Log\Logger
     */
    protected static $logger;

    /**
     *
     * @var \Minibus\Controller\Alert\Service\AlertHandler
     */
    private static $alerthandler;

    /**
     *
     * @var EndPointConnection
     */
    protected $endPointConnection;

    /**
     *
     * @var \Zend\Config\Config
     */
    protected $connectionParameters;

    /**
     *
     * @var string
     */
    protected $logFilePath;

    /**
     *
     * @var array
     */
    protected $idObjectsAlertList;

    /**
     *
     * @var array
     */
    protected $locks;

    /**
     *
     * @var array
     */
    protected $processIdentifiers;

    /**
     * Set service locator
     *
     * @param ServiceLocatorInterface $serviceLocator            
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
        $this->setAlertHandler();
    }

    /**
     * Get service locator
     *
     * @return ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }

    /**
     *
     * @param \Minibus\Model\Entity\Execution $execution            
     */
    public function setExecution(Execution $execution)
    {
        self::$_execution = $this->execution = $execution;
    }

    /**
     *
     * @return \Minibus\Model\Entity\Execution
     */
    public function getExecution()
    {
        return self::$_execution;
    }

    /**
     *
     * @return \Minibus\Model\Entity\Process
     */
    protected function getProcess()
    {
        $processStateHandler = $this->getProcessStateHandler();
        $identifiers = $this->getProcessIdentifiers();
        return $processStateHandler->getProcess($identifiers['mode'], $identifiers['type'], $identifiers['endpoint'], $identifiers['annee']);
    }

    /**
     *
     * @return \Minibus\Controller\Alert\Service\AlertHandler
     */
    protected function setAlertHandler()
    {
        self::$alerthandler = $this->getServiceLocator()->get('alert-handler');
    }

    /**
     *
     * @return \Minibus\Model\Process\DataTransfer\Hash\HashCalculator
     */
    protected function getHashCalculator()
    {
        return $this->getServiceLocator()->get('hash-calculator');
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Minibus\Model\Process\DataTransfer\DataTransferAgentInterface::setLogger()
     */
    public function setLogger(Logger $logger)
    {
        self::$logger = $logger;
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Minibus\Model\Process\DataTransfer\DataTransferAgentInterface::getLogger()
     */
    public function getLogger()
    {
        return self::$logger;
    }

    /**
     *
     * @param string $message            
     * @param array $extra            
     */
    public static function logInfo($message, array $extra = array())
    {
        if (is_null(self::$logger))
            throw new ProcessException("Impossible de loguer le message d'information : " . $message);
        else
            self::$logger->info($message, $extra);
    }

    /**
     *
     * @param string $message            
     * @param array $extra            
     */
    public static function logWarn($message, array $extra = array())
    {
        if (is_null(self::$logger))
            throw new ProcessException("Impossible de loguer le message d'avertissement : " . $message);
        self::$logger->warn($message, $extra);
    }

    /**
     *
     * @param string $message            
     * @param array $extra            
     */
    public static function logError($message, array $extra = array())
    {
        if (is_null(self::$logger))
            throw new ProcessException("Impossible de loguer le message d'erreur : " . $message);
        self::$logger->err($message, $extra);
    }

    /**
     *
     * @param string $message            
     */
    public static function alertWarn($message, $objectIdentifier = null)
    {
        self::alert($message, "WARNING", $objectIdentifier);
    }

    /**
     *
     * @param string $message            
     */
    public static function alertError($message, $objectIdentifier = null)
    {
        self::alert($message, "ERROR", $objectIdentifier);
    }

    /**
     *
     * @param string $message            
     */
    public static function alertAlert($message, $objectIdentifier = null)
    {
        self::alert($message, "ALERT", $objectIdentifier);
    }

    /**
     *
     * @param string $message            
     * @param string $level            
     */
    private static function alert($message, $level, $objectIdentifier = null)
    {
        if (is_null(self::$alerthandler) || is_null(self::$_execution))
            throw new ProcessException("Impossible de lancer l'alerte : " . $message);
        self::$alerthandler->alert($message, $level, self::$_execution, $objectIdentifier);
    }

    /**
     * Indique que le processus est encore en vie et ne doit pas être tué par les exécuteurs.
     * Un processus qui n'appelle pas setAlive(true) au moins une fois par minute sera nettoyé au bout de deux minutes.
     *
     * @param bool $bool            
     * @throws ProcessException
     */
    protected function setAlive($bool)
    {
        $executionId = $this->getExecution()->getId();
        $this->getLogger()->info("*****Execution {$executionId} : alive flag set to " . ($bool === true) ? 'true' : 'false');
        
        $this->getEntityManager()->clear($this->getProcess());
        $this->getEntityManager()->clear($this->getExecution());
        $this->getEntityManager()->refresh($this->getProcess());
        $this->setExecution($this->getEntityManager()
            ->getRepository('Minibus\Model\Entity\Execution')
            ->find($executionId));
        if ($bool && $this->getProcess()->getInterrupted()) {
            $this->getEntityManager()->refresh($this->getExecution());
            $this->getProcess()->setRunning(false);
            $this->getProcess()->setAlive(false);
            $this->getExecution()->setState(Execution::STOPPED_STATE);
            $this->getProcess()->setInterrupted(false);
            throw new ProcessException("L'exécution " . $this->getExecution()->getId() . " a été interrompue ");
        }
        $this->getProcess()->setRunning($bool);
        $this->getProcess()->setAlive($bool);
        $this->getExecution()->setState($bool ? Execution::RUNNING_STATE : Execution::STOPPED_STATE);
        $this->getEntityManager()->flush($this->getProcess());
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Minibus\Model\Process\DataTransfer\DataTransferAgentInterface::getEndPointConnection()
     */
    public function getEndPointConnection()
    {
        if (! isset($this->endPointConnection))
            $this->initEndPointConnection();
        if (! isset($this->endPointConnection))
            throw new ProcessException("No connection available for" . get_called_class());
        return $this->endPointConnection;
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Minibus\Model\Process\DataTransfer\DataTransferAgentInterface::setEndPointConnection()
     */
    public function setEndPointConnection(EndPointConnection $endPointConnection)
    {
        $this->endPointConnection = $endPointConnection;
    }

    /**
     */
    private function initEndPointConnection()
    {
        $endPointConnectionBuilder = $this->getEndpointConnectionBuilder();
        $endPointConnectionBuilder->visit($this);
    }

    /**
     *
     * @return string
     */
    public function getConnectionType()
    {
        return $this->connectionParameters->get('type');
    }

    /**
     *
     * @return array
     */
    public function getConnectionParameters()
    {
        return $this->connectionParameters->get('params');
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Minibus\Model\Process\DataTransfer\DataTransferAgentInterface::setConnectionParameters()
     */
    public function setConnectionParameters(Config $parameters)
    {
        $this->connectionParameters = $parameters;
    }

    /**
     *
     * @return \Minibus\Controller\Process\Connection\EndpointConnectionBuilder
     */
    protected function getEndpointConnectionBuilder()
    {
        return $this->getServiceLocator()->get('endpoint-connection-builder');
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Minibus\Model\Process\DataTransfer\DataTransferAgentInterface::hasConnection()
     */
    public function hasConnection()
    {
        return true;
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Minibus\Model\Process\DataTransfer\DataTransferAgentInterface::setLogFilePath()
     */
    public function setLogFilePath($logFilePath)
    {
        $this->logFilePath = $logFilePath;
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Minibus\Model\Process\DataTransfer\DataTransferAgentInterface::setLogFilePath()
     */
    public function getLogFilePath()
    {
        return $this->logFilePath;
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Minibus\Model\Process\DataTransfer\DataTransferAgentInterface::redirectErrorMessages()
     */
    public function redirectErrorMessages()
    {
        ini_set("log_errors", 1);
        ini_set("error_log", $this->getLogFilePath());
    }

    /**
     *
     * @return integer
     */
    protected function getAnneeScolaire()
    {
        return $this->getProcess()->getAnnee();
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Minibus\Model\Process\DataTransfer\DataTransferAgentInterface::setExecutionMode()
     */
    public function setExecutionMode($executionMode)
    {
        $this->executionMode = $executionMode;
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Minibus\Model\Process\DataTransfer\DataTransferAgentInterface::getExecutionMode()
     */
    public function getExecutionMode()
    {
        return $this->executionMode;
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Minibus\Model\Process\DataTransfer\DataTransferAgentInterface::setConverter()
     */
    public function setConverter(IConverter $converter)
    {
        $this->converter = $converter;
    }

    /**
     *
     * @return IConverter
     */
    protected function getConverter()
    {
        if (is_null($this->converter))
            throw new \Exception($this->translate("No converter defined in configuration for datatransfer ") . get_called_class());
        return $this->converter;
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Minibus\Model\Process\DataTransfer\DataTransferAgentInterface::setIdObjectsAlertList()
     */
    public function setIdObjectsAlertList(array $idObjectsAlertList)
    {
        $this->idObjectsAlertList = $idObjectsAlertList;
        self::logInfo($this->translate("Objects subject to an alert") . " : \r\n");
        self::logInfo(print_r($idObjectsAlertList, true));
    }

    /**
     *
     * @return array:
     */
    protected function getIdObjectsAlertList()
    {
        return $this->idObjectsAlertList;
    }

    /**
     *
     * @param string $objectIdentifier            
     * @return boolean
     */
    protected function isInAlert($objectIdentifier)
    {
        return in_array($objectIdentifier, $this->getIdObjectsAlertList());
    }

    /**
     *
     * @return array:
     */
    protected function getProcessIdentifiers()
    {
        return $this->processIdentifiers;
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Minibus\Model\Process\DataTransfer\DataTransferAgentInterface::setProcessIdentifiers()
     */
    public function setProcessIdentifiers(array $processIdentifiers)
    {
        $this->processIdentifiers = $processIdentifiers;
    }

    /**
     *
     * @return \Minibus\Model\Process\Service\ProcessStateHandler
     */
    protected function getProcessStateHandler()
    {
        return $this->getServiceLocator()->get('process-state-handler');
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Minibus\Model\Process\DataTransfer\DataTransferAgentInterface::setLocks()
     */
    public function setLocks(array $locks)
    {
        $this->locks = $locks;
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Minibus\Model\Process\DataTransfer\DataTransferAgentInterface::acquireLocks()
     */
    public function acquireLocks()
    {
        if (! isset($this->locks) && ! is_array($this->locks)) {
            $message = "Il ne semble pas y avoir de verrous définis pour ce process. Arrêt";
            self::getLogger()->err($message);
            self::alertError($message);
            $this->setAlive(false);
            return false;
        }
        foreach ($this->locks as $name => $mode) {
            self::getLogger()->info($this->translate("Trying to acquire the lock ") . $name . " " . $this->translate("in mode") . " : " . ($mode == LOCK_EX ? $this->translate('exclusive') : $this->translate('shared')));
            if (true === $this->lockFile($name, $mode))
                self::getLogger()->info($this->translate("Successfully acquired the lock ") . $name . " " . $this->translate("in mode") . " : " . ($mode == LOCK_EX ? $this->translate('exclusive') : $this->translate('shared')));
            else {
                $message = $this->translate("Unable to acquire the lock ") . $name . " " . $this->translate("in mode") . " : " . ($mode == LOCK_EX ? $this->translate('exclusive') : $this->translate('shared'));
                self::getLogger()->err($message);
                self::alertError($message);
                $this->setAlive(false);
                return false;
            }
        }
        return true;
    }

    private function lockFile($name, $mode)
    {
        self::$fileLocks[$name] = fopen($this->getLockFileName($name), 'w+');
        if (! flock(self::$fileLocks[$name], $mode))
            return false;
        return true;
    }

    private function getLockFileName($name)
    {
        $name = sprintf('/tmp/MDM_LOCK_%s.sem', $name);
        return $name;
    }

    /**
     *
     * @return \Zend\I18n\Translator\Translator
     */
    private function getTranslator()
    {
        return $this->getServiceLocator()->get('Translator');
    }

    /**
     *
     * @param string $message            
     * @param string $textDomain            
     * @param string $locale            
     * @return string
     */
    protected function translate($message, $textDomain = 'default', $locale = null)
    {
        return $this->getTranslator()->translate($message, $textDomain, $locale);
    }
}