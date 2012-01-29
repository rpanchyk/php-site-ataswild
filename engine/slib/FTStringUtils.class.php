<?php
require_once dirname(__FILE__) . '/../inc/cde.inc.php';

/**
 * Works with strings
 */
class FTStringUtils extends FTFireTrot
{
	/**
	 * Check string contains other string
	 * @param string $haystack - input string
	 * @param string $needle - string to find
	 * @param bool $case - case sensitivity, default = TRUE
	 * @param int $pos - position, default = 0
	 */
	static function contains($haystack, $needle, $case = TRUE, $pos = 0)
	{
		// http://stackoverflow.com/questions/834303/php-startswith-and-endswith-functions/3395821#3395821
		try
		{
			if ($case)
				return strpos($haystack, $needle, 0) === $pos;

			return stripos($haystack, $needle, 0) === $pos;
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}

	/**
	 * Check string starts with other string
	 * @param string $haystack - input string
	 * @param string $needle - string to find
	 * @param bool $case - case sensitivity, default = TRUE
	 */
	static function startsWith($haystack, $needle, $case = TRUE)
	{
		try
		{
			return self::contains($haystack, $needle, $case, 0);
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}
	/**
	 * Check string ends with other string
	 * @param string $haystack - input string
	 * @param string $needle - string to find
	 * @param bool $case - case sensitivity, default = TRUE
	 */
	static function endsWith($haystack, $needle, $case = TRUE)
	{
		try
		{
			return self::contains($haystack, $needle, $case, (strlen($haystack) - strlen($needle)));
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}

	/**
	 * Trim begin of string
	 * @param string $haystack - input string
	 * @param string $needle - string to find
	 */
	static function trimStart($haystack, $needle)
	{
		try
		{
			return substr($haystack, strlen($needle));
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}
	/**
	 * Trim end of string
	 * @param string $haystack - input string
	 * @param string $needle - string to find
	 */
	static function trimEnd($haystack, $needle)
	{
		try
		{
			return substr($haystack, 0, strrpos($haystack, $needle));
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}

	/**
	 * Convert string from cp1251 to utf-8
	 * @param string $string - input string in cp1251 encoding
	 * @return string in utf-8 encoding
	 */
	static function convertCp1251ToUtf8($string)
	{
		try
		{
			// http://webkev.com/2011/03/01/konvertiruem-windows-1251-v-utf-8-s-pomoshhyu-php/

			static $table = array(
				"\xA8" => "\xD0\x81",
				"\xB8" => "\xD1\x91",
				"\xA1" => "\xD0\x8E",
				"\xA2" => "\xD1\x9E",
				"\xAA" => "\xD0\x84",
				"\xAF" => "\xD0\x87",
				"\xB2" => "\xD0\x86",
				"\xB3" => "\xD1\x96",
				"\xBA" => "\xD1\x94",
				"\xBF" => "\xD1\x97",
				"\x8C" => "\xD3\x90",
				"\x8D" => "\xD3\x96",
				"\x8E" => "\xD2\xAA",
				"\x8F" => "\xD3\xB2",
				"\x9C" => "\xD3\x91",
				"\x9D" => "\xD3\x97",
				"\x9E" => "\xD2\xAB",
				"\x9F" => "\xD3\xB3",
			);

			return preg_replace('#[\x80-\xFF]#se', ' "$0" >= "\xF0" ? "\xD1".chr(ord("$0")-0x70) :
		                       ("$0" >= "\xC0" ? "\xD0".chr(ord("$0")-0x30) :
		                        (isset($table["$0"]) ? $table["$0"] : "")
		                       )', $string);
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}

	/**
	 * Get CRLF for current OS
	 */
	static function getCrlf()
	{
		try
		{
			return PHP_EOL;
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}

	/**
	 * Encrypt input string with MD5
	 */
	static function cryptString($string)
	{
		try
		{
			return md5($string);
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}

	/**
	 * Escapes a string with slashes
	 * @param Object $object - String or Array
	 */
	static public function addSlashes($object)
	{
		try
		{
			if (is_array($object))
			{
				$res = array();
				foreach ($object as $entryKey => $entryValue)
					$res[$entryKey] = self::addSlashes($entryValue);
				return $res;
			}
			elseif (is_string($object))
				return addslashes($object);
			else
				return $object;
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}
	/**
	 * Remove slashes from string
	 * @param Object $object - String or Array
	 */
	static public function stripSlashes($object)
	{
		try
		{
			if (is_array($object))
			{
				$res = array();
				foreach ($object as $entryKey => $entryValue)
					$res[$entryKey] = self::stripSlashes($entryValue);
				return $res;
			}
			elseif (is_string($object))
				return stripslashes($object);
			else
				return $object;
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}

	/**
	 * Transforms the notation for numbers (like '2M')
	 * to an integer (2*1024*1024 in this case)
	 * @param string $string - input string
	 */
	static public function getConfigStringSizeInBytes($string)
	{
		// Get value, without last char
		$ret = substr($string, 0, -1);

		$lastLetter = substr($string, -1);
		switch (strtoupper($lastLetter))
		{
			case 'P':
				$ret *= 1024;
			case 'T':
				$ret *= 1024;
			case 'G':
				$ret *= 1024;
			case 'M':
				$ret *= 1024;
			case 'K':
				$ret *= 1024;
				break;
			default:
				$ret = $string;
				break;
		}

		return $ret;
	}
}
