<?php
namespace Minibus\Model\Browse\Factory;

use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Minibus\Controller\Process\Exception\ProcessException;

/**
 *
 * @author Joachim Dornbusch
 * @copyright Joachim Dornbusch - AgroParisTech 2014-2015
 *            @30 juin 2015
 */
class AbstractFormatterFactory implements AbstractFactoryInterface
{

    /**
     *
     * @var array
     */
    private $formatterIndex;

    /**
     *
     * @var string
     */
    public static $termination = "_ajax_formatter";

    /**
     * (non-PHPdoc)
     *
     * @see \Zend\ServiceManager\AbstractFactoryInterface::createServiceWithName()
     */
    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        try {
            $className = $this->getFormaterIndex($serviceLocator)[$this->removeTermination($requestedName)];
            $reflectionClass = new \ReflectionClass($className);
            $instance = $reflectionClass->newInstance();
        } catch (\Exception $e) {
            throw new ProcessException($reflectionClass, $e->getCode(), $e);
        }
        
        return $instance;
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Zend\ServiceManager\AbstractFactoryInterface::canCreateServiceWithName()
     */
    public function canCreateServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        return substr($requestedName, - strlen(self::$termination)) === self::$termination && array_key_exists($this->removeTermination($requestedName), $this->getFormaterIndex($serviceLocator));
    }

    /**
     *
     * @param string $processidentifier            
     * @return string
     */
    public function removeTermination($requestedName)
    {
        return str_replace(self::$termination, '', $requestedName);
    }

    /**
     *
     * @return \Minibus\Model\Configuration\Service\DataTypesHandler
     */
    private function getDataTypesHandler(ServiceLocatorInterface $serviceLocator)
    {
        return $serviceLocator->get('datatypes-handler');
    }

    /**
     *
     * @param ServiceLocatorInterface $serviceLocator            
     * @return array
     */
    private function getFormaterIndex(ServiceLocatorInterface $serviceLocator)
    {
        if (! isset($this->formatterIndex))
            $this->formatterIndex = $this->getDataTypesHandler($serviceLocator)->getFormatterIndex();
        return $this->formatterIndex;
    }
}
