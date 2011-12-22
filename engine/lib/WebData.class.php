<?php
require_once dirname(__FILE__) . '/../inc/cde.inc.php';

/**
 * Process all super global variables
 */
class WebData
{
	static private $instance = NULL;

	static public function getInstance($aSuperGlobals = array(), $bISecureData = TRUE, $db = NULL)
	{
		if (self::$instance == NULL)
			self::$instance = new self($aSuperGlobals, $bISecureData, $db);

		return self::$instance;
	}

	protected function __construct($aSuperGlobals = array(), $bISecureData = TRUE, $db = NULL)
	{
		try
		{
			if ($bISecureData)
				$this->SecureWebData($aSuperGlobals, $db);
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}

	public function SecureWebData($aSuperGlobals, $db = NULL)
	{
		try
		{
			if (!is_array($aSuperGlobals) || count($aSuperGlobals) == 0)
				return FALSE;

			$this->UnregisterGlobals($aSuperGlobals);
			$this->ShiftGlobals($aSuperGlobals, TRUE, $db);
			$this->RemoveGlobals($aSuperGlobals);
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}

	protected function UnregisterGlobals($aSuperGlobals)
	{
		// http://php.tonnikala.org/manual/pt_BR/security.globals.php#85447

		try
		{
			if (!ini_get('register_globals'))
				return FALSE;

			foreach ($aSuperGlobals as $name)
				foreach ($GLOBALS[$name] as $key => $value)
					if (isset($GLOBALS[$key]))
						unset($GLOBALS[$key]);
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}

	protected function ShiftGlobals($aSuperGlobals, $bIsMakeSafety = FALSE, $db = NULL)
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

			// Shift params from globals to local variables
			foreach ($aSuperGlobals as $name)
			{
				if (is_array($GLOBALS[$name]))
					foreach ($GLOBALS[$name] as $key => $value)
					{
						if ($bIsMakeSafety)
						{
							// Safe keys
							$key = addslashes($key);

							// Safe values
							if ($db != NULL)
								$value = $db->quote($value);
							else
								$value = addslashes($value);
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

	protected function RemoveGlobals($aSuperGlobals)
	{
		try
		{
			// Remove all globals
			foreach ($aSuperGlobals as $name)
				if (!is_null(@$GLOBALS[strtoupper($name)]))
					unset($GLOBALS[strtoupper($name)]);
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}
}
