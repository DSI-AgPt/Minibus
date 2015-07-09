<?php
namespace Minibus\Controller\Process\AbstractFactory;

use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Minibus\Model\Entity\Execution;
use Minibus\Controller\Exceptions\RestApiException;
use Doctrine\DBAL\DBALException;
use Minibus\Model\Process\DataTransfer\AbstractDataTransferAgent;
use Minibus\Controller\Process\Exception\ProcessException;
use Minibus\Model\Process\DataTransfer\DataTransferAgentInterface;
use Zend\Config\Config;

/**
 *
 * @author Joachim Dornbusch
 * @copyright Joachim Dornbusch -AgroParisTech 2014-2015
 *            @30 juin 2015
 */
class AbstractDataTransferFactory implements AbstractFactoryInterface
{

    /**
     *
     * @var Config
     */
    protected $config;

    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        $identifiers = self::splitIntoIdentifiers($requestedName);
        $dataTransferAgentClassName = $this->getDatatypesHandler($serviceLocator)->getTransferAgentClassName($identifiers['mode'], $identifiers['type'], $identifiers['endpoint']);
        $reflectionClass = new \ReflectionClass($dataTransferAgentClassName);
        $dataTransferAgent = $reflectionClass->newInstance();
        if ($dataTransferAgent instanceof \Minibus\Model\Process\DataTransfer\DataTransferAgentInterface) {
            
            try {
                $this->injectConverter($serviceLocator, $dataTransferAgent, $identifiers);
                $this->injectExecution($serviceLocator, $dataTransferAgent, $identifiers);
                $this->injectLogger($serviceLocator, $dataTransferAgent);
                if ($dataTransferAgent->hasConnection())
                    $this->injectConnectionParameters($serviceLocator, $dataTransferAgent, $identifiers['endpoint'], new Config($serviceLocator->get('Config')));
                $this->injectAlertList($serviceLocator, $dataTransferAgent, $identifiers);
                $this->injectLocks($serviceLocator, $dataTransferAgent, $identifiers);
            } catch (\Exception $e) {
                $execution = $dataTransferAgent->getExecution();
                if (isset($execution)) {
                    $execution->setState(Execution::STOPPED_STATE);
                    $this->getEntityManager($serviceLocator)->flush();
                }
                throw new ProcessException($e->getMessage(), $e->getCode(), $e);
            }
            
            return $dataTransferAgent;
        }
        return null;
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Zend\ServiceManager\AbstractFactoryInterface::canCreateServiceWithName()
     */
    public function canCreateServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        try {
            $identifiers = self::splitIntoIdentifiers($requestedName);
        } catch (ProcessException $e) {
            return false;
        }
        
        return $this->getDatatypesHandler($serviceLocator)->hasDataType($identifiers['mode'], $identifiers['type'], $identifiers['endpoint']);
    }

    /**
     *
     * @param string $name
     *            un nom formaté en mode-type-endpoint-annee
     * @throws ProcessException
     * @return array un tableau associatif
     */
    public static function splitIntoIdentifiers($name)
    {
        $flatArray = explode('-', $name);
        if (count($flatArray) == 3)
            array_push($flatArray, '0');
        if (count($flatArray) != 4)
            throw new ProcessException("Impossible d'analyser l'identifiant de process $name");
        return array(
            "mode" => $flatArray[0],
            "type" => $flatArray[1],
            "endpoint" => $flatArray[2],
            "annee" => $flatArray[3]
        );
    }

    /**
     *
     * @param ServiceLocatorInterface $serviceLocator            
     * @param DataTransferAgentInterface $dataTransferAgent            
     * @param string $endpoint            
     * @param Config $config            
     * @throws ProcessException
     */
    private function injectConnectionParameters(ServiceLocatorInterface $serviceLocator, DataTransferAgentInterface $dataTransferAgent, $endpoint, Config $config)
    {
        $connectionParameters = $config->data_endpoints->$endpoint;
        if (is_null($connectionParameters))
            throw new ProcessException($this->translate($serviceLocator, "No connection seems configured for application") . " " . $endpoint);
        $dataTransferAgent->setConnectionParameters($connectionParameters);
    }

    /**
     *
     * @param ServiceLocatorInterface $serviceLocator            
     * @param DataTransferAgentInterface $dataTransferAgent            
     * @param array $identifiers            
     */
    private function injectAlertList(ServiceLocatorInterface $serviceLocator, DataTransferAgentInterface $dataTransferAgent, array $identifiers)
    {
        $idObjectsAlertList = $this->getAlertDataHandler($serviceLocator)->getIdObjectListForProcess(array(
            $identifiers
        ));
        $dataTransferAgent->setIdObjectsAlertList($idObjectsAlertList);
    }

    /**
     *
     * @param ServiceLocatorInterface $serviceLocator            
     * @param DataTransferAgentInterface $dataTransferAgent            
     * @param array $identifiers            
     */
    private function injectLocks(ServiceLocatorInterface $serviceLocator, DataTransferAgentInterface $dataTransferAgent, array $identifiers)
    {
        $locks = $this->getDatatypesHandler($serviceLocator)->getTransferAgentLocks($identifiers['mode'], $identifiers['type'], $identifiers['endpoint']);
        $dataTransferAgent->setLocks($locks);
    }

    private function injectConverter(ServiceLocatorInterface $serviceLocator, DataTransferAgentInterface $dataTransferAgent, array $identifiers)
    {
        $converterClassName = $this->getDatatypesHandler($serviceLocator)->getConverterClassName($identifiers['mode'], $identifiers['type'], $identifiers['endpoint']);
        if (is_null($converterClassName))
            return;
        try {
            $reflectionClass = new \ReflectionClass($converterClassName);
            $converter = $reflectionClass->newInstance();
        } catch (\Exception $e) {
            throw new ProcessException($this->translate($serviceLocator, "Unable to create class") . " " . $converterClassName . " " . $this->translate($serviceLocator, "for the following reason") . " " . $e->getMessage(), $e->getCode(), $e);
        }
        if ($converter instanceof ServiceLocatorAwareInterface)
            $converter->setServiceLocator($serviceLocator);
        $dataTransferAgent->setConverter($converter);
    }

    /**
     *
     * @param ServiceLocatorInterface $serviceLocator            
     * @param DataTransferAgentInterface $dataTransferAgent            
     * @param array $identifiers            
     */
    private function injectExecution(ServiceLocatorInterface $serviceLocator, DataTransferAgentInterface $dataTransferAgent, array $identifiers)
    {
        $execution = new Execution();
        $em = $this->getEntityManager($serviceLocator);
        $processStateHandler = $this->getProcessStateHandler($serviceLocator);
        $process = $processStateHandler->getProcess($identifiers['mode'], $identifiers['type'], $identifiers['endpoint'], $identifiers['annee']);
        $process->setInterrupted(false);
        $execution->setProcess($process);
        $process->addExecution($execution);
        // exceptions rattrapées plus haut
        $em->persist($execution);
        $dataTransferAgent->setExecution($execution);
        $dataTransferAgent->setProcessIdentifiers($identifiers);
        $em->flush($process);
    }

    /**
     *
     * @param ServiceLocatorInterface $serviceLocator            
     * @param DataTransferAgentInterface $dataTransferAgent            
     */
    private function injectLogger(ServiceLocatorInterface $serviceLocator, DataTransferAgentInterface $dataTransferAgent)
    {
        $logFilePath = $this->getLogPath($dataTransferAgent->getExecution(), $serviceLocator);
        $writer = new \Zend\Log\Writer\Stream($logFilePath);
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info($this->translate($serviceLocator, "Injection of logger to file") . " " . $logFilePath);
        $dataTransferAgent->setLogger($logger);
        $dataTransferAgent->setLogFilePath($logFilePath);
    }

    /**
     *
     * @param Execution $execution            
     * @param ServiceLocatorInterface $serviceLocator            
     * @return string
     */
    private function getLogPath(Execution $execution, ServiceLocatorInterface $serviceLocator)
    {
        $logFilePath = $this->getLoghandler($serviceLocator)->getLogPath($execution->getLogidentifier());
        
        $succes = fopen($logFilePath, 'w');
        if (! $succes)
            throw new \Exception($this->translate($serviceLocator, "Unable to create file") . " " . $logFilePath);
        return $logFilePath;
    }

    /**
     *
     * @return \Doctrine\ORM\EntityManager
     */
    protected function getEntityManager(ServiceLocatorInterface $serviceLocator)
    {
        return $serviceLocator->get('doctrine.entitymanager.orm_default');
    }

    /**
     *
     * @return \Minibus\Controller\Log\Service\LogHandler
     */
    protected function getLoghandler(ServiceLocatorInterface $serviceLocator)
    {
        return $serviceLocator->get('log-handler');
    }

    /**
     *
     * @return \Minibus\Model\Process\Service\ProcessStateHandler
     */
    protected function getProcessStateHandler(ServiceLocatorInterface $serviceLocator)
    {
        return $serviceLocator->get('process-state-handler');
    }

    /**
     *
     * @return \Minibus\Controller\Alert\Service\AlertDataHandler
     */
    private function getAlertDataHandler(ServiceLocatorInterface $serviceLocator)
    {
        return $serviceLocator->get('alert-data-handler');
    }

    /**
     *
     * @param ServiceLocatorInterface $serviceLocator            
     * @return \Minibus\Model\Configuration\Service\DataTypesHandler
     */
    public function getDatatypesHandler(ServiceLocatorInterface $serviceLocator)
    {
        return $serviceLocator->get("datatypes-handler");
    }

    /**
     *
     * @param ServiceLocatorInterface $serviceLocator            
     * @return \Zend\I18n\Translator\Translator
     */
    private function getTranslator(ServiceLocatorInterface $serviceLocator)
    {
        return $this->getServiceLocator()->get('Translator');
    }

    /**
     *
     * @param ServiceLocatorInterface $serviceLocator            
     * @param string $message            
     * @param string $textDomain            
     * @param string $locale            
     * @return string
     */
    private function translate(ServiceLocatorInterface $serviceLocator, $message, $textDomain = 'default', $locale = null)
    {
        return $this->getTranslator($serviceLocator)->translate($message, $textDomain, $locale);
    }
}
