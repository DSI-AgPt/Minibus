<?php

/**
 * 
 * @author Julie CORNACCHIA
 *
 */
namespace Minibus\Model\Io\Crypt;

use Zend\Log\Logger;
use Zend\Config\Config;
use Zend\ServiceManager\ServiceLocatorAwareInterface;

class PgpEncrypt implements ServiceLocatorAwareInterface {
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
    public function encrypt($decryptedFilePath, $encryptedFilePath)
    {
        if (! file_exists ( $decryptedFilePath ))
            throw new \Exception ( "Le fichier $decryptedFilePath n'existe pas" );
        
        return shell_exec ( "gpg --no-tty --lsign-key CS_applis_UPSay --recipient CS_applis_UPSay --output $encryptedFilePath -encrypt $decryptedFilePath" );
    }
}