<?php

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
namespace Minibus;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Application;
use Minibus\Controller\IndexController;
use Minibus\Controller\Exceptions\RestApiException;
use Zend\Serializer\Adapter\Json;

class Module {
	public function onBootstrap(MvcEvent $e) {
		$eventManager = $e->getApplication ()->getEventManager ();
		$moduleRouteListener = new ModuleRouteListener ();
		// controller can't dispatch request action that passed to the url
		$eventManager->getSharedManager ()->attach ( 'Zend\Mvc\Controller\AbstractActionController', 'dispatch', array (
				$this,
				'handleControllerCannotDispatchRequest' 
		), 101 );
		// controller not found, invalid, or route is not matched anymore
		$eventManager->attach ( 'dispatch.error', array (
				$this,
				'handleControllerNotFoundAndControllerInvalidAndRouteNotFound' 
		), 100 );
		$eventManager->attach ( 'dispatch.error', array (
				$this,
				'handleRestApiError' 
		), 102 );
		
		/**
		 *
		 * @var ServiceLocatorInterface
		 */
		$sm = $e->getApplication ()->getServiceManager ();
		$eventManager->attachAggregate ( $sm->get ( 'zfc-user-redirection' ) );
		
		$moduleRouteListener->attach ( $eventManager );
		$this->addEnumType ( $e );
	}
	private function addEnumType(MvcEvent $e) {
		$em = $e->getApplication ()->getServiceManager ()->get ( 'Doctrine\ORM\EntityManager' );
		$platform = $em->getConnection ()->getDatabasePlatform ();
		$platform->registerDoctrineTypeMapping ( 'enum', 'string' );
	}
	public function handleControllerCannotDispatchRequest(MvcEvent $e) {
		$action = $e->getRouteMatch ()->getParam ( 'action' );
		$controller = get_class ( $e->getTarget () );
		
		if (! method_exists ( $e->getTarget (), $action . 'Action' )) {
			$logText = 'The requested controller ' . $controller . ' was unable to dispatch the request : ' . $action . 'Action';
			return $this->redirectToLogin ( $e );
		}
	}
	public function handleControllerNotFoundAndControllerInvalidAndRouteNotFound(MvcEvent $e) {
		$error = $e->getError ();
		if ($error == Application::ERROR_CONTROLLER_NOT_FOUND) {
			$logText = 'The requested controller ' . $e->getRouteMatch ()->getParam ( 'controller' ) . '  could not be mapped to an existing controller class.';
			$this->redirectToLogin ( $e );
		}
		
		if ($error == Application::ERROR_CONTROLLER_INVALID) {
			$logText = 'The requested controller ' . $e->getRouteMatch ()->getParam ( 'controller' ) . ' is not dispatchable';
			return $this->redirectToLogin ( $e );
		}
		
		if ($error == Application::ERROR_ROUTER_NO_MATCH) {
			$logText = 'The requested URL could not be matched by routing.';
			return $this->redirectToLogin ( $e );
		}
	}
	public function handleRestApiError(MvcEvent $e) {
		if (array_key_exists ( 'exception', $e->getParams () )) {
			$exception = $e->getParams ()['exception'];
			if ($exception instanceof RestApiException) {
				$e->stopPropagation ();
				$response = $e->getResponse ();
				$response->setStatusCode ( '400' );
				$response->getHeaders ()->addHeaderLine ( 'Content-Type', 'application/json' );
				$adapter = new Json ();
				$errorData = $exception->getErrorData ();
				$original = $exception->getOriginal ();
				$count = 0;
				while ( $original instanceof \Exception ) {
					$errorData ['previous-' . $count] = $original->getMessage () . '/' . $original->getFile () . '/' . $original->getLine ();
					$original = $original->getPrevious ();
					$count ++;
				}
				
				$response->setContent ( $adapter->serialize ( $errorData ) );
				$response->sendHeaders ();
				$response->sendContent ();
				exit ();
			}
		}
	}
	private function redirectToLogin(MvcEvent $e) {
		$target = $e->getTarget ();
		$url = $e->getRouter ()->assemble ( array (
				"controller" => 'ZfcUser\Controller\UserController' 
		), array (
				'name' => 'zfcuser/login' 
		) );
		$response = $e->getResponse ();
		$response->setHeaders ( $response->getHeaders ()->addHeaderLine ( 'Location', $url ) );
		$response->setStatusCode ( 302 );
		$response->sendHeaders ();
		exit ();
	}
	public function getConfig() {
		return include __DIR__ . '/config/module.config.php';
	}
	public function getAutoloaderConfig() {
		return array (
				'Zend\Loader\ClassMapAutoloader' => array (
						__DIR__ . '/autoload_classmap.php' 
				),
				'Zend\Loader\StandardAutoloader' => array (
						'namespaces' => array (
								__NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__ 
						) 
				) 
		);
	}
}
