<?php
require_once dirname(__FILE__) . '/../inc/cde.inc.php';

/**
 * Guid generator
 */
class Guid extends FTFireTrot
{
	// http://www.phpclasses.org/package/1738-PHP-Generate-global-unique-identifiers-text-values.html

	private $valueMD5;

	public function __construct()
	{
		try
		{
			$this->getGuid();
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}

	public function newGuid()
	{
		try
		{
			return new Guid();
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}
	public function toString()
	{
		try
		{
			$raw = strtolower($this->valueMD5);

			return substr($raw, 0, 8) . '-' . substr($raw, 8, 4) . '-' . substr($raw, 12, 4) . '-' . substr($raw, 16, 4) . '-' . substr($raw, 20);
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}

	protected function getGuid()
	{
		try
		{
			$valueBeforeMD5 = $this->getLocalInfo() . ':' . $this->currentTimeMillis() . ':' . $this->nextLong();

			$this->valueMD5 = md5($valueBeforeMD5);
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}
	protected function getLocalInfo()
	{
		try
		{
			global $request;

			FTException::throwOnTrue(!isset($request->dataWeb->server['SERVER_NAME']), 'No server address');

			return strtolower(php_uname() . '/' . $request->dataWeb->server['SERVER_NAME']);
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}
	protected function currentTimeMillis()
	{
		try
		{
			list($usec, $sec) = explode(' ', microtime());

			return $sec . substr($usec, 2, 3);
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}
	protected function nextLong()
	{
		try
		{
			return (rand(0, 1) ? '-' : '') . rand(1000, 9999) . rand(1000, 9999) . rand(1000, 9999) . rand(100, 999) . rand(100, 999);
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}
}
