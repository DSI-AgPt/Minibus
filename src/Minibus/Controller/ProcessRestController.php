<?php
namespace Minibus\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\Serializer\Adapter\Json;
use Minibus\Controller\Form\CdmFrUploadForm;
use Zend\Debug\Debug;
use Minibus\Controller\Exceptions\RestApiException;
use JMS\Serializer\SerializerBuilder;

/**
 *
 * @author Joachim Dornbusch
 * @copyright Joachim Dornbusch -AgroParisTech 2014-2015
 *            @30 juin 2015
 */
class ProcessRestController extends AbstractRestfulController
{

    /**
     * (non-PHPdoc)
     *
     * @see \Zend\Mvc\Controller\AbstractRestfulController::create()
     */
    public function create($data)
    {
        // TODO refacto translate
        $mode = $this->params('mode', false);
        $type = $this->params('type', false);
        $endpoint = $this->params('endpoint', false);
        $annee = $this->params('annee', 0);
        $processStateHandler = $this->getProcessStateHandler();
        $processExecutionHandler = $this->getProcessExecutionHandler();
        $processExecutionHandler->cleanProcesses();
        
        $process = $processStateHandler->getProcess($mode, $type, $endpoint, $annee);
        if (is_null($process) || ! $process instanceof \Minibus\Model\Entity\Process)
            throw new RestApiException(array(
                "not-found" => "Le process $mode-$type-$endpoint-$annee n'existe pas "
            ));
        else 
            if ($process instanceof \Minibus\Model\Entity\Process) {
                if ($process->getActive() !== true)
                    throw new RestApiException(array(
                        "inactive" => "Le process $mode-$type-$endpoint-$annee n'est pas actif"
                    ));
                if ($process->getRunning() == true)
                    throw new RestApiException(array(
                        "running" => "Le process $mode-$type-$endpoint-$annee est en cours d'exécution"
                    ));
                $processStateHandler->askImmediateExecution($process, $data);
                $response = $this->getResponse();
                $response->getHeaders()->addHeaderLine('Content-Type', 'application/json');
                $serializer = $this->getSerializer();
                $response->setContent($serializer->serialize($process, 'json'));
                return $response;
            }
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Zend\Mvc\Controller\AbstractRestfulController::get()
     */
    public function get($id)
    {
        $mode = $this->params('mode', false);
        $type = $this->params('type', false);
        $endpoint = $this->params('endpoint', false);
        $annee = $this->params('annee', 0);
        $processStateHandler = $this->getProcessStateHandler();
        $process = $processStateHandler->getProcess($mode, $type, $endpoint, $annee);
        if (is_null($process))
            $process = $processStateHandler->getDefaultProcess($mode, $type, $endpoint, $annee);
        $response = $this->getResponse();
        $response->getHeaders()->addHeaderLine('Content-Type', 'application/json');
        $serializer = $this->getSerializer();
        $processJson = $serializer->serialize($process, 'json');
        $response->setContent($processJson);
        
        return $response;
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Zend\Mvc\Controller\AbstractRestfulController::update()
     */
    public function update($id, $data)
    {
        $routeMatch = $this->getEvent()->getRouteMatch();
        $mode = $routeMatch->getParam('mode', false);
        $type = $routeMatch->getParam('type', false);
        $endpoint = $routeMatch->getParam('endpoint', false);
        $annee = $routeMatch->getParam('annee', 0);
        
        $processStateHandler = $this->getProcessStateHandler();
        $process = $processStateHandler->getProcess($mode, $type, $endpoint, $annee);
        
        if (is_null($process)) {
            $process = $processStateHandler->getDefaultProcess($mode, $type, $endpoint, $annee);
            $processStateHandler->saveProcess($process);
        }
        if (array_key_exists('force-execution-stop', $data)) {
            $process->setInterrupted(true);
        } else {
            $active = array_key_exists('active', $data) && $data['active'] === 'true';
            $process->setActive($active);
            $process->setShedule($data['shedule'] === 'true' && $data['active'] === 'true');
            $process->setCron($data['cron']);
        }
        
        $processStateHandler->saveProcess($process);
        $response = $this->getResponse();
        $response->getHeaders()->addHeaderLine('Content-Type', 'application/json');
        $serializer = $this->getSerializer();
        $processJson = $serializer->serialize($process, 'json');
        $response->setContent($processJson);
        return $response;
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Zend\Mvc\Controller\AbstractRestfulController::getList()
     */
    public function getList()
    {
        $routeMatch = $this->getEvent()->getRouteMatch();
        $mode = $routeMatch->getParam('mode', false);
        $annee = $routeMatch->getParam('annee', 0);
        $processStateHandler = $this->getProcessStateHandler();
        $processes = $processStateHandler->getRunningProcessList($mode, $annee);
        $response = $this->getResponse();
        $response->getHeaders()->addHeaderLine('Content-Type', 'application/json');
        $serializer = $this->getSerializer();
        $processJson = $serializer->serialize($processes, 'json');
        $response->setContent($processJson);
        return $response;
    }

    /**
     *
     * @see \Zend\Mvc\Controller\AbstractRestfulController::getIdentifier()
     */
    protected function getIdentifier($routeMatch, $request)
    {
        $routeMatch = $this->getEvent()->getRouteMatch();
        $mode = $routeMatch->getParam('mode', false);
        $type = $routeMatch->getParam('type', false);
        $endpoint = $routeMatch->getParam('endpoint', false);
        $annee = $routeMatch->getParam('annee', 0);
        if ($mode !== false && $type !== false && $endpoint !== false) {
            $id = $mode . '-' . $type . '-' . $endpoint;
            $id .= '-' . $annee;
            return $id;
        }
        return false;
    }

    /**
     *
     * @return \Minibus\Model\Process\Service\ProcessStateHandler
     */
    private function getProcessStateHandler()
    {
        return $this->getServiceLocator()->get('process-state-handler');
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
     * @return \JMS\Serializer\Serializer
     */
    private function getSerializer()
    {
        return $this->getServiceLocator()->get('jms_serializer.serializer');
    }
}
