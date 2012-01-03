<?php
require_once dirname(__FILE__) . '/../inc/cde.inc.php';

/**
 * Works with arrays
 */
class FTArrayUtils extends FTFireTrot
{
	/**
	 * Case-intensitive in_array
	 * @param String $needle - string to find
	 * @param Array $haystack - input array
	 * @return Boolean
	 * @link http://www.php.net/manual/ru/function.in-array.php#89256
	 */
	static public function containsValueCI($needle, $haystack)
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
	 * Case-intensitive array_key_exists
	 * @param String $needle - string to find
	 * @param Array $haystack - input array
	 * @return Boolean
	 */
	static public function containsKeyCI($needle, $haystack)
	{
		try
		{
			return array_key_exists(strtolower($needle), $haystack);
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}

	/**
	 * Checks array for null, is_array, count
	 * @param Array $data - input array
	 * @param int $nMinCount - minimal count value
	 * @return Boolean
	 */
	static public function checkData($data, $nMinCount = 1)
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
