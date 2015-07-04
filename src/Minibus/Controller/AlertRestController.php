<?php
namespace Minibus\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\Serializer\Adapter\Json;
use Minibus\Controller\Form\CdmFrUploadForm;
use Zend\Debug\Debug;
use Minibus\Controller\Exceptions\RestApiException;
use JMS\Serializer\SerializerBuilder;
use Minibus\Model\Entity\Execution;

/**
 *
 * @author Joachim Dornbusch
 * @copyright Joachim Dornbusch -AgroParisTech 2014-2015
 *            @30 juin 2015
 */
class AlertRestController extends AbstractRestfulController
{

    /**
     * (non-PHPdoc)
     *
     * @see \Zend\Mvc\Controller\AbstractRestfulController::getList()
     */
    public function getList()
    {
        $routeMatch = $this->getEvent()->getRouteMatch();
        
        $selectedProcess = $this->params()->fromQuery('process', array());
        if (empty($selectedProcess))
            $selectedProcess = array();
        $selectedLevels = $this->params()->fromQuery('levels', array());
        if (empty($selectedLevels))
            $selectedLevels = array();
        $alerts = $this->getAlertDataHandler()->getAlertsForProcess($selectedProcess, $selectedLevels);
        $response = $this->getResponse();
        $response->getHeaders()->addHeaderLine('Content-Type', 'application/json');
        $serializer = $this->getSerializer();
        $alertssJson = $serializer->serialize($alerts, 'json');
        $response->setContent($alertssJson);
        return $response;
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Zend\Mvc\Controller\AbstractRestfulController::delete()
     */
    public function delete($ids)
    {
        $ids = json_decode($ids);
        if (is_array($ids))
            $nbDeleted = $this->getAlertDataHandler()->removeAlerts(array_values($ids));
        $deleted = array(
            "deleted" => $nbDeleted
        );
        $response = $this->getResponse();
        $response->getHeaders()->addHeaderLine('Content-Type', 'application/json');
        $serializer = $this->getSerializer();
        $alertssJson = $serializer->serialize($deleted, 'json');
        $response->setContent($alertssJson);
        return $response;
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Zend\Mvc\Controller\AbstractRestfulController::deleteList()
     */
    public function deleteList()
    {
        $process = $this->params()->fromQuery('process');
        $levels = $this->params()->fromQuery('levels');
        $nbDeleted = 0;
        
        if (! empty($process) && ! empty($levels)) {
            
            $process = explode(',', $process);
            $process = array_map(function ($proces)
            {
                $parts = array_merge(explode('-', $proces), array(
                    null,
                    null,
                    null
                ));
                return array(
                    'mode' => $parts[0],
                    'type' => $parts[1],
                    'endpoint' => $parts[2]
                );
            }, $process);
            $levels = explode(',', $levels);
            $nbDeleted = $this->getAlertDataHandler()->removeAllAlerts($process, $levels);
        }
        
        $deleted = array(
            "deleted" => $nbDeleted
        );
        $response = $this->getResponse();
        $response->getHeaders()->addHeaderLine('Content-Type', 'application/json');
        $serializer = $this->getSerializer();
        $alertssJson = $serializer->serialize($deleted, 'json');
        $response->setContent($alertssJson);
        return $response;
    }

    /**
     *
     * @return \JMS\Serializer\Serializer
     */
    private function getSerializer()
    {
        return $this->getServiceLocator()->get('jms_serializer.serializer');
    }

    /**
     *
     * @return \Minibus\Controller\Alert\Service\AlertDataHandler
     */
    private function getAlertDataHandler()
    {
        return $this->getServiceLocator()->get('alert-data-handler');
    }
}
