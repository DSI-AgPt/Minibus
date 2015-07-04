<?php
namespace Minibus\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\Serializer\Adapter\Json;
use Minibus\Model\Process\Conversion\AbstractConverterFactory;
use Minibus\Model\Browse\Factory\AbstractFormatterFactory;

/**
 *
 * @author Joachim Dornbusch
 * @copyright Joachim Dornbusch -AgroParisTech 2014-2015
 *            @30 juin 2015
 */
class DataRestController extends AbstractRestfulController
{

    /**
     * (non-PHPdoc)
     *
     * @see \Zend\Mvc\Controller\AbstractRestfulController::getList()
     */
    public function getList()
    {
        $response = $this->getResponse();
        $response->getHeaders()->addHeaderLine('Content-Type', 'application/json');
        $type = $this->params()->fromRoute("type", null);
        $anneeScolaire = $this->params()->fromRoute("annee_scolaire", null);
        if (! is_null($type)) {
            $formatter = $this->getServiceLocator()->get($type . AbstractFormatterFactory::$termination);
            $columns = $this->params()->fromQuery("columns", null);
            $start = $this->params()->fromQuery("start", null);
            $length = $this->params()->fromQuery("length", null);
            $order = $this->params()->fromQuery("order", null);
            $search = $this->params()->fromQuery("search", null);
            $data = $formatter->getData($columns, $start, $length, $order, $search, $anneeScolaire);
            $response->setContent(\Zend\Json\Json::encode($data));
        }
        
        return $response;
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Zend\Mvc\Controller\AbstractRestfulController::create()
     */
    public function create($data)
    {
        $this->getConfigurationHandler()
            ->setConfigurationData($data)
            ->save();
        return $this->getList();
    }

    /**
     *
     * @return \Minibus\Model\Configuration\Service\ConfigurationHandler
     */
    private function getConfigurationHandler()
    {
        return $this->getServiceLocator()->get('configuration-handler');
    }

    /**
     *
     * @return array
     */
    private function getConfiguration()
    {
        return $this->getConfigurationHandler()->getConfiguration();
    }
}
