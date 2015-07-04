<?php

namespace ApplicationTest\Model\Configration\Service;

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

class ConfigurationHandlerTest extends \PHPUnit_Framework_TestCase {
	public function testChangementAnneesDansConfiguration() {
		$ch = $this->getConfigurationHandler ();
		if ($ch instanceof ConfigurationHandler) {
			$ch->setConfigurationData ( array (
					'last_year' => '2005',
					'first_year' => '2000' 
			) );
			$ch->save ();
			$config = $ch->getConfiguration ();
		}
		$this->assertNotNull ( $config );
		$this->assertArrayHasKey ( 'first_year', $config, "Le tableau retourné n'a pas de clé first_year" );
		$this->assertArrayHasKey ( 'last_year', $config, "Le tableau retourné n'a pas de clé last_year" );
		$this->assertEquals ( 2000, $config ['first_year'], "La première annee devrait être 2000" );
		$this->assertEquals ( 2005, $config ['last_year'], "La dernière annee devrait être 2005" );
	}
	private function getConfigurationHandler() {
		return Bootstrap::getServiceManager ()->get ( 'configuration-handler' );
	}
}

