<?php
require_once dirname(__FILE__) . '/../inc/cde.inc.php';

/**
 * Mvc factory class - creates an instance of given type
 */
class MvcFactory
{
	/**
	 * Holds intances collection
	 * @var Array
	 */
	static private $aInstances = array();

	private function __construct()
	{
	}
	private function __clone()
	{
	}

	/**
	 * Creates an instance of given type
	 * @param String $strAppName - application name
	 * @param String $strInstatnce - instance type
	 * @param Array $args - arguments for instance constructor
	 * @param Boolean $bIsSingleton - create instance as singleton (default: TRUE)
	 * @return Instance of given type
	 */
	static public function create($strAppName, $strInstatnce, $args = NULL, $bIsSingleton = TRUE)
	{
		try
		{
			FTException::throwOnTrue(is_null($strAppName), 'No app name');

			// Load file
			FTCore::loadFile(FTFileSystem::pathCombine(APP_PATH, $strAppName), $strInstatnce);

			// Define class name
			$strClassName = ucfirst($strAppName) . ucfirst($strInstatnce);
			FTException::throwOnTrue(!class_exists($strClassName), 'Class not found: ' . $strClassName);

			// Create instance as prototype
			if (!$bIsSingleton)
				return new $strClassName($args);

			// Create instance as singleton
			if (!isset(self::$aInstances[$strClassName]))
				self::$aInstances[$strClassName] = new $strClassName($args);
			return self::$aInstances[$strClassName];
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}

	/**
	 * Destroy singleton instance
	 * @param String $strClassName - class name
	 */
	static public function destroy($strClassName)
	{
		try
		{
			if (isset(self::$aInstances[$strClassName]) && self::$aInstances[$strClassName] != NULL)
				return (self::$aInstances[$strClassName] = NULL);
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}
}
