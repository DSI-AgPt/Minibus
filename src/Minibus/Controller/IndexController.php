<?php

namespace Minibus\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Mvc\MvcEvent;
use CdmFr\Model\Services\CdmFrDeserializer;
use Doctrine\ORM\EntityManager;
use Zend\EventManager\Event;

/**
 *
 * @author Joachim Dornbusch
 * @copyright Joachim Dornbusch -AgroParisTech 2014-2015
 *            @30 juin 2015
 */
class IndexController extends AbstractActionController {
	
	/**
	 * Attache les évènements
	 *
	 * @see \Zend\Mvc\Controller\AbstractController::attachDefaultListeners()
	 */
	protected function attachDefaultListeners() {
		parent::attachDefaultListeners ();
		
		$events = $this->getEventManager ();
		$events->attach ( 'dispatch', array (
				$this,
				'preDispatch' 
		), 100 );
	}
	
	/**
	 * Avant l'action
	 *
	 * @param MvcEvent $e        	
	 */
	public function preDispatch(MvcEvent $e) {
		if ($this->zfcUserAuthentication ()->hasIdentity ()) {
			$userName = $this->zfcUserAuthentication ()->getIdentity ()->getUsername ();
		} else {
			$userName = 'anonymous';
		}
		$this->layout ()->setVariable ( 'dataTypes', $this->getDataTypesHandler ()->getDataTypes () );
		$this->layout ()->setVariable ( 'userName', $userName );
		$this->layout ()->setVariable ( 'action', $this->params ( 'action' ) );
		$this->layout ()->setVariable ( 'authService', $this->getFileAuthService () );
		$controller = $this->params ( 'controller' );
		$controller = explode ( '\\', $controller );
		$controller = array_pop ( $controller );
		$controller = strtolower ( $controller );
		$this->layout ()->setVariable ( 'controller', $controller );
	}
	
	/**
	 *
	 * @return \Zend\View\Model\ViewModel
	 */
	public function browseAction() {
		$view = new ViewModel ();
		$view->setVariable ( 'dataTypes', $this->getDataTypesHandler ()->getLastLevelOfdataTypes () );
		$configurationHandler = $this->getConfigurationHandler ();
		$configuration = $configurationHandler->getConfiguration ();
		$view->setVariable ( 'firstYear', $configuration ['first_year'] );
		$view->setVariable ( 'lastYear', $configuration ['last_year'] );
		$this->layout ()->setVariable ( 'displayLogWindow', false );
		return $view;
	}
	
	/**
	 *
	 * @return \Zend\View\Model\ViewModel
	 */
	public function acquisitionAction() {
		$view = new ViewModel ();
		$view->setVariable ( 'dataTypes', $this->getDataTypesHandler ()->getLastLevelOfdataTypes () );
		$configurationHandler = $this->getConfigurationHandler ();
		$configuration = $configurationHandler->getConfiguration ();
		$dataTransferInserts = new \ArrayObject ();
		$actionEvent = new Event ( 'minibus-acquisition-action-hook', this );
		$response = $this->getEventManager ()->trigger ( $actionEvent );
		$params = $actionEvent->getParams ();
		foreach ( $params as $key => $value ) {
			$view->setVariable ( $key, $value );
		}
		
		$view->setVariable ( 'firstYear', $configuration ['first_year'] );
		$view->setVariable ( 'lastYear', $configuration ['last_year'] );
		$processHandler = $this->getProcessHandler ();
		$form = $processHandler->getForm ( 'acquisition' );
		$view->setVariable ( 'form', $form );
		$this->layout ()->setVariable ( 'displayLogWindow', true );
		$this->addAlertRedirectionData ( $view );
		return $view;
	}
	
	/**
	 *
	 * @return \Zend\View\Model\ViewModel
	 */
	public function exportAction() {
		$view = new ViewModel ();
		$view->setVariable ( 'dataTypes', $this->getDataTypesHandler ()->getLastLevelOfdataTypes () );
		$configurationHandler = $this->getConfigurationHandler ();
		$configuration = $configurationHandler->getConfiguration ();
		$actionEvent = new Event ( 'minibus-export-action-hook', this );
		$response = $this->getEventManager ()->trigger ( $actionEvent );
		$params = $actionEvent->getParams ();
		foreach ( $params as $key => $value ) {
			$view->setVariable ( $key, $value );
		}
		$view->setVariable ( 'firstYear', $configuration ['first_year'] );
		$view->setVariable ( 'lastYear', $configuration ['last_year'] );
		$processHandler = $this->getProcessHandler ();
		$form = $processHandler->getForm ( 'export' );
		$view->setVariable ( 'form', $form );
		$this->layout ()->setVariable ( 'displayLogWindow', true );
		$this->addAlertRedirectionData ( $view );
		return $view;
	}
	
	/**
	 *
	 * @param ViewModel $view        	
	 */
	private function addAlertRedirectionData(ViewModel $view) {
		if ($this->flashMessenger ()->hasMessages ())
			$messages = $this->flashMessenger ()->getMessages ();
		else
			return;
		$message = unserialize ( $messages [0] );
		if (false == $message)
			return;
		if (is_array ( $message )) {
			if (array_key_exists ( 'process', $message ))
				$view->setVariable ( 'openProcess', $message ['process'] );
			if (array_key_exists ( 'execution', $message ))
				$view->setVariable ( 'selectExecution', $message ['execution'] );
		}
	}
	
	/**
	 *
	 * @return \Zend\View\Model\ViewModel
	 */
	public function alertsAction() {
		$view = new ViewModel ();
		$this->layout ()->setVariable ( 'displayLogWindow', false );
		return $view;
	}
	
	/**
	 *
	 * @return \Zend\View\Model\ViewModel
	 */
	public function configurationAction() {
		$view = new ViewModel ();
		$configurationHandler = $this->getConfigurationHandler ();
		$form = $configurationHandler->getForm ();
		$view->setVariable ( 'form', $form );
		$this->layout ()->setVariable ( 'displayLogWindow', true );
		return $view;
	}
	
	/**
	 *
	 * @return \Minibus\Controller\Auth\Service\FileAuthService
	 */
	private function getFileAuthService() {
		return $this->getServiceLocator ()->get ( 'file-auth-service' );
	}
	
	/**
	 *
	 * @return \Minibus\Model\Configuration\Service\DataTypesHandler
	 */
	private function getDataTypesHandler() {
		return $this->getServiceLocator ()->get ( 'datatypes-handler' );
	}
	
	/**
	 *
	 * @return \Minibus\Model\Configuration\Service\ConfigurationHandler
	 */
	private function getConfigurationHandler() {
		return $this->getServiceLocator ()->get ( 'configuration-handler' );
	}
	
	/**
	 *
	 * @return \Minibus\Model\Process\Service\ProcessStateHandler
	 */
	private function getProcessHandler() {
		return $this->getServiceLocator ()->get ( 'process-state-handler' );
	}
}
