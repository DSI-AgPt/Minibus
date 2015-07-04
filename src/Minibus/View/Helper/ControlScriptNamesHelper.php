<?php

namespace Minibus\View\Helper;

use Zend\View\Helper\AbstractHelper;

class ControlScriptNamesHelper extends AbstractHelper {
	public function __invoke($dataTypes, $browse = false) {
		$filter = new \Zend\Filter\Word\CamelCaseToDash ();
		$view = $this->getView ();
		$scripts = array ();
		$html = '';
		foreach ( $dataTypes as $key1 => $data ) {
			if ($browse)
				$array = $data ['configuration'] ['browse'];
			else
				$array = $data ['configuration'] ['sources'];
			foreach ( $array as $key2 => $source ) {
				
				$scriptName = strtolower ( $filter->filter ( $source ['control'] ) );
				if (! in_array ( $scriptName, $scripts )) {
					$view->inlineScript ()->prependFile ( $view->basePath ( 'js/controls/' . $scriptName . '.js' ) );
					array_push ( $scripts, $scriptName );
				}
			}
		}
		return $html;
	}
}