<?php
namespace Minibus\Model\Configuration\Service;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Doctrine\ORM\AbstractQuery;
use JMS\Serializer\SerializerBuilder;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query;
use Minibus\Model\Entity\Configuration;
use DoctrineORMModule\Form\Annotation\AnnotationBuilder;
use Zend\Form\Form;
use Zend\Form\FormInterface;
use Zend\Stdlib\Hydrator\ClassMethods;
use Minibus\Controller\Exceptions\RestApiException;
use Zend\Filter\Callback;
use Zend\Config\Config;

/**
 *
 * @author Joachim Dornbusch
 * @copyright Joachim Dornbusch -AgroParisTech 2014-2015
 *            @1 juil. 2015
 */
class DataTypesHandler implements ServiceLocatorAwareInterface
{

    const NOT_IMPLEMENTED = 'not-implemented';
    
    use \Minibus\Util\Traits\ServiceLocatorAwareTrait;

    /**
     *
     * @return array
     */
    public function getDataTypes()
    {
        $config = $this->getServiceLocator()->get('Config');
        return $config['data_types'];
    }

    /**
     *
     * @return array
     */
    public function getDataTransferAgents()
    {
        $config = $this->getServiceLocator()->get('Config');
        return $config['data_transfer_agents'];
    }

    /**
     * Ecrase la hiérarchie des data types en ne gardant que les types feuilles pour des raisons de commodité
     *
     * @return array
     */
    public function getLastLevelOfdataTypes()
    {
        $dataTypes = $this->getDataTypes();
        $lastLevel = array();
        foreach ($dataTypes as $key1 => $value1) {
            if (! array_key_exists('children', $value1))
                $lastLevel[$key1] = $value1;
            else
                foreach ($value1['children'] as $key2 => $value2)
                    $lastLevel[$key2] = $value2;
        }
        return $lastLevel;
    }

    public function getFormatterIndex()
    {
        $lastLevel = $this->getLastLevelOfdataTypes();
        $formatters = array();
        foreach ($lastLevel as $key => $entry) {
            if (isset($entry['configuration']['browse']['general']['control'])) {
                $browseControl = $entry['configuration']['browse']['general']['control'];
                if ($browseControl == 'defaultBrowseControl')
                    if (isset($entry['configuration']['browse']['general']['datatable-formatter']))
                        $formatters[$key] = $entry['configuration']['browse']['general']['datatable-formatter'];
            }
        }
        return $formatters;
    }

    /**
     *
     * @param string $mode            
     * @param string $type            
     * @param string $endpoint            
     * @throws \Exception
     * @return boolean
     */
    public function hasDataType($mode, $type, $endpoint)
    {
        $lastLevel = $this->getLastLevelOfdataTypes();
        if (! array_key_exists($type, $lastLevel))
            return false;
        $typeConfiguration = $lastLevel[$type]['configuration'];
        
        switch ($mode) {
            case 'export':
                $modeConfiguration = $typeConfiguration['targets'];
                break;
            case 'acquisition':
                $modeConfiguration = $typeConfiguration['sources'];
                break;
            default:
                throw new \Exception("Le mode $mode n'existe pas.");
                break;
        }
        if (! array_key_exists($endpoint, $modeConfiguration))
            return false;
        
        return true;
    }

    /**
     *
     * @param string $mode            
     * @param string $type            
     * @param string $endpoint            
     * @throws \Exception
     * @return string
     */
    public function getTransferAgentClassName($mode, $type, $endpoint)
    {
        $transferAgentKey = $this->getTransferAgentKey($mode, $type, $endpoint);
        $dataTransferAgentIndex = $this->getDataTransferAgents();
        if (array_key_exists($transferAgentKey, $dataTransferAgentIndex))
            return $dataTransferAgentIndex[$transferAgentKey]['class'];
        return $dataTransferAgentIndex[self::NOT_IMPLEMENTED]['class'];
    }

    /**
     *
     * @param string $mode            
     * @param string $type            
     * @param string $endpoint            
     * @throws \Exception
     * @return string
     */
    public function getConverterClassName($mode, $type, $endpoint)
    {
        $transferAgentKey = $this->getTransferAgentKey($mode, $type, $endpoint);
        $dataTransferAgentIndex = $this->getDataTransferAgents();
        if (array_key_exists($transferAgentKey, $dataTransferAgentIndex) && isset($dataTransferAgentIndex[$transferAgentKey]['converter']))
            return $dataTransferAgentIndex[$transferAgentKey]['converter'];
        return null;
    }

    /**
     *
     * @param string $mode            
     * @param string $type            
     * @param string $endpoint            
     * @throws \Exception
     * @return string
     */
    public function getTransferAgentKey($mode, $type, $endpoint)
    {
        if (! $this->hasDataType($mode, $type, $endpoint))
            throw new \Exception("Le transfert de données $mode, $type, $endpoint n'est pas défini dans la configuration.");
        $lastLevel = $this->getLastLevelOfdataTypes();
        $typeConfiguration = $lastLevel[$type]['configuration'];
        switch ($mode) {
            case 'export':
                $modeConfiguration = $typeConfiguration['targets'];
                break;
            case 'acquisition':
                $modeConfiguration = $typeConfiguration['sources'];
                break;
        }
        $transferAgentKey = $modeConfiguration[$endpoint]['dataTransferAgent'];
        return $transferAgentKey;
    }

    /**
     *
     * @param string $mode            
     * @param string $type            
     * @param string $endpoint            
     * @throws \Exception
     * @return array
     */
    public function getTransferAgentLocks($mode, $type, $endpoint)
    {
        if (! $this->hasDataType($mode, $type, $endpoint))
            throw new \Exception("Le transfert de données $mode, $type, $endpoint n'est pas défini dans la configuration.");
        $lastLevel = $this->getLastLevelOfdataTypes();
        $typeConfiguration = $lastLevel[$type]['configuration'];
        switch ($mode) {
            case 'export':
                $modeConfiguration = $typeConfiguration['targets'];
                break;
            case 'acquisition':
                $modeConfiguration = $typeConfiguration['sources'];
                break;
        }
        if (array_key_exists($endpoint, $modeConfiguration)) {
            $endpointConfig = $modeConfiguration[$endpoint];
            if (is_array($endpointConfig) && array_key_exists('locks', $endpointConfig))
                return $modeConfiguration[$endpoint]['locks'];
        }
        throw new \Exception("Il n'y a pas de définition de verrous pour le transfert de données $mode, $type, $endpoint.");
    }
}