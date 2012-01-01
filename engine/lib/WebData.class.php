<?php
require_once dirname(__FILE__) . '/../inc/cde.inc.php';

/**
 * Process super global variables
 */
class WebData
{
	static private $instance = NULL;

	/**
	 * Get WebData object
	 */
	static public function getInstance($aSuperGlobals = array(), $bISecureData = TRUE)
	{
		if (self::$instance == NULL)
			self::$instance = new self($aSuperGlobals, $bISecureData);

		return self::$instance;
	}

	/**
	 * Hidden constructor
	 */
	protected function __construct($aSuperGlobals = array(), $bISecureData = TRUE)
	{
		try
		{
			if ($bISecureData)
				$this->secureWebData($aSuperGlobals);
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}

	/**
	 * Move global variables to WebData object
	 */
	protected function secureWebData($aSuperGlobals)
	{
		try
		{
			if (!is_array($aSuperGlobals) || count($aSuperGlobals) == 0)
				return;

			$this->shiftGlobals($aSuperGlobals, TRUE);
			$this->removeGlobals($aSuperGlobals);
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}

	/**
	 * Shift params from globals to local variables
	 */
	protected function shiftGlobals($aSuperGlobals, $bIsMakeSafety = FALSE)
	{
		try
		{
			// Add data to globals
			if (!isset($GLOBALS['_SERVER']))
				$GLOBALS['_SERVER'] = $_SERVER;

			if (!isset($GLOBALS['_REQUEST']))
				$GLOBALS['_REQUEST'] = (isset($_POST) && count($_POST) > 0 ? $_POST : $_GET);

			if (!isset($GLOBALS['_ENV']))
				$GLOBALS['_ENV'] = $_ENV;

			foreach ($aSuperGlobals as $name)
			{
				if (is_array($GLOBALS[$name]))
					foreach ($GLOBALS[$name] as $key => $value)
					{
						if ($bIsMakeSafety)
						{
							// Safe keys
							$key = FTStringUtils::addSlashes($key);

							// Safe values
							$value = FTStringUtils::addSlashes($value);
						}
						// Set vars
						$this->{
							str_replace('_', '', strtolower($name))}
						[$key] = $value;
					}
			}
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}

	/**
	 Remove GLOBAL variables
	 */
	protected function removeGlobals($aSuperGlobals)
	{
		// http://php.tonnikala.org/manual/pt_BR/security.globals.php#85447
		try
		{
			if (!ini_get('register_globals'))
				return FALSE;

			foreach ($aSuperGlobals as $name)
			{
				if (is_array($GLOBALS[$name]))
					foreach ($GLOBALS[$name] as $key => $value)
						if (isset($GLOBALS[$key]))
							unset($GLOBALS[$key]);

				if (isset($GLOBALS[$name]))
					unset($GLOBALS[$name]);
			}
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}
}
