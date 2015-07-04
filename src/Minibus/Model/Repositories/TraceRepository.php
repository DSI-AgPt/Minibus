<?php

namespace Minibus\Model\Repository;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\EntityRepository;
use Minibus\Model\Entity\Process;

class TraceRepository extends EntityRepository {

	/**
	 *
	 * @param Process $process
	 * @return array:
	 */
	public function getHashAndIdentifiers(Process $process) {
		$qb = $this->getEntityManager ()->createQueryBuilder ();
		$qb->select ( "trace.id_data,trace.hash" )->from ( '\Minibus\Model\Entity\Trace', 'trace' )->where ( 'trace.process = :process' )->setParameter ( 'process', $process )->orderBy ( "trace.id_data" );

		$tracesHashAndIdentifiers = $qb->getQuery ()->getResult ();

		return $tracesHashAndIdentifiers;
	}

	/**
	 *
	 * @param Process $process
	 * @return \Doctrine\ORM\mixed
	 */
	public function removeAllTraces(Process $process) {
		$qb = $this->getEntityManager ()->createQueryBuilder ();
		$result = $qb->delete ( '\Minibus\Model\Entity\Trace', 'trace' )->where ( 'trace.process = :process' )->setParameter ( 'process', $process )->getQuery ()->execute ();
		return $result;
	}



}
