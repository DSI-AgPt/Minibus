<?php
namespace Minibus\Controller\Alert\Service;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Log\Logger;
use Minibus\Model\Entity\Alert;
use Minibus\Model\Entity\Execution;

/**
 *
 * @author Joachim Dornbusch
 * @copyright Joachim Dornbusch -AgroParisTech 2014-2015
 *            @30 juin 2015
 */
class AlertHandler implements ServiceLocatorAwareInterface
{
    
    use\Minibus\Util\Traits\ServiceLocatorAwareTrait;
    
    use \Minibus\Util\Traits\EntityManagerTrait;

    /**
     *
     * @return \Minibus\Model\Process\Service\ProcessStateHandler
     */
    private function getProcessStateHandler()
    {
        return $this->getServiceLocator()->get('process-state-handler');
    }

    /**
     *
     * @param string $message            
     * @param int $level            
     * @param Execution $execution            
     */
    public function alert($message, $level, Execution $execution, $objectIdentifier = null)
    {
        $alert = new Alert();
        $alert->setExecution($execution);
        $alert->setMessage($message);
        $alert->setDate(new \DateTime());
        $alert->setLevel($level);
        if (! is_null($objectIdentifier))
            $alert->setIdobject($objectIdentifier);
        $process = $execution->getProcess();
        $this->getEntityManager()->persist($process);
        $this->getEntityManager()->persist($execution);
        $this->getEntityManager()->persist($alert);
        $this->getEntityManager()->flush();
    }
}