<?php
/**
 * 
 * @author Joachim Dornbusch 1 juil. 2015
 * @copyright Joachim Dornbusch - AgroParisTech - 2014,2015
 *
 */
namespace Minibus\Model\Io\Sftp;

use Zend\Log\Logger;
use Zend\Config\Config;
use Zend\ServiceManager\ServiceLocatorAwareInterface;

class SftpClient implements ServiceLocatorAwareInterface
{
    use\Minibus\Util\Traits\ServiceLocatorAwareTrait;

    /**
     *
     * @var \Zend\Config\Config
     */
    private $config;

    /**
     *
     * @var resource
     */
    private $session;

    /**
     *
     * @param Config $config            
     */
    public function setConfig(Config $config)
    {
        $this->config = $config;
    }

    /**
     *
     * @throws \Exception
     * @return boolean
     */
    public function connect()
    {
        $this->session = ssh2_connect($this->config->host, $this->config->port);
        if (false === $this->session)
            throw new \Exception("Echec de la connexion ssh " . print_r($this->config->toArray(), true));
        $auth = ssh2_auth_pubkey_file($this->session, $this->config->username, $this->config->pubkeyfile, $this->config->privkeyfile);
        if (true !== $auth)
            throw new \Exception("Echec de l'authentification ssh " . print_r($this->config->toArray(), true));
        return true;
    }

    /**
     *
     * @param string $remoteFileName            
     * @param string $localFilename            
     * @throws \Exception
     */
    public function receive($remoteFileName, $localFilename)
    {
        if (empty($this->session))
            throw new \Exception("Veuillez vous connecter au serveur avant de demander un fichier");
        ssh2_scp_recv($this->session, $remoteFileName, $localFilename);
    }
}