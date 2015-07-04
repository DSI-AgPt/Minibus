<?php
namespace Minibus\Controller\Auth\Service;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\Log\Logger;
use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Mvc\MvcEvent;
use Zend\Config\Reader\Ini;
use Zend\Permissions\Acl\Role\GenericRole;
use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Resource\GenericResource;

/**
 *
 * @author Joachim Dornbusch
 * @copyright Joachim Dornbusch -AgroParisTech 2014-2015
 *            @30 juin 2015
 */
class FileAuthService implements ServiceLocatorAwareInterface
{
    
    use\Minibus\Util\Traits\ServiceLocatorAwareTrait;

    /**
     *
     * @var Acl
     */
    private $acl;

    /**
     *
     * @param string $name            
     * @param string $routename            
     * @return boolean
     */
    public function checkAutorisation($name, $routename)
    {
        $this->initAcl();
        $config = $this->getServiceLocator()->get('Config');
        $filePath = $config['auth']['filePath'];
        $reader = new Ini();
        try {
            $usersData = $reader->fromFile($filePath);
        } catch (\Exception $e) {
            error_log($e->getMessage());
            return false;
        }
        return is_array($usersData) && array_key_exists($name, $usersData) && $this->acl->hasResource($routename) && $this->acl->hasRole($usersData[$name]) && $this->acl->isAllowed($usersData[$name], $routename);
    }

    /**
     */
    private function initAcl()
    {
        if (! is_null($this->acl))
            return;
        $this->acl = new Acl();
        $config = $this->getServiceLocator()->get('Config');
        $roles = $config['acl']['roles'];
        $allResources = array();
        foreach ($roles as $role => $resources) {
            
            $role = new GenericRole($role);
            $this->acl->addRole($role);
            
            $allResources = array_merge($resources, $allResources);
            
            foreach ($resources as $resource) {
                if (! $this->acl->hasResource($resource))
                    $this->acl->addResource(new GenericResource($resource));
            }
            
            foreach ($allResources as $resource) {
                $this->acl->allow($role, $resource);
            }
        }
    }
}