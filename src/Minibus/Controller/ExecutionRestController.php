<?php
namespace Minibus\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\Serializer\Adapter\Json;
use Minibus\Controller\Form\CdmFrUploadForm;
use Zend\Debug\Debug;
use Minibus\Controller\Exceptions\RestApiException;
use JMS\Serializer\SerializerBuilder;
use Minibus\Model\Entity\Execution;

class ExecutionRestController extends AbstractRestfulController
{

    /**
     * (non-PHPdoc)
     * 
     * @see \Zend\Mvc\Controller\AbstractRestfulController::getList()
     */
    public function getList()
    {
        $routeMatch = $this->getEvent()->getRouteMatch();
        $mode = $this->params('mode', false);
        $type = $this->params('type', false);
        $endpoint = $this->params('endpoint', false);
        $annee = $this->params('annee', 0);
        $processHandler = $this->getProcessStateHandler();
        $executions = $processHandler->getProcessExecutions($mode, $type, $endpoint, $annee);
        if ($executions instanceof \Doctrine\ORM\PersistentCollection)
            $executions = $executions->toArray();
        usort($executions, function (Execution $a, Execution $b)
        {
            return intval($a->getId()) < intval($b->getId());
        });
        $response = $this->getResponse();
        $response->getHeaders()->addHeaderLine('Content-Type', 'application/json');
        $serializer = $this->getSerializer();
        $processJson = $serializer->serialize($executions, 'json');
        $response->setContent($processJson);
        return $response;
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
     * @return \JMS\Serializer\Serializer
     */
    private function getSerializer()
    {
        return $this->getServiceLocator()->get('jms_serializer.serializer');
    }
}
