<?php

namespace ApplicationTest\Model\Process\Service\Arvus;

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
use Minibus\Model\Process\DataTransfer\Acquisition\Personnes\Etudiants\Arvus\ValueConverter As ValueConverter;

class ValueConverterTest extends \PHPUnit_Framework_TestCase {
	public function testSitMaritale() {
		$converter = new ValueConverter ();
		$sitMaritaleConvertie = $converter->translateSitMaritale ( 3 );
		$this->assertNotNull ( $sitMaritaleConvertie );
		$this->assertEquals ( 34, $sitMaritaleConvertie );
	}
	private function getConfigurationHandler() {
		return Bootstrap::getServiceManager ()->get ( 'configuration-handler' );
	}
}

