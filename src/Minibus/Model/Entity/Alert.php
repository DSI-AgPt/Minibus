<?php
namespace Minibus\Model\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Minibus\Model\Repository\AlertRepository")
 */
class Alert
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     */
    private $date;

    /**
     * @ORM\Column(type="string", nullable=false, columnDefinition="ENUM('WARNING', 'ERROR','ALERT')")
     */
    private $level;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $message;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $idobject;

    /**
     * @ORM\ManyToOne(targetEntity="Minibus\Model\Entity\Execution")
     * @ORM\JoinColumn(name="execution_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $execution;

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
     * Set date
     *
     * @param \DateTime $date            
     * @return Alert
     */
    public function setDate($date)
    {
        $this->date = $date;
        
        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set message
     *
     * @param string $message            
     * @return Alert
     */
    public function setMessage($message)
    {
        $this->message = $message;
        
        return $this;
    }

    /**
     * Get message
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set idobject
     *
     * @param string $idobject            
     * @return Alert
     */
    public function setIdobject($idobject)
    {
        $this->idobject = $idobject;
        
        return $this;
    }

    /**
     * Get idobject
     *
     * @return string
     */
    public function getIdobject()
    {
        return $this->idobject;
    }

    /**
     * Set execution
     *
     * @param \Minibus\Model\Entity\Execution $execution            
     * @return Alert
     */
    public function setExecution(\Minibus\Model\Entity\Execution $execution)
    {
        $this->execution = $execution;
        
        return $this;
    }

    /**
     * Get execution
     *
     * @return \Minibus\Model\Entity\Execution
     */
    public function getExecution()
    {
        return $this->execution;
    }

    /**
     * Set level
     *
     * @param string $level
     * @return Alert
     */
    public function setLevel($level)
    {
        $this->level = $level;

        return $this;
    }

    /**
     * Get level
     *
     * @return string 
     */
    public function getLevel()
    {
        return $this->level;
    }
}
