<?php
namespace Minibus\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\Form\Form;

class DefaultProcessControlHelper extends AbstractHelper
{

    public function __invoke($keyData, $keyEndPoint, array $data, Form $form)
    {
        $html = '';
        $options = array_key_exists('options', $data) ? $data['options'] : array();
        $form->get('endpoint')->setValue($keyEndPoint);
        $html .= $this->getView()->partial('helpers/partials/default-process-control', array(
            'keyData' => $keyData,
            'keyEndPoint' => $keyEndPoint,
            'label' => $data['label'],
            'processDescription' => array_key_exists('process-description', $data) ? $data['process-description'] : '',
            'form' => $form,
            'class' => 'DefaultProcessControl',
            'options' => $options
        ));
        
        return $html;
    }
}