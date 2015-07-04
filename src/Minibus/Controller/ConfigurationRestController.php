<?php
namespace Minibus\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\Serializer\Adapter\Json;

/**
 *
 * @author Joachim Dornbusch
 * @copyright Joachim Dornbusch -AgroParisTech 2014-2015
 *            @30 juin 2015
 */
class ConfigurationRestController extends AbstractRestfulController
{

    /**
     * (non-PHPdoc)
     *
     * @see \Zend\Mvc\Controller\AbstractRestfulController::getList()
     */
    public function getList()
    {
        $response = $this->getResponse();
        $configurationHandler = $this->getConfigurationHandler();
        $configuration = $configurationHandler->getConfiguration();
        $response->getHeaders()->addHeaderLine('Content-Type', 'application/json');
        $adapter = new Json();
        $response->setContent($adapter->serialize($configuration));
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
