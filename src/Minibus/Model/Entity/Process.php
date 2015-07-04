<?php
/**
 * 
 * @author Joachim Dornbusch 1 juil. 2015
 * @copyright Joachim Dornbusch - AgroParisTech - 2014,2015
 *
 */
namespace Minibus\Model\Entity;

use Doctrine\ORM\Mapping as ORM;
use Zend\Form\Annotation;

/**
 * @ORM\Entity
 */
class Process
{

    const MAX_PRIORITY = 32767;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer", length=11)
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=11, nullable=true)
     * @Annotation\Type("text")
     * @Annotation\Name("mode")
     */
    private $mode;

    /**
     * @ORM\Column(type="string", length=45, nullable=true)
     * @Annotation\Type("text")
     * @Annotation\Name("type")
     */
    private $type;

    /**
     * @ORM\Column(type="string", length=45, nullable=true)
     */
    private $endpoint;

    /**
     * @ORM\Column(type="integer", length=4, nullable=true, options={"default":0})
     */
    private $annee;

    /**
     * @ORM\Column(type="string", length=15, nullable=true)
     * @Annotation\Type("text")
     * @Annotation\Name("cron")
     */
    private $cron;

    /**
     * @ORM\Column(type="boolean", nullable=false, options={"default":false})
     */
    private $active;

    /**
     * @ORM\Column(type="boolean", nullable=false, options={"default":false})
     * @Annotation\Type("checkbox")
     */
    private $shedule;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $nextExecution;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $priority;

    /**
     * @ORM\Column(type="string", length=24, nullable=true)
     */
    private $logLevel;

    /**
     * @ORM\Column(type="json_array", nullable=true)
     */
    private $nextExecutionParameters;

    /**
     * @ORM\Column(type="boolean", nullable=false, options={"default":false})
     */
    private $running;

    /**
     * @ORM\OneToMany(targetEntity="Minibus\Model\Entity\Execution", mappedBy="process")
     */
    private $executions;

    /**
     * @ORM\Column(type="boolean", nullable=false, options={"default":false})
     */
    private $alive;

    /**
     * @ORM\Column(type="boolean", nullable=false, options={"default":false})
     */
    private $interrupted;

    /**
     * Set mode
     *
     * @param string $mode            
     * @return Process
     */
    public function setMode($mode)
    {
        $this->mode = $mode;
        
        return $this;
    }

    /**
     * Get mode
     *
     * @return string
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * Set type
     *
     * @param string $type            
     * @return Process
     */
    public function setType($type)
    {
        $this->type = $type;
        
        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set cron
     *
     * @param string $cron            
     * @return Process
     */
    public function setCron($cron)
    {
        $this->cron = $cron;
        
        return $this;
    }

    /**
     * Get cron
     *
     * @return string
     */
    public function getCron()
    {
        return $this->cron;
    }

    /**
     * Set active
     *
     * @param boolean $active            
     * @return Process
     */
    public function setActive($active)
    {
        $this->active = $active;
        
        return $this;
    }

    /**
     * Get active
     *
     * @return boolean
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set shedule
     *
     * @param boolean $shedule            
     * @return Process
     */
    public function setShedule($shedule)
    {
        $this->shedule = $shedule;
        
        return $this;
    }

    /**
     * Get shedule
     *
     * @return boolean
     */
    public function getShedule()
    {
        return $this->shedule;
    }

    /**
     * Set priority
     *
     * @param integer $priority            
     * @return Process
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;
        
        return $this;
    }

    /**
     * Get priority
     *
     * @return integer
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * Set endpoint
     *
     * @param string $endpoint            
     * @return Process
     */
    public function setEndpoint($endpoint)
    {
        $this->endpoint = $endpoint;
        
        return $this;
    }

    /**
     * Get endpoint
     *
     * @return string
     */
    public function getEndpoint()
    {
        return $this->endpoint;
    }

    /**
     * Set nextExecution
     *
     * @param \DateTime $nextExecution            
     * @return Process
     */
    public function setNextExecution($nextExecution)
    {
        $this->nextExecution = $nextExecution;
        
        return $this;
    }

    /**
     * Get nextExecution
     *
     * @return \DateTime
     */
    public function getNextExecution()
    {
        return $this->nextExecution;
    }

    /**
     * Set logLevel
     *
     * @param string $logLevel            
     * @return Process
     */
    public function setLogLevel($logLevel)
    {
        $this->logLevel = $logLevel;
        
        return $this;
    }

    /**
     * Get logLevel
     *
     * @return string
     */
    public function getLogLevel()
    {
        return $this->logLevel;
    }

    /**
     * Set annee
     *
     * @param integer $annee            
     * @return Process
     */
    public function setAnnee($annee)
    {
        $this->annee = $annee;
        
        return $this;
    }

    /**
     * Get annee
     *
     * @return integer
     */
    public function getAnnee()
    {
        return $this->annee;
    }

    /**
     * Set nextExecutionParameters
     *
     * @param array $nextExecutionParameters            
     * @return Process
     */
    public function setNextExecutionParameters(array $nextExecutionParameters)
    {
        $this->nextExecutionParameters = $nextExecutionParameters;
        
        return $this;
    }

    /**
     * Get nextExecutionParameters
     *
     * @return array
     */
    public function getNextExecutionParameters()
    {
        return $this->nextExecutionParameters;
    }

    /**
     * Set running
     *
     * @param boolean $running            
     * @return Process
     */
    public function setRunning($running)
    {
        $this->running = $running;
        
        return $this;
    }

    /**
     * Get running
     *
     * @return boolean
     */
    public function getRunning()
    {
        return $this->running;
    }

    /**
     * Set alive
     *
     * @param boolean $alive            
     * @return Process
     */
    public function setAlive($alive)
    {
        $this->alive = $alive;
        
        return $this;
    }

    /**
     * Get alive
     *
     * @return boolean
     */
    public function getAlive()
    {
        return $this->alive;
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->executions = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add executions
     *
     * @param \Minibus\Model\Entity\Execution $executions            
     * @return Process
     */
    public function addExecution(\Minibus\Model\Entity\Execution $executions)
    {
        $this->executions[] = $executions;
        
        return $this;
    }

    /**
     * Remove executions
     *
     * @param \Minibus\Model\Entity\Execution $executions            
     */
    public function removeExecution(\Minibus\Model\Entity\Execution $executions)
    {
        $this->executions->removeElement($executions);
    }

    /**
     * Get executions
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getExecutions()
    {
        return $this->executions;
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
     * Idetifiant externe du processus
     *
     * @return string
     */
    public function getExternalIdentifier()
    {
        return $this->getType() . '-' . $this->getMode() . '-' . $this->getEndpoint() . '-' . $this->getAnnee();
    }

    /**
     * Set interrupted
     *
     * @param boolean $interrupted            
     * @return Process
     */
    public function setInterrupted($interrupted)
    {
        $this->interrupted = $interrupted;
        
        return $this;
    }

    /**
     * Get interrupted
     *
     * @return boolean
     */
    public function getInterrupted()
    {
        return $this->interrupted;
    }
}
