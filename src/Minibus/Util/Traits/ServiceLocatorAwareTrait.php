<?php

namespace Minibus\Util\Traits;

use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Classe ServiceLocatorAwareInterfaceDefaultImplementation
 * Implementation par défaut de ServiceLocatorAwareInterface
 *
 * Projet : Instm Ent 2013-2015
 * Fichier créé par pauline.moinereau le 02/04/14 à 11:15
 *
 * @copyright Copyright (coffee) Institut Mines Télécom 2013-2015, All Rights Reserved
 * @author ALYOTECH
 * @package Commun\Traits
 */
trait ServiceLocatorAwareTrait {
	/**
	 *
	 * @var ServiceLocatorInterface
	 */
	protected $sm;
	
	/**
	 *
	 * @param ServiceLocatorInterface $serviceLocator        	
	 */
	public function setServiceLocator(ServiceLocatorInterface $serviceLocator) {
		$this->sm = $serviceLocator;
	}
	
	/**
	 *
	 * @return ServiceLocatorInterface
	 */
	public function getServiceLocator() {
		return $this->sm;
	}
}
