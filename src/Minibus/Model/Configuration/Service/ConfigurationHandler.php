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

/**
 *
 * @author Joachim Dornbusch
 * @copyright Joachim Dornbusch -AgroParisTech 2014-2015
 *            1 juil. 2015
 */
class ConfigurationHandler implements ServiceLocatorAwareInterface
{

    private $configurationData = false;
    
    use\Minibus\Util\Traits\ServiceLocatorAwareTrait;
    use\Minibus\Util\Traits\EntityManagerTrait;

    /**
     *
     * @return array
     */
    public function getConfiguration()
    {
        $configuration = $this->getConfigurationFromDatabase(true);
        if (is_null($configuration))
            $configuration = $this->getDefaultConfiguration();
        return $configuration;
    }

    /**
     *
     * @param bool $asArray            
     * @return NULL|array
     */
    public function getConfigurationFromDatabase($asArray = false)
    {
        $em = $this->getEntityManager();
        $query = $em->createQueryBuilder()
            ->select('config')
            ->from('Minibus\Model\Entity\Configuration', 'config')
            ->getQuery();
        $result = $query->getResult($asArray ? AbstractQuery::HYDRATE_ARRAY : AbstractQuery::HYDRATE_OBJECT);
        if (count($result) == 0)
            return null;
        else
            return $result[0];
    }

    /**
     *
     * @return array
     */
    public function getDefaultConfiguration()
    {
        return $this->getServiceLocator()->get('Config')['default_configuration'];
    }

    /**
     *
     * @throws \Exception
     */
    public function save()
    {
        if (! isset($this->configurationData))
            throw new \Exception("Aune donnée de configuation n'a encore été ajoutée");
        $configuration = $this->getConfigurationFromDatabase();
        $em = $this->getEntityManager();
        if (is_null($configuration)) {
            $configuration = new Configuration();
            $em->persist($configuration);
            $defaultConfiguration = $this->getDefaultConfiguration();
            $data = array_merge($defaultConfiguration, $this->configurationData);
        }
        $hydrator = new ClassMethods();
        $hydrator->hydrate($this->configurationData, $configuration);
        $em->flush();
    }

    /**
     *
     * @param unknown $data            
     * @throws RestApiException
     * @return \Minibus\Model\Configuration\Service\ConfigurationHandler
     */
    public function setConfigurationData($data)
    {
        $form = $this->getForm();
        $form->setData($data);
        if ($form->isValid()) {
            $this->configurationData = $form->getData();
            return $this;
        } else {
            throw new RestApiException($form->getMessages());
        }
    }

    /**
     *
     * @return Form
     */
    public function getForm()
    {
        $builder = new AnnotationBuilder($this->getEntityManager());
        return $builder->createForm('Minibus\Model\Entity\Configuration');
    }
}