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
trait EntityManagerTrait {
	/**
	 *
	 * @var \Doctrine\ORM\EntityManager
	 */
	private static $entityManager;
	/**
	 *
	 * @return \Doctrine\ORM\EntityManager
	 */
	protected function getEntityManager() {
		if (is_null ( self::$entityManager ))
			self::$entityManager = $this->getServiceLocator ()->get ( 'doctrine.entitymanager.orm_default' );
		if (! self::$entityManager->isOpen ()) {
			self::$entityManager = self::$entityManager->create ( self::$entityManager->getConnection (), self::$entityManager->getConfiguration () );
			self::$entityManager->clear ();
		}
		return self::$entityManager;
	}
}
