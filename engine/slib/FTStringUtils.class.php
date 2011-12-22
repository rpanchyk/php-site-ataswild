<?php
require_once dirname(__FILE__) . '/../inc/cde.inc.php';

/**
 * Works with strings
 */
class FTStringUtils extends FTFireTrot
{
	static function contains($haystack, $needle, $case = TRUE, $pos = 0)
	{
		try
		{
			// http://stackoverflow.com/questions/834303/php-startswith-and-endswith-functions/3395821#3395821

			if ($case)
				$result = (strpos($haystack, $needle, 0) === $pos);
			else
				$result = (stripos($haystack, $needle, 0) === $pos);

			return $result;
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}

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

	static function convertCp1251ToUtf8New($strInput)
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
		                       )', $strInput);
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
			global $request;

			FTException::throwOnTrue(!FTArrayUtils::checkData(@$request->dataWeb->server), 'No data to get CRLF value');

			// Default
			$CRLF = "\n";

			// Get specific value
			if (strpos($request->dataWeb->server['SCRIPT_FILENAME'], ':'))
				$CRLF = "\r" . $CRLF;

			return $CRLF;
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}

	/**
	 * Encrypt input string
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
}
