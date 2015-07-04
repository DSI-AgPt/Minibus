<?php

namespace Minibus\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\Form\Form;

class DefaultBrowseControlHelper extends AbstractHelper {
	public function __invoke($keyData, array $browseConfiguration) {
		$html = '';
		$html .= $this->getView ()->partial ( 'helpers/partials/default-browse-control', array (
				'keyData' => $keyData,
				'class' => 'DefaultBrowseControl',
				'columns' => $browseConfiguration ['columns'] 
		) );
		
		return $html;
	}
}