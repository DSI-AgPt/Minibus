<?php

namespace Minibus\Controller\Alert\Service;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Log\Logger;
use Minibus\Model\Entity\Alert;
use Minibus\Model\Entity\Execution;
use Minibus\Controller\Process\AbstractFactory\AbstractDataTransferFactory;
use Doctrine\DBAL\DBALException;

/**
 *
 * @author Joachim Dornbusch
 * @copyright Joachim Dornbusch -AgroParisTech 2014-2015
 *            29 juin 2015
 */
class AlertDataHandler implements ServiceLocatorAwareInterface {
	
	use \Minibus\Util\Traits\ServiceLocatorAwareTrait;
	
	use \Minibus\Util\Traits\EntityManagerTrait;
	
	/**
	 *
	 * @param string $requestedType        	
	 * @return Ambigous <unknown>|multitype:
	 */
	public function getAlertsStructure($requestedType) {
		$dataTypesHandler = $this->getDataTypesHandler ();
		$types = $dataTypesHandler->getLastLevelOfdataTypes ();
		array_walk ( $types, "self::extractEndPoindsFromDataTypes" );
		if (array_key_exists ( $requestedType, $types ))
			return $types [$requestedType];
		return array ();
	}
	
	/**
	 *
	 * @param array $selectedProcess        	
	 * @param array $selectedLevels        	
	 * @return Ambigous <mixed, multitype:>
	 */
	public function getAlertsForProcess(array $selectedProcess, array $selectedLevels) {
		$selectedProcessKeys = array ();
		foreach ( $selectedProcess as $processIdentifier ) {
			if ($processIdentifier == "not-implemented")
				continue;
			$keys = AbstractDataTransferFactory::splitIntoIdentifiers ( $processIdentifier );
			array_push ( $selectedProcessKeys, $keys );
		}
		
		$alertes = $this->getAlertsRepository ()->getAlertsForProcess ( $selectedProcessKeys, $selectedLevels );
		for($i = 0; $i < count ( $alertes ); $i ++) {
			$date = $alertes [$i] ['date'];
			if ($date instanceof \DateTime)
				$alertes [$i] ['date'] = $date->format ( "d m Y - h:m:s" );
			if (array_key_exists ( 'message', $alertes [$i] )) {
				$message = $alertes [$i] ['message'];
				$alertes [$i] ['message'] = preg_replace ( '/(\\x[0-9a-f]{2})+/', '...suppression_photo...', $message );
			}
		}
		
		return $alertes;
	}
	
	/**
	 *
	 * @param array $selectedProcess        	
	 * @return Ambigous <multitype:, unknown>
	 */
	public function getIdObjectListForProcess(array $selectedProcess) {
		$objectIds = $this->getAlertsRepository ()->getAllIdObjects ( $selectedProcess );
		return $objectIds;
	}
	
	/**
	 *
	 * @param array $ids        	
	 * @throws DBALException
	 * @return number
	 */
	public function removeAlerts(array $ids) {
		$alertes = $this->getAlertsRepository ()->findById ( $ids );
		$nbDeleted = 0;
		foreach ( $alertes as $alerte ) {
			try {
				$this->getEntityManager ()->remove ( $alerte );
				$nbDeleted ++;
			} catch ( DBALException $e ) {
				// TODO gérer
				throw $e;
			}
		}
		try {
			$this->getEntityManager ()->flush ();
			return $nbDeleted;
		} catch ( DBALException $e ) {
			// TODO gérer
			throw $e;
		}
		return 0;
	}
	
	/**
	 *
	 * @param array $selectedProcessKeys        	
	 * @param array $selectedLevels        	
	 * @throws DBALException
	 * @return number
	 */
	public function removeAllAlerts(array $selectedProcessKeys, array $selectedLevels) {
		$alertes = $this->getAlertsRepository ()->getAlertsForProcess ( $selectedProcessKeys, $selectedLevels, false );
		$nbDeleted = 0;
		foreach ( $alertes as $alerte ) {
			try {
				$this->getEntityManager ()->remove ( $alerte );
				$nbDeleted ++;
			} catch ( DBALException $e ) {
				// TODO gérer
				throw $e;
			}
		}
		try {
			$this->getEntityManager ()->flush ();
			return $nbDeleted;
		} catch ( DBALException $e ) {
			// TODO gérer
			throw $e;
		}
		return 0;
	}
	private static function extractEndPoindsFromDataTypes(&$type, $key) {
		$result = array ();
		$result ['sources'] = array ();
		$result ['cibles'] = array ();
		$sources = array ();
		$cibles = array ();
		$configuration = $type ['configuration'];
		if (array_key_exists ( 'sources', $configuration ))
			$sources = $configuration ['sources'];
		if (array_key_exists ( 'cibles', $configuration ))
			$cibles = $configuration ['cibles'];
		foreach ( $sources as $key => $value ) {
			$result ['sources'] [$key] = $value ['dataTransferAgent'];
		}
		foreach ( $cibles as $key => $value ) {
			$result ['cibles'] [$value ['label']] = $value ['dataTransferAgent'];
		}
		$type = $result;
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
	 * @return \Minibus\Model\Repository\AlertRepository
	 */
	private function getAlertsRepository() {
		return $this->getEntityManager ()->getRepository ( 'Minibus\Model\Entity\Alert' );
	}
}