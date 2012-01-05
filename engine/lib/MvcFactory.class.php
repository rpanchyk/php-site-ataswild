<?php
require_once dirname(__FILE__) . '/../inc/cde.inc.php';

/**
 * Mvc factory class - creates an instance of given type
 */
class MvcFactory
{
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
	 */
	static public function create($strAppName, $strInstatnce, $args = NULL, $bIsSingleton = TRUE)
	{
		try
		{
			// Result instance
			$oInstance = NULL;

			if (is_null($strAppName))
				return $oInstance;

			// Define path to file
			$path = FTFileSystem::pathCombine(APP_PATH, $strAppName, $strInstatnce . '.php');
			if (!file_exists($path))
				throw new Exception('File not found: ' . $path);

			// Load file
			require_once $path;

			// Define class name
			$strClassName = ucfirst($strAppName) . ucfirst($strInstatnce);
			if (!class_exists($strClassName))
				throw new Exception('Class not found: ' . $strClassName);

			// Create instance
			if ($bIsSingleton)
			{
				if (!isset(self::$aInstances[$strClassName]) || self::$aInstances[$strClassName] == NULL)
					self::$aInstances[$strClassName] = new $strClassName($args);

				$oInstance = self::$aInstances[$strClassName];
			}
			else
				$oInstance = new $strClassName($args);

			// Get controller config
			if ($strInstatnce === ParamsMvc::ENTITY_CONTROLLER && !@isset($oInstance->config))
			{
				$pathConfig = FTFileSystem::pathCombine(APP_PATH, $strAppName, 'app.' . EntityFileType::CONFIG_TYPE . '.php');
				if (file_exists($pathConfig))
				{
					// All vars in config
					global $request;
					$oInstance->config = require_once $pathConfig;
				}
			}

			return $oInstance;
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
				return (self::$instance = NULL);
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}
}
