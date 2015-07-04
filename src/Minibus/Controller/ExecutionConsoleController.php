<?php
namespace Minibus\Controller;

use Zend\Serializer\Adapter\Json;
use Zend\Mvc\Controller\AbstractActionController;

class ExecutionConsoleController extends AbstractActionController
{

    /**
     *
     * @return boolean|\Zend\Stdlib\ResponseInterface
     */
    public function executeAction()
    {
        set_time_limit(0);
        // TODO magical value
        ini_set('memory_limit', '1800M');
        $verbose = true;
        $responseContent = array();
        $processExecutionHandler = $this->getProcessExecutionHandler();
        $processInformation = $processExecutionHandler->executeNextProcess($verbose);
        $processUpdater = $this->getProcessUpdater();
        $processUpdater->cleanOldExecutions();
        $response = $this->getResponse();
        $adapter = new Json();
        $response->setContent($adapter->serialize($processInformation));
        return $response;
    }

    /**
     *
     * @return \Minibus\Controller\Process\Service\ProcessExecutionHandler
     */
    private function getProcessExecutionHandler()
    {
        return $this->getServiceLocator()->get('process-execution-handler');
    }

    /**
     *
     * @return \Minibus\Model\Process\Service\ProcessUpdater
     */
    private function getProcessUpdater()
    {
        return $this->getServiceLocator()->get('process-updater');
    }
}
