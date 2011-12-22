<?php
require_once dirname(__FILE__) . '/../inc/cde.inc.php';

/**
 * Core functionality
 */
class FTCore extends FTFireTrot
{
	const fileDefaultExtension = 'php';

	/**
	 * Checks system requirements
	 */
	static public function checkSystemRequirements()
	{
		try
		{
			//global $engineConfig;

			return TRUE;
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}

	/**
	 * Includes (loads) a file into current context
	 */
	static public function loadFile($strDirPath, $strName, $bIsIncludeOnce = TRUE, $strSuffix = '', $strExtension = self::fileDefaultExtension)
	{
		try
		{
			$filePath = self::getFilePath($strDirPath, $strName, $strSuffix, $strExtension);

			if ($bIsIncludeOnce)
				require_once $filePath;
			else
				require $filePath;
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}
	static public function loadClass($strDirPath, $strName, $bIsIncludeOnce = TRUE, $strExtension = self::fileDefaultExtension)
	{
		try
		{
			self::loadFile($strDirPath, $strName, $bIsIncludeOnce, 'class', $strExtension);
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}
	static public function loadConfig($strDirPath, $strName, $bIsIncludeOnce = TRUE, $strExtension = self::fileDefaultExtension)
	{
		try
		{
			self::loadFile($strDirPath, $strName, $bIsIncludeOnce, 'config', $strExtension);
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}
	static public function loadInterface($strDirPath, $strName, $bIsIncludeOnce = TRUE, $strExtension = self::fileDefaultExtension)
	{
		try
		{
			self::loadFile($strDirPath, $strName, $bIsIncludeOnce, 'interface', $strExtension);
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}

	static public function getFilePath($strDirPath, $strEntityName, $strEntity = '', $strExtension = self::fileDefaultExtension)
	{
		try
		{
			$strErrorMsg = 'Failed to load ' . $strEntity . ' "' . $strEntityName . '": ';

			if (!defined('DS'))
				throw new Exception($strErrorMsg . 'Constant "DS" is not defined');

			if (empty($strDirPath))
				throw new Exception($strErrorMsg . 'Empty folder path');

			if (empty($strEntityName))
				throw new Exception($strErrorMsg . 'Empty ' . $strEntity . ' name');

			if (!is_dir($strDirPath))
				throw new Exception($strErrorMsg . 'Directory "' . $strDirPath . '" is not valid');

			$filePath = rtrim($strDirPath, DS) . DS . $strEntityName . (!empty($strEntity) ? ('.' . $strEntity) : '') . '.' . $strExtension;

			if (!file_exists($filePath))
				throw new Exception($strErrorMsg . 'File "' . $filePath . '" not found', E_ERROR);

			return $filePath;
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}

	static public function createDirs(Array $aDirs)
	{
		try
		{
			foreach ($aDirs as $dir)
			{
				if (file_exists($dir) && is_dir($dir))
					continue;

				FTException::throwOnTrue(!mkdir($dir), 'Cannot create dir: "' . $dir . '"');
			}
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}
}
