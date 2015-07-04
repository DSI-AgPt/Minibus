<?php
namespace Minibus\Controller\Process\Service;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Cron\CronExpression;
use DateTime;
use Minibus\Model\Entity\Process;

/**
 *
 * @author Joachim Dornbusch
 * @copyright Joachim Dornbusch -AgroParisTech 2014-2015
 *            @30 juin 2015
 */
class Sheduler implements ServiceLocatorAwareInterface
{
    
    use \Minibus\Util\Traits\ServiceLocatorAwareTrait;

    /**
     *
     * @param Process $process            
     */
    public function updateNextExecutionDate(Process $process)
    {
        $nextExecutionDate = $this->cronNextExecution($process->getCron());
        $process->setNextExecution($nextExecutionDate);
    }

    /**
     *
     * @param array $sheduledProcesses            
     * @return \Minibus\Model\Entity\Process
     */
    public function getElectedProcess(array $sheduledProcesses)
    {
        $passedDateProcesses = $this->getPassedDateProcesses($sheduledProcesses);
        $electedProcess = $this->getMaxPriorityProcess($passedDateProcesses);
        $this->updateProcessPriority($passedDateProcesses);
        return $electedProcess;
    }

    /**
     *
     * @param \Minibus\Model\Entity\Process[] $sheduledProcesses            
     * @return \Minibus\Model\Entity\Process[]
     */
    public function getPassedDateProcesses(array $sheduledProcesses)
    {
        $now = new DateTime('now');
        $passedDateProcesses = array();
        foreach ($sheduledProcesses as $sheduledProcess) {
            $date = $sheduledProcess->getNextExecution();
            if (! is_null($date))
                if (self::isSoonerThan($date, $now) || self::isEqualsTo($now, $date)) {
                    array_push($passedDateProcesses, $sheduledProcess);
                }
        }
        return $passedDateProcesses;
    }

    /**
     *
     * @param \Minibus\Model\Entity\Process[] $passedDateProcesses            
     * @return \Minibus\Model\Entity\Process
     */
    public function getMaxPriorityProcess(array $passedDateProcesses)
    {
        $maxPriorityProcess = null;
        $maxPriority = - Process::MAX_PRIORITY;
        foreach ($passedDateProcesses as $passedDateProcess) {
            $priority = $passedDateProcess->getPriority();
            if ($priority > $maxPriority) {
                $maxPriorityProcess = $passedDateProcess;
                $maxPriority = $priority;
            }
        }
        return $maxPriorityProcess;
    }

    /**
     *
     * @param \Minibus\Model\Entity\Process[] $passedDateProcesses            
     * @return \Minibus\Model\Entity\Process
     */
    public function updateProcessPriority(array $passedDateProcesses)
    {
        foreach ($passedDateProcesses as $passedDateProcess) {
            $priority = $passedDateProcess->getPriority();
            if ($priority == Process::MAX_PRIORITY)
                $passedDateProcess->setPriority(0);
            else
                $passedDateProcess->setPriority($priority + 1);
        }
    }

    /**
     *
     * @param unknown $cron            
     * @return DateTime
     */
    private function cronNextExecution($cron)
    {
        $cron = CronExpression::factory($cron);
        return $cron->getNextRunDate();
    }

    /**
     *
     * @param DateTime $date1            
     * @param DateTime $date2            
     * @return boolean
     */
    private static function isSoonerThan(DateTime $date1, DateTime $date2)
    {
        return $date1 < $date2;
    }

    /**
     *
     * @param DateTime $date1            
     * @param DateTime $date2            
     * @return boolean
     */
    private static function isEqualsTo(DateTime $date1, DateTime $date2)
    {
        return $date1 == $date2;
    }
}
