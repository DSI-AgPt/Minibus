<?php

namespace ApplicationTest\Controller;

use ApplicationTest\Bootstrap;
use Zend\Mvc\Router\Http\TreeRouteStack as HttpRouter;
use Minibus\Controller\IndexController;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\RouteMatch;
use PHPUnit_Framework_TestCase;
use Minibus\Controller\ConfigurationRestController;
use Minibus\Model\Configuration\Service\ConfigurationHandler;
use Zend\Stdlib\Parameters;

class ConfigurationRestControllerTest extends \PHPUnit_Framework_TestCase {
	protected $controller;
	protected $request;
	protected $response;
	protected $routeMatch;
	protected $event;
	protected function setUp() {
		$this->controller = new ConfigurationRestController ();
		$this->request = new Request ();
		$this->routeMatch = new RouteMatch ( array (
				'controller' => 'rest_configuration' 
		) );
		$this->event = new MvcEvent ();
		$config = Bootstrap::getServiceManager ()->get ( 'Config' );
		$routerConfig = isset ( $config ['router'] ) ? $config ['router'] : array ();
		$router = HttpRouter::factory ( $routerConfig );
		
		$this->event->setRouter ( $router );
		$this->event->setRouteMatch ( $this->routeMatch );
		$this->controller->setEvent ( $this->event );
		$this->controller->setServiceLocator ( Bootstrap::getServiceManager () );
	}
	public function testChangementAnneesDansConfiguration() {
		$this->request->setMethod ( 'POST' );
		$this->request->setPost ( new Parameters ( array (
				'last_year' => 'toto',
				'first_year' => 'toto' 
		) ) );
		$result = $this->controller->dispatch ( $this->request );
		$response = $this->controller->getResponse ();
		$this->assertEquals ( 200, $response->getStatusCode () );
	}
}

