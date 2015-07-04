<?php

namespace Minibus\View\Helper;

use Zend\View\Helper\AbstractHelper;

class SelectAcademicYearHelper extends AbstractHelper {
	public function __invoke($key, $label, $minYear, $maxYear) {
		$html = '<select class="year-select-menu" id="year-select-menu-' . $key . '">';
		for($i = $minYear; $i <= $maxYear; $i ++) {
			$html .= '<option value=' . $i . '>';
			$html .= $i . '/' . ($i + 1);
			$html .= '</option>';
		}
		$html .= '</select>';
		return $html;
	}
}