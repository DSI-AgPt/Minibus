<?php
namespace Minibus\Controller\Process\Service;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Minibus\Model\Entity\Process;
use Minibus\Controller\Exceptions\RestApiException;
use Minibus\Controller\Process\Exception\ProcessException;
use Minibus\Model\Process\DataTransfer\DataTransferAgentInterface;
use Minibus\Model\Entity\Execution;

/**
 *
 * @author Joachim Dornbusch
 * @copyright Joachim Dornbusch -AgroParisTech 2014-2015
 *            @30 juin 2015
 */
class ProcessExecutionHandler implements ServiceLocatorAwareInterface
{

    /**
     *
     * @var bool
     */
    private $verbose;
    
    use\Minibus\Util\Traits\ServiceLocatorAwareTrait;
    
    use\Minibus\Util\Traits\EntityManagerTrait;

    /**
     *
     * @param bool $verbose            
     * @throws RestApiException
     */
    public function executeNextProcess($verbose = false)
    {
        // TODO refacto translete and complete
        $this->verbose = $verbose;
        // nettoye les processus
        $this->cleanProcesses();
        // charger la liste des process planifiés
        $sheduledProcesses = $this->getSheduledProcessList();
        
        // charger la liste des process actifs
        $eligibleProcesses = $this->getEligibleProcessList();
        // déterminer le process à éxecuter
        $electedProcess = $this->getElectedProcess($eligibleProcesses);
        // mettre à jour les date d'exécution des process
        $this->updateProcessNextExecutionDate($sheduledProcesses);
        
        $this->getEntityManager()->flush();
        // créer le thread
        if (! is_null($electedProcess) && $electedProcess instanceof Process) {
            $this->conditionalDisplay("########################################################################\r\n");
            $this->conditionalDisplay("## Exécution du processus {$electedProcess->getId()}\r\n");
            $nextExecutionParameters = $electedProcess->getNextExecutionParameters();
            $electedProcess->setNextExecution(null);
            try {
                $dataTransferAgent = $this->getServiceLocator()->get($electedProcess->getMode() . '-' . $electedProcess->getType() . '-' . $electedProcess->getEndpoint() . '-' . $electedProcess->getAnnee());
            } catch (\Zend\ServiceManager\Exception\ServiceNotCreatedException $e) {
                $electedProcess->setRunning(false);
                $this->getEntityManager()->flush($electedProcess);
                if (isset($dataTransferAgent) && $dataTransferAgent instanceof DataTransferAgentInterface) {
                    $dataTransferAgent->getExecution()->setState(Execution::STOPPED_STATE);
                    $this->getEntityManager()->flush($dataTransferAgent->getExecution());
                }
                throw new RestApiException(array(
                    "data-transfer" => $e->getMessage()
                ), $e);
            } catch (\Zend\ServiceManager\Exception\ServiceNotFoundException $e) {
                $electedProcess->setRunning(false);
                $this->getEntityManager()->flush($electedProcess);
                if (isset($dataTransferAgent) && $dataTransferAgent instanceof DataTransferAgentInterface) {
                    $dataTransferAgent->getExecution()->setState(Execution::STOPPED_STATE);
                    $this->getEntityManager()->flush($dataTransferAgent->getExecution());
                }
                throw new RestApiException(array(
                    "data-transfer" => $e->getMessage()
                ), $e);
            }
            try {
                if (! is_null($nextExecutionParameters) && is_array($nextExecutionParameters) && array_key_exists('mode', $nextExecutionParameters)) {
                    $dataTransferAgent->setExecutionMode($nextExecutionParameters['mode']);
                    $electedProcess->setNextExecutionParameters(array());
                }
                $electedProcess->setRunning(true);
                if ($dataTransferAgent instanceof DataTransferAgentInterface)
                    $dataTransferAgent->getExecution()->setState(Execution::RUNNING_STATE);
                $dataTransferAgent->redirectErrorMessages();
                $this->getEntityManager()->flush();
                if (true === $dataTransferAgent->acquireLocks())
                    $dataTransferAgent->run();
            } catch (ProcessException $e1) {
                
                $dataTransferAgent->getLogger()->err("Le processus a levé une exception : " . $e1->getMessage());
                {
                    $dataTransferAgent->getLogger()->err($e1->getMessage());
                    $e1 = $e1->getPrevious();
                }
                while (! is_null($e1))
                    $electedProcess->setRunning(false);
                if ($dataTransferAgent instanceof DataTransferAgentInterface)
                    $dataTransferAgent->getExecution()->setState(Execution::STOPPED_STATE);
            } catch (\Exception $e2) {
                $dataTransferAgent->getLogger()->err("Le processus a rencontré une erreur : " . $e2->getMessage() . " Classe " . $e2->getFile() . " Ligne " . $e2->getLine());
                while (! is_null($e2->getPrevious())) {
                    $e2 = $e2->getPrevious();
                    $dataTransferAgent->getLogger()->err($e2->getMessage() . " Classe " . $e2->getFile() . " Ligne " . $e2->getLine());
                }
                $electedProcess->setRunning(false);
                $electedProcess->setAlive(false);
                if ($dataTransferAgent instanceof DataTransferAgentInterface)
                    $dataTransferAgent->getExecution()->setState(Execution::STOPPED_STATE);
            }
            $this->getEntityManager()->flush();
        } else {
            $this->conditionalDisplay("########################################################################\r\n");
            $this->conditionalDisplay("## Aucun processus élu\r\n");
        }
    }

    /**
     * Liste les process actifs, planifiés qui ne sont pas en cours d'execution
     *
     * @return \Minibus\Model\Entity\Process[]:
     */
    private function getSheduledProcessList()
    {
        return $this->getProcessStateHandler()->getSheduledProcessList();
    }

    /**
     * Liste les process actifs
     *
     * @return \Minibus\Model\Entity\Process[]:
     */
    private function getActiveProcessList()
    {
        return $this->getProcessStateHandler()->getActiveProcessList();
    }

    /**
     * Liste les process actifs qui ne sont pas en cours d'execution
     *
     * @return \Minibus\Model\Entity\Process[]:
     */
    private function getEligibleProcessList()
    {
        return $this->getProcessStateHandler()->getEligibleProcessList();
    }

    /**
     *
     * @param array $sheduledProcesses            
     */
    private function updateProcessNextExecutionDate(array $sheduledProcesses)
    {
        $sheduler = $this->getSheduler();
        foreach ($sheduledProcesses as $sheduledProcess) {
            if (intval($sheduledProcess->getPriority()) < Process::MAX_PRIORITY) {
                $sheduler->updateNextExecutionDate($sheduledProcess);
            }
        }
    }

    /**
     */
    public function cleanProcesses()
    {
        $this->conditionalDisplay("Nettoyage des processus\r\n");
        $activeProcesses = $this->getActiveProcessList();
        foreach ($activeProcesses as $activeProcess) {
            // les executions qui sont running mais n'ont plus de process running comme parent,
            // on les tue
            $this->conditionalDisplay("########################################################################\r\n");
            $this->conditionalDisplay("##Processus actif " . $activeProcess->getExternalIdentifier() . "\r\n");
            $this->conditionalDisplay(">> #Marqué comme " . ($activeProcess->getRunning() ? " running " : " not running ") . ".\r\n");
            $this->conditionalDisplay(">> #Marqué comme " . ($activeProcess->getAlive() ? " alive " : " not alive ") . ".\r\n");
            $executions = $activeProcess->getExecutions();
            foreach ($executions as $execution) {
                $this->conditionalDisplay(" >> #Execution n°" . $execution->getId() . " à l'état " . $execution->getState() . ".\r\n");
                if ($activeProcess->getRunning() == false) {
                    if ($execution->getState() == Execution::RUNNING_STATE) {
                        $this->conditionalDisplay(">> !! >> >> !! >> Arrêt forcé de l'exécution" . $execution->getId() . ".\r\n");
                        posix_kill($execution->getPid(), 9);
                        $execution->setInformation("Arrête forcé de l'exécution");
                        $execution->setState(Execution::STOPPED_STATE);
                    }
                }
            }
            // Les processus qui ne sont pas alive, on met le running à false
            // et inversement
            if ($activeProcess->getRunning() != $activeProcess->getAlive()) {
                $this->conditionalDisplay(">> #Changement de la marque running pour " . ($activeProcess->getAlive() ? " running " : " not running ") . ".\r\n");
                $activeProcess->setRunning($activeProcess->getAlive());
            }
            
            // on remet alive à false pour tout le monde
            // à charge pour les exécutions de le remettre à true
            $activeProcess->setAlive(false);
        }
        $this->getEntityManager()->flush();
    }

    /**
     *
     * @return \Minibus\Model\Entity\Process:
     */
    private function getElectedProcess(array $sheduledProcesses)
    {
        return $this->getSheduler()->getElectedProcess($sheduledProcesses);
    }

    /**
     *
     * @param string $string            
     */
    private function conditionalDisplay($string)
    {
        if (true !== $this->verbose)
            return;
        echo $string;
    }

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
     * @return \Minibus\Controller\Process\Service\Sheduler
     */
    private function getSheduler()
    {
        return $this->getServiceLocator()->get('process-sheduler');
    }
}