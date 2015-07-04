<?php

namespace Minibus\Model\Entity;

use Doctrine\ORM\Mapping as ORM;
use Zend\Form\Annotation;
// TODO move it
// http://stackoverflow.com/questions/20454426/how-to-make-doctrine-and-zf2-form-annotations-work-together
use Doctrine\Common\Annotations\AnnotationRegistry;
$pathToZF2Library = __DIR__ . '/../../../../../../vendor/zendframework/zendframework/library/';
AnnotationRegistry::registerAutoloadNamespace ( 'Zend\Form\Annotation', $pathToZF2Library );

/**
 * @ORM\Entity
 * @Annotation\Hydrator("Zend\Stdlib\Hydrator\ClassMethods")
 */
class Configuration {
	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;
	
	/**
	 * @ORM\Column(type="integer", length=4, nullable=true)
	 * @Annotation\Validator({"name":"Zend\I18n\Validator\Int"})
	 * @Annotation\Filter({"name":"Zend\Filter\Int"})
	 * @Annotation\Required(true)
	 * @Annotation\Attributes({"type":"text"})
	 */
	private $first_year;
	
	/**
	 * @ORM\Column(type="integer", length=4, nullable=true)
	 * @Annotation\Validator({"name":"Zend\I18n\Validator\Int"})
	 * @Annotation\Filter({"name":"Zend\Filter\Int"})
	 * @Annotation\Attributes({"type":"text"})
	 * @Annotation\Required(true)
	 */
	private $last_year;
	
	/**
	 * Get id
	 *
	 * @return integer
	 */
	public function getId() {
		return $this->id;
	}

    /**
     * Set first_year
     *
     * @param integer $firstYear
     * @return Configuration
     */
    public function setFirstYear($firstYear)
    {
        $this->first_year = $firstYear;

        return $this;
    }

    /**
     * Get first_year
     *
     * @return integer 
     */
    public function getFirstYear()
    {
        return $this->first_year;
    }

    /**
     * Set last_year
     *
     * @param integer $lastYear
     * @return Configuration
     */
    public function setLastYear($lastYear)
    {
        $this->last_year = $lastYear;

        return $this;
    }

    /**
     * Get last_year
     *
     * @return integer 
     */
    public function getLastYear()
    {
        return $this->last_year;
    }
}
