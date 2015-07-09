<?php
/**
 * 
 * @author Joachim Dornbusch 1 juil. 2015
 * @copyright Joachim Dornbusch - AgroParisTech - 2014,2015
 *
 */
namespace Minibus\Model\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Execution
{

    const STOPPED_STATE = 'Stopped';

    const RUNNING_STATE = 'Running';

    const DEFAULT_INFORMATION = 'No information';

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     */
    private $startDate;

    /**
     * @ORM\Column(type="string", length=45, nullable=false, options={"default":"not running"})
     */
    private $state;

    /**
     */
    private $result;

    /**
     * @ORM\Column(type="text", nullable=false, options={"default":"no information"})
     */
    private $information;

    /**
     * @ORM\Column(type="string", length=13, nullable=false)
     */
    private $logidentifier;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private $pid;

    /**
     * @ORM\ManyToOne(targetEntity="Minibus\Model\Entity\Process", inversedBy="executions")
     * @ORM\JoinColumn(name="process_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $process;

    public function __construct()
    {
        $this->startDate = new \DateTime();
        $this->logidentifier = uniqid();
        $this->state = self::RUNNING_STATE;
        $this->information = self::DEFAULT_INFORMATION;
        $this->pid = getmypid();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set startDate
     *
     * @param \DateTime $startDate            
     * @return Execution
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;
        
        return $this;
    }

    /**
     * Get startDate
     *
     * @return \DateTime
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * Set result
     *
     * @param string $result            
     * @return Execution
     */
    public function setResult($result)
    {
        $this->result = $result;
        
        return $this;
    }

    /**
     * Get result
     *
     * @return string
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * Set information
     *
     * @param string $information            
     * @return Execution
     */
    public function setInformation($information)
    {
        $this->information = $information;
        
        return $this;
    }

    /**
     * Get information
     *
     * @return string
     */
    public function getInformation()
    {
        return $this->information;
    }

    /**
     * Set process
     *
     * @param \Minibus\Model\Entity\Process $process            
     * @return Execution
     */
    public function setProcess(\Minibus\Model\Entity\Process $process = null)
    {
        $this->process = $process;
        
        return $this;
    }

    /**
     * Get process
     *
     * @return \Minibus\Model\Entity\Process
     */
    public function getProcess()
    {
        return $this->process;
    }

    /**
     * Set logidentifier
     *
     * @param string $logidentifier            
     * @return Execution
     */
    public function setLogidentifier($logidentifier)
    {
        $this->logidentifier = $logidentifier;
        
        return $this;
    }

    /**
     * Get logidentifier
     *
     * @return string
     */
    public function getLogidentifier()
    {
        return $this->logidentifier;
    }

    /**
     * Set state
     *
     * @param string $state            
     * @return Execution
     */
    public function setState($state)
    {
        $this->state = $state;
        
        return $this;
    }

    /**
     * Get state
     *
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Set pid
     *
     * @param integer $pid            
     * @return Execution
     */
    public function setPid($pid)
    {
        $this->pid = $pid;
        
        return $this;
    }

    /**
     * Get pid
     *
     * @return integer
     */
    public function getPid()
    {
        return $this->pid;
    }
}
