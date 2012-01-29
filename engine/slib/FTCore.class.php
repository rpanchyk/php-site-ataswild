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
	 * @return Boolean
	 */
	static public function checkSystemRequirements()
	{
		try
		{
			global $engineConfig;

			// Check PHP version
			if (version_compare(PHP_VERSION, $engineConfig['requirements']['php_min_version'], '<'))
				throw new Exception('Newer PHP version required: ' . $engineConfig['requirements']['php_min_version'] . '. Current version: ' . PHP_VERSION);

			// Check session started
			if (session_id() === '')
				throw new Exception('Session is not started');
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}

	/**
	 * Load file into current context
	 * @param String $strDirPath - path to folder
	 * @param String $strName - file prefix
	 * @param Boolean $bIsIncludeOnce - include once (default: TRUE)
	 * @param String $strSuffix - file suffix
	 * @param String $strExtension - file extension
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
	/**
	 * Load class into current context
	 * @param String $strDirPath - path to folder
	 * @param String $strName - class name
	 * @param Boolean $bIsIncludeOnce - include once (default: TRUE)
	 * @param String $strExtension - file extension (default: 'php')
	 */
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
	/**
	 * Load config into current context
	 * @param String $strDirPath - path to folder
	 * @param String $strName - config name
	 * @param Boolean $bIsIncludeOnce - include once (default: TRUE)
	 * @param String $strExtension - file extension (default: 'php')
	 */
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
	/**
	 * Load include into current context
	 * @param String $strDirPath - path to folder
	 * @param String $strName - include name
	 * @param Boolean $bIsIncludeOnce - include once (default: TRUE)
	 * @param String $strExtension - file extension (default: 'php')
	 */
	static public function loadInclude($strDirPath, $strName, $bIsIncludeOnce = TRUE, $strExtension = self::fileDefaultExtension)
	{
		try
		{
			self::loadFile($strDirPath, $strName, $bIsIncludeOnce, 'inc', $strExtension);
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}
	/**
	 * Load interface into current context
	 * @param String $strDirPath - path to folder
	 * @param String $strName - interface name
	 * @param Boolean $bIsIncludeOnce - include once (default: TRUE)
	 * @param String $strExtension - file extension (default: 'php')
	 */
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

	/**
	 * Return path to file
	 * @param String $strDirPath - path to folder
	 * @param String $strEntityName - partial file name
	 * @param String $strEntity - file type (@see EntityFileType)
	 * @param String $strExtension - file extension
	 * @return String
	 */
	static public function getFilePath($strDirPath, $strEntityName, $strEntity = '', $strExtension = self::fileDefaultExtension)
	{
		try
		{
			$strErrorMsg = 'Failed to load ' . $strEntity . ' "' . $strEntityName . '": ';

			if (!defined('DS'))
				throw new Exception($strErrorMsg . 'Constant "DS" is not defined');

			if (empty($strDirPath) || !is_dir($strDirPath))
				throw new Exception($strErrorMsg . 'Directory "' . $strDirPath . '" is not valid');

			if (empty($strEntityName))
				throw new Exception($strErrorMsg . 'Empty ' . $strEntity . ' name');

			$fileName = $strEntityName . (!empty($strEntity) ? ('.' . $strEntity) : '') . '.' . $strExtension;
			$filePath = rtrim($strDirPath, DS) . DS . $fileName;

			if (!file_exists($filePath))
				throw new Exception($strErrorMsg . 'File "' . $filePath . '" not found', E_ERROR);

			return $filePath;
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}

	/**
	 * Create dirs
	 * @param Array $aDirs - list of folders
	 */
	static public function createDirs(Array $aDirs)
	{
		try
		{
			foreach ($aDirs as $dir)
			{
				if (file_exists($dir) && is_dir($dir))
					continue;

				if (!mkdir($dir))
					throw new Exception('Cannot create dir: "' . $dir . '"');

				if (!chmod($dir, 0666))
					throw new Exception('Cannot chmod dir: "' . $dir . '"');
			}
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}
}
