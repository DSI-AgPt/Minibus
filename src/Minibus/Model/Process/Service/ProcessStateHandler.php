<?php

namespace Minibus\Model\Process\Service;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Doctrine\ORM\AbstractQuery;
use JMS\Serializer\SerializerBuilder;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query;
use Minibus\Model\Entity\Configuration;
use DoctrineORMModule\Form\Annotation\AnnotationBuilder;
use Zend\Form\Form;
use Zend\Form\FormInterface;
use Zend\Stdlib\Hydrator\ClassMethods;
use Minibus\Controller\Exceptions\RestApiException;
use Zend\Filter\Callback;
use Minibus\Model\Entity\Process;
use Doctrine\DBAL\DBALException;

class ProcessStateHandler implements ServiceLocatorAwareInterface {
	
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
	public function getForm($mode) {
		$builder = new AnnotationBuilder ( $this->getEntityManager () );
		$form = $builder->createForm ( 'Minibus\Model\Entity\Process' );
		$form->setData ( array (
				"mode" => $mode 
		) );
		
		return $form;
	}
	
	use \Minibus\Util\Traits\EntityManagerTrait;
	
	/**
	 *
	 * @return \Minibus\Model\Entity\Process
	 */
	public function getProcess($mode, $type, $endpoint, $annee) {
		$em = $this->getEntityManager ();
		$params = array (
				'mode' => $mode,
				'type' => $type,
				'endpoint' => $endpoint,
				'annee' => $annee 
		);
		$process = $em->getRepository ( 'Minibus\Model\Entity\Process' )->findOneBy ( $params );
		return $process;
	}
	/**
	 * Liste les process actifs,planifiés qui ne sont pas en cours d'execution
	 *
	 * @return \Minibus\Model\Entity\Process[]
	 */
	public function getSheduledProcessList() {
		$em = $this->getEntityManager ();
		$params = array (
				'active' => true,
				'shedule' => true,
				'running' => false,
				'alive' => false 
		);
		$processes = $em->getRepository ( 'Minibus\Model\Entity\Process' )->findBy ( $params );
		return $processes;
	}
	
	/**
	 * Liste les process actifs,planifiés qui ne sont pas en cours d'execution
	 *
	 * @return \Minibus\Model\Entity\Process[]
	 */
	public function getActiveProcessList() {
		$em = $this->getEntityManager ();
		$params = array (
				'active' => true 
		);
		$processes = $em->getRepository ( 'Minibus\Model\Entity\Process' )->findBy ( $params );
		return $processes;
	}
	
	/**
	 * Liste les process actifs,planifiés qui ne sont pas en cours d'execution
	 *
	 * @return \Minibus\Model\Entity\Process[]
	 */
	public function getMaxPriorityProcessList() {
		$em = $this->getEntityManager ();
		$params = array (
				'priority' => Process::MAX_PRIORITY 
		);
		$processes = $em->getRepository ( 'Minibus\Model\Entity\Process' )->findBy ( $params );
		return $processes;
	}
	
	/**
	 * Liste les process actifs qui ne sont pas en cours d'execution
	 *
	 * @return \Minibus\Model\Entity\Process[]
	 */
	public function getEligibleProcessList() {
		$em = $this->getEntityManager ();
		$params = array (
				'active' => true,
				'running' => false,
				'alive' => false 
		);
		$processes = $em->getRepository ( 'Minibus\Model\Entity\Process' )->findBy ( $params );
		return $processes;
	}
	/**
	 * Liste les process actifs qui ne sont pas en cours d'execution
	 *
	 * @return \Minibus\Model\Entity\Process[]:
	 */
	public function getRunningProcessList($mode, $annee) {
		$em = $this->getEntityManager ();
		$params = array (
				'mode' => $mode,
				'annee' => $annee,
				'running' => true 
		);
		$processes = $em->getRepository ( 'Minibus\Model\Entity\Process' )->findBy ( $params );
		return $processes;
	}
	
	/**
	 *
	 * @param string $mode        	
	 * @param string $type        	
	 * @param string $endpoint        	
	 * @param string $annee        	
	 * @return \Minibus\Model\Entity\Process
	 */
	public function getDefaultProcess($mode, $type, $endpoint, $annee) {
		$process = new Process ();
		$process->setMode ( $mode );
		$process->setEndpoint ( $endpoint );
		$process->setType ( $type );
		$process->setActive ( false );
		$process->setAnnee ( $annee );
		$process->setShedule ( false );
		$process->setAlive ( false );
		$process->setRunning ( false );
		$process->setInterrupted ( false );
		$m = rand ( 0, 60 );
		$h = rand ( 0, 6 );
		$process->setCron ( $m . ' ' . $h . ' * * *' );
		return $process;
	}
	public function saveProcess(Process $process) {
		$em = $this->getEntityManager ();
		$em->persist ( $process );
		try {
			$em->flush ();
		} catch ( DBALException $e ) {
			throw new RestApiException ( array (
					"save-process" => $e->getMessage () 
			) );
		}
	}
	public function askImmediateExecution(Process $process, $data) {
		$em = $this->getEntityManager ();
		$process->setNextExecutionParameters ( $data );
		$process->setNextExecution ( new \DateTime () );
		$process->setPriority ( Process::MAX_PRIORITY );
		$others = $this->getMaxPriorityProcessList ();
		foreach ( $others as $other ) {
			if ($other == $process)
				continue;
			$other->setPriority ( 0 );
		}
		
		try {
			$em->flush ();
		} catch ( DBALException $e ) {
			throw new RestApiException ( array (
					"save-process" => $e->getMessage () 
			) );
		}
	}
	/**
	 *
	 * @param string $mode        	
	 * @param string $type        	
	 * @param string $endpoint        	
	 * @param int $annee        	
	 * @return \Minibus\Model\Entity\Execution[]
	 */
	public function getProcessExecutions($mode, $type, $endpoint, $annee) {
		$process = $this->getProcess ( $mode, $type, $endpoint, $annee );
		if (is_null ( $process ))
			return array ();
		return $process->getExecutions ();
	}

}