<?php
namespace Minibus\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\Serializer\Adapter\Json;
use Minibus\Controller\Form\CdmFrUploadForm;
use Zend\Debug\Debug;
use Minibus\Controller\Exceptions\RestApiException;
use JMS\Serializer\SerializerBuilder;

/**
 *
 * @author Joachim Dornbusch
 * @copyright Joachim Dornbusch -AgroParisTech 2014-2015
 *            @30 juin 2015
 */
class LogRestController extends AbstractRestfulController
{

    /**
     *
     * @var int
     */
    const DEFAULT_NB_LINES = 100;

    /**
     * (non-PHPdoc)
     *
     * @see \Zend\Mvc\Controller\AbstractRestfulController::get()
     */
    public function get($id)
    {
        $logIdentifier = str_replace('..', '', $id);
        $nbLines = $this->params('nblines', self::DEFAULT_NB_LINES);
        $filePath = $this->getLoghandler()->getLogPath($logIdentifier);
        
        if (false === file_exists($filePath)) {
            // TODO gérer fichier inexistant
        }
        header("Content-Type: text/plain");
        header("Content-Disposition: attachment; filename=\"{$logIdentifier}.txt\"");
        $file = escapeshellarg($filePath);
        if ($nbLines == 'all')
            $last_lines = `cat $file`;
        else {
            $nbLines = intval($nbLines);
            $last_lines = `tail -n $nbLines $file`;
        }
        echo $last_lines;
        exit();
    }

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
     * @return \Minibus\Controller\Log\Service\LogHandler
     */
    protected function getLoghandler()
    {
        return $this->getServiceLocator()->get('log-handler');
    }
}
