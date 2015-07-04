<?php

namespace Minibus\Model\Process\Service;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Minibus\Model\Entity\Execution;

class ProcessUpdater implements ServiceLocatorAwareInterface {
	const DEFAULT_NUMBER_OF_EXECUTION_TO_KEEP = 10;
	
	/**
	 *
	 * @var ServiceLocatorInterface $serviceLocator
	 */
	protected $serviceLocator;
	
	/**
	 * Set service locator
	 *
	 * @param ServiceLocatorInterface $serviceLocator        	
	 */
	public function setServiceLocator(ServiceLocatorInterface $serviceLocator) {
		$this->serviceLocator = $serviceLocator;
	}
	
	/**
	 * Get service locator
	 *
	 * @return ServiceLocatorInterface
	 */
	public function getServiceLocator() {
		return $this->serviceLocator;
	}
	
	use\Minibus\Util\Traits\EntityManagerTrait;
	
	/**
	 *
	 * @return boolean
	 */
	public function cleanOldExecutions() {
		$em = $this->getEntityManager ();
		$processes = $em->getRepository ( 'Minibus\Model\Entity\Process' )->findAll ();
		foreach ( $processes as $process ) {
			$executions = $process->getExecutions ();
			if ($executions instanceof \Doctrine\ORM\PersistentCollection) {
				$count = $executions->count ();
				$numberToDelete = $count - $this->getNumberToKeep ();
				$first = true;
				$executions = $executions->toArray ();
				usort ( $executions, function (Execution $e1, Execution $e2) {
					return $e2->getId () > $e1->getId ();
				} );
				
				foreach ( $executions as $execution ) {
					
					$count --;
					// Si une exécution est encours, si ce n'est pas la première exécution d'un process
					// en cours on l'arrête.
					if ((false === $process->getRunning () || false === $first) && $execution->getState () == Execution::RUNNING_STATE)
						$execution->setState ( Execution::STOPPED_STATE );
					if ($count <= $numberToDelete) {
						$em->remove ( $execution );
						$execution->getProcess ()->removeExecution ( $execution );
					}
					
					$first = false;
				}
			}
		}
		$em->flush ();
	}
	
	/**
	 *
	 * @return int
	 */
	private function getNumberToKeep() {
		$config = $this->getServiceLocator ()->get ( 'Config' );
		if (array_key_exists ( 'number-of-executions-to-keep', $config ))
			return intval ( $config ['number-of-executions-to-keep'] );
		return intval ( self::DEFAULT_NUMBER_OF_EXECUTION_TO_KEEP );
	}
}