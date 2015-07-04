<?php
namespace Minibus\Controller\Log\Service;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 *
 * @author Joachim Dornbusch
 * @copyright Joachim Dornbusch -AgroParisTech 2014-2015
 *            @30 juin 2015
 */
class LogHandler implements ServiceLocatorAwareInterface
{
    
    use \Minibus\Util\Traits\ServiceLocatorAwareTrait;
    
    use\Minibus\Util\Traits\EntityManagerTrait;

    /**
     *
     * @return \Minibus\Model\Process\Service\ProcessStateHandler
     */
    private function getProcessStateHandler()
    {
        return $this->getServiceLocator()->get('process-state-handler');
    }

    /**
     *
     * @param string $logIdentifier            
     * @throws \Exception
     * @return string
     */
    public function getLogPath($logIdentifier)
    {
        $config = $this->getServiceLocator()->get('Config');
        if (! array_key_exists('process-log-directory', $config))
            throw new \Exception("La configuration local.php devrait contenir une entrée process-log-directory");
        $logDirectory = $config['process-log-directory'];
        if (! is_dir($logDirectory)) {
            $succes = mkdir($logDirectory, '744');
            if (! $succes)
                throw new \Exception("Impossible de créer le répertoire $logDirectory");
        }
        $logFilePath = $logDirectory . '/' . $logIdentifier . '.txt';
        return $logFilePath;
    }
}