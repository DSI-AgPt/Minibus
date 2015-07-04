<?php
namespace Minibus\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\Serializer\Adapter\Json;
use Minibus\Controller\Exceptions\RestApiException;

/**
 *
 * @author Joachim Dornbusch
 * @copyright Joachim Dornbusch -AgroParisTech 2014-2015
 *            @30 juin 2015
 */
class ExecutionController extends AbstractRestfulController
{

    /**
     * (non-PHPdoc)
     * 
     * @see \Zend\Mvc\Controller\AbstractRestfulController::create()
     */
    public function create($data)
    {
        set_time_limit(0);
        ignore_user_abort(true);
        //TODO magical value
        ini_set('memory_limit', '1800M');
        $verbose = $this->params()->fromQuery('verbose', false);
        $verbose = ($verbose !== false);
        $responseContent = array();
        $processExecutionHandler = $this->getProcessExecutionHandler();
        $processInformation = $processExecutionHandler->executeNextProcess($verbose);
        $processUpdater = $this->getProcessUpdater();
        $processUpdater->cleanOldExecutions();
        $response = $this->getResponse();
        $response->getHeaders()->addHeaderLine('Content-Type', 'application/json');
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
