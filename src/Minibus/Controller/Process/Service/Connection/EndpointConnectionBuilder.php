<?php
namespace Minibus\Controller\Process\Service\Connection;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Minibus\Model\Entity\Process;
use Minibus\Controller\Exceptions\RestApiException;
use Minibus\Controller\Process\Exception\ProcessException;
use Minibus\Model\Process\DataTransfer\DataTransferAgentInterface;
use Minibus\Model\Io\SftpClient;
use Minibus\Model\Process\DataTransfer\EndPointConnection;

/**
 *
 * @author Joachim Dornbusch
 * @copyright Joachim Dornbusch -AgroParisTech 2014-2015
 *            @30 juin 2015
 */
class EndpointConnectionBuilder implements ServiceLocatorAwareInterface
{

    const DATABASE_TYPE = 'database';

    const WEB_SERVICE_TYPE = 'webservice';

    const SFTP_TYPE = 'sftp';
    
    use \Minibus\Util\Traits\ServiceLocatorAwareTrait;

    /**
     *
     * @param DataTransferAgentInterface $dataTransferAgent            
     */
    public function visit(DataTransferAgentInterface $dataTransferAgent)
    {
        $connexionType = $dataTransferAgent->getConnectionType();
        $connexionParameters = $dataTransferAgent->getConnectionParameters();
        $concreteConnexion = null;
        switch ($connexionType) {
            case self::DATABASE_TYPE:
                $keys = array(
                    'driver',
                    'dbname',
                    'host',
                    'user',
                    'password'
                );
                foreach ($keys as $key) {
                    if (! $connexionParameters->offsetExists($key))
                        throw new ProcessException("Les paramètres de base de données sont incomplets : manque " . $key);
                }
                
                $dsn = $connexionParameters->driver . ':dbname=' . $connexionParameters->dbname . ';host=' . $connexionParameters->host;
                $user = $connexionParameters->user;
                $password = $connexionParameters->password;
                $concreteConnexion = new \PDO($dsn, $user, $password);
                break;
            case self::WEB_SERVICE_TYPE:
                $concreteConnexion = $this->getJsonRestClient();
                if (! $connexionParameters->offsetExists("url"))
                    throw new ProcessException("Les paramètres du client rest sont incomplets : manque l'url");
                if (! $connexionParameters->offsetExists("key"))
                    throw new ProcessException("Les paramètres du client rest sont incomplets : manque la clé d'authentification");
                try {
                    $concreteConnexion->setBaseUrl($connexionParameters->url);
                } catch (\Exception $e) {
                    throw new ProcessException("Url du web service non valable :" . $connexionParameters->url . " " . $e->getMessage());
                }
                $concreteConnexion->setKey($connexionParameters->key);
                break;
            case self::SFTP_TYPE:
                $concreteConnexion = $this->getSftpClient();
                if (! $connexionParameters->offsetExists("host"))
                    throw new ProcessException("Les paramètres du client sftp sont incomplets : manque le host");
                if (! $connexionParameters->offsetExists("port"))
                    throw new ProcessException("Les paramètres du client sftp sont incomplets : manque le port");
                if (! $connexionParameters->offsetExists("username"))
                    throw new ProcessException("Les paramètres du client sftp sont incomplets : manque username");
                if (! $connexionParameters->offsetExists("pubkeyfile"))
                    throw new ProcessException("Les paramètres du client sftp sont incomplets : manque pubkeyfile");
                if (! $connexionParameters->offsetExists("privkeyfile"))
                    throw new ProcessException("Les paramètres du client sftp sont incomplets : manque privkeyfile");
                
                $concreteConnexion->setConfig($connexionParameters);
                
                break;
            default:
                throw new ProcessException("Le type de connexion $connexionType n'est pas connu.");
                break;
        }
        $endPointConnection = new EndPointConnection();
        $endPointConnection->setObject($concreteConnexion);
        $dataTransferAgent->setEndPointConnection($endPointConnection);
    }

    /**
     *
     * @return \Minibus\Model\Io\Rest\Client
     */
    private function getJsonRestClient()
    {
        return $this->getServiceLocator()->get('json_rest_client');
    }

    /**
     *
     * @return \Minibus\Model\Io\Sftp\SftpClient
     */
    private function getSftpClient()
    {
        return $this->getServiceLocator()->get('sftp_client');
    }
}