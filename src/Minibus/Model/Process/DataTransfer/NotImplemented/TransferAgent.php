<?php

namespace Minibus\Model\Process\DataTransfer\NotImplemented;

use Minibus\Model\Process\DataTransfer\AbstractDataTransferAgent;
use Minibus\Model\Entity\Execution;

class TransferAgent extends AbstractDataTransferAgent {
	public function run() {
		$this->getExecution ()->setState ( Execution::RUNNING_STATE );
		$this->getLogger ()->warn ( "Désolé, ce process n'est pas encore implémenté" );
		sleep ( 1 );
		$this->getLogger ()->info ( "Toutefois, ce fut un plaisir de faire votre connaissance." );
		sleep ( 1 );
		$this->getLogger ()->info ( "Au revoir." );
		sleep ( 1 );
		$this->setAlive ( false );
		$this->getExecution ()->setState ( Execution::STOPPED_STATE );
	}
	
	/**
	 * (non-PHPdoc)
	 *
	 * @see \Minibus\Model\Process\DataTransfer\AbstractDataTransferAgent::hasConnection()
	 */
	public function hasConnection() {
		return false;
	}
}