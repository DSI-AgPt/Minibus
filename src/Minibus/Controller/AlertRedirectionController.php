<?php
namespace Minibus\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Mvc\MvcEvent;
use CdmFr\Model\Services\CdmFrDeserializer;
use Doctrine\ORM\EntityManager;
use Minibus\Controller\Form\CdmFrUploadForm;

/**
 *
 * @author Joachim Dornbusch
 * @copyright Joachim Dornbusch -AgroParisTech 2014-2015
 *            @30 juin 2015
 */
class AlertRedirectionController extends AbstractActionController
{

    /**
     */
    public function redirectAction()
    {
        $route = $this->params("mode", 'export');
        $data = array();
        $data['process'] = $this->params("process", null);
        $data['execution'] = $this->params("execution", null);
        $route = $route == 'acquisition' ? 'acquisition' : 'export';
        $this->flashMessenger()->addMessage(serialize($data));
        $this->redirect()->toRoute($route);
    }
}
