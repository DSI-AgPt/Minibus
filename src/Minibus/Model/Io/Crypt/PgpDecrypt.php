<?php
/**
 * 
 * @author Joachim Dornbusch 1 juil. 2015
 * @copyright Joachim Dornbusch - AgroParisTech - 2014,2015
 *
 */
namespace Minibus\Model\Io\Crypt;

use Zend\Log\Logger;
use Zend\Config\Config;
use Zend\ServiceManager\ServiceLocatorAwareInterface;

class PgpDecrypt implements ServiceLocatorAwareInterface
{
    use \Minibus\Util\Traits\ServiceLocatorAwareTrait;

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
     * @param string $remoteFileName            
     * @param string $localFilename            
     * @throws \Exception
     */
    public function decrypt($encryptedFilePath, $decryptedFilePath, $passPhrase)
    {
        if (! file_exists($encryptedFilePath))
            throw new \Exception("Le fichier $encryptedFilePath n'existe pas");
        
        return shell_exec("echo $passPhrase | gpg --no-tty --passphrase-fd 0 -o $decryptedFilePath -d $encryptedFilePath");
    }
}