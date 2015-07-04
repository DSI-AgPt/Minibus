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
class AlertStructureRestController extends AbstractRestfulController
{

    /**
     * (non-PHPdoc)
     * 
     * @see \Zend\Mvc\Controller\AbstractRestfulController::getList()
     */
    public function getList()
    {
        $routeMatch = $this->getEvent()->getRouteMatch();
        
        $type = $this->params('type', false);
        
        $alerts = $this->getAlertDataHandler()->getAlertsStructure($type);
        $response = $this->getResponse();
        $response->getHeaders()->addHeaderLine('Content-Type', 'application/json');
        $serializer = $this->getSerializer();
        $alertssJson = $serializer->serialize($alerts, 'json');
        $response->setContent($alertssJson);
        return $response;
    }

    /**
     *
     * @return \Minibus\Controller\Alert\Service\AlertDataHandler
     */
    private function getAlertDataHandler()
    {
        return $this->getServiceLocator()->get('alert-data-handler');
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
