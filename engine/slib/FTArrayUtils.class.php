<?php
require_once dirname(__FILE__) . '/../inc/cde.inc.php';

/**
 * Works with arrays
 */
class FTArrayUtils extends FTFireTrot
{
	/**
	 * Case-intensitive in_array
	 * http://www.php.net/manual/ru/function.in-array.php#89256
	 */
	static function inArrayCI($needle, $haystack)
	{
		try
		{
			return in_array(strtolower($needle), array_map('strtolower', $haystack));
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}

	/**
	 * Checks array for null,is_array,count
	 */
	static function checkData($data, $nMinCount = 1)
	{
		try
		{
			$result = isset($data) && is_array($data);

			return $nMinCount > 0 ? $result && count($data) >= $nMinCount : $result;
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}
}
