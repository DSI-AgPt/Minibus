<?php

namespace Minibus\Util\Encoding;

class JsonCleaner {
	public static function removeUnicodeSequences($string) {
		return preg_replace_callback ( '/\\\\u([0-9a-fA-F]{4})/', function ($match) {
			return mb_convert_encoding ( pack ( 'H*', $match [1] ), 'UTF-8', 'UTF-16BE' );
		}, $string );
	}
}
