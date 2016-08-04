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
 * @ORM\Entity(repositoryClass="Minibus\Model\Repository\TraceRepository")
 */
class Trace
{

    /**
     * @ORM\Id
     * @ORM\Column(type="string", length=25)
     */
    private $id_data;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $hash;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Minibus\Model\Entity\Process")
     * @ORM\JoinColumn(name="process_id", referencedColumnName="id")
     */
    private $process;

    /**
     * Get id_data
     *
     * @return integer
     */
    public function getIdData()
    {
        return $this->id_data;
    }

    /**
     * Set hash
     *
     * @param string $hash            
     * @return Trace
     */
    public function setHash($hash)
    {
        $this->hash = $hash;
        
        return $this;
    }

    /**
     * Get hash
     *
     * @return string
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * Set process
     *
     * @param \Minibus\Model\Entity\Process $process            
     * @return Trace
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
     * Set id_data
     *
     * @param integer $idData            
     * @return Trace
     */
    public function setIdData($idData)
    {
        $this->id_data = $idData;
        
        return $this;
    }
}
