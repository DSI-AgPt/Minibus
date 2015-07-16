<?php
namespace Minibus\Controller\Auth\Service;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\Log\Logger;
use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Mvc\MvcEvent;
use Zend\Console\Request as ConsoleResquest;

/**
 *
 * @author Joachim Dornbusch
 * @copyright Joachim Dornbusch -AgroParisTech 2014-2015
 *            @30 juin 2015
 */
class ZfcUserRedirectionListener implements ServiceLocatorAwareInterface, ListenerAggregateInterface
{
    
    use \Minibus\Util\Traits\ServiceLocatorAwareTrait;

    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH, array(
            $this,
            'checkAuthentication'
        ), 200);
    }

    public function detach(EventManagerInterface $events)
    {
        // TODO implémenter
    }

    public function checkAuthentication(MvcEvent $event)
    {
        if ($event->getRequest() instanceof ConsoleResquest)
            return;
        $routename = $event->getRouteMatch()->getMatchedRouteName();
        // TODO mettre en conf
        if ($routename == 'zfcuser/login' || $routename == 'zfcuser/logout' || $routename == 'execution')
            return;
        
        $zfcUser = $this->getServiceLocator()->get('zfcuser_auth_service');
        $config = $this->getServiceLocator()->get('Config');
        $authorized = false;
        if ($zfcUser->hasidentity())
            $authorized = $this->getFileAuthService()->checkAutorisation($zfcUser->getIdentity()
                ->getUserName(), $routename);
        if (! $zfcUser->hasIdentity() || true !== $authorized) {
            $url = $event->getRouter()->assemble(array(), array(
                'name' => 'zfcuser/login'
            ));
            $response = $event->getResponse();
            $response->getHeaders()->addHeaderLine('Location', $url);
            $response->setStatusCode(302);
            $response->sendHeaders();
            $stopCallBack = function ($event) use($response)
            {
                $event->stopPropagation();
                return $response;
            };
            $event->getApplication()
                ->getEventManager()
                ->attach(MvcEvent::EVENT_ROUTE, $stopCallBack, - 10000);
            return $response;
        }
    }

    /**
     *
     * @return \Minibus\Controller\Auth\Service\FileAuthService
     */
    private function getFileAuthService()
    {
        return $this->getServiceLocator()->get('file-auth-service');
    }
}