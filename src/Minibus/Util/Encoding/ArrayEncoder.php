<?php

namespace Minibus\Util\Encoding;

class ArrayEncoder {
	public static function utf8Converter($array) {
		array_walk_recursive ( $array, function (&$item, $key) {
			if (is_string ( $item ))
				if (! mb_detect_encoding ( $item, 'utf-8', true )) {
					$item = utf8_encode ( $item );
				}
		} );
		
		return $array;
	}
}
