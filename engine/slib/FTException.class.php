<?php
require_once dirname(__FILE__) . '/../inc/cde.inc.php';

/**
 * Exception handler
 */
class FTException extends Exception
{
	const logDirPath = LOGS_PATH;
	const defaultErrorID = 'SYSTEM';

	public function __construct($message = NULL, $code = 0)
	{
		parent::__construct($message, intval($code));
	}

	static public function toString(Exception $exception)
	{
		// http://php.net/manual/ru/language.exceptions.extending.php

		try
		{
			$result = 'Exception in file: ' . $exception->getFile() . ' [' . $exception->getLine() . '] ';
			$result .= "\n" . 'with message: ' . $exception->getMessage();
			$result .= "\n" . 'Stack trace: ' . "\n" . $exception->getTraceAsString();

			return 'Exception [Code#' . $exception->getCode() . ']: ' . $exception->getMessage() . ' in ' . $exception->getFile() . ' on line ' . $exception->getLine();
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}

	static public function toStringForWeb(Exception $exception, $bIsIncludeStackTrace = TRUE)
	{
		// http://php.net/manual/ru/language.exceptions.extending.php

		try
		{
			$result = '<div style="font: bold italic 12px Verdana; color:DD0000;">' . self::toString($exception) . '</div>';

			if ($bIsIncludeStackTrace)
				$result .= '<div style="font: bold 12px Courier New; color: 0000BB;">Stack trace:<br /><textarea style="width:100%; border:solid 1px #848388; background-color:#FFFF99;" rows="10" readonly="readonly">' . $exception->getTraceAsString() . '</textarea></div>';

			return $result;
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}

	static public function errorHandler($errno, $errstr, $errfile, $errline)
	{
		// http://ua.php.net/manual/en/function.set-error-handler.php
		// http://forum.vingrad.ru/faq/topic-164992.html

		try
		{
			if (!(error_reporting() & $errno))
			{
				// This error code is not included in error_reporting
				return;
			}

			$exception = new FTException($errstr, $errno);
			$exception->file = $errfile;
			$exception->line = $errline;
			throw $exception;
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}
	static public function exceptionHandler($exception, $bIsIncludeStackTrace = TRUE)
	{
		// http://ua.php.net/manual/en/function.set-exception-handler.php

		try
		{
			global $engineConfig;

			// Log exception
			self::saveEx($exception);

			if ($engineConfig['system']['is_debug'])
			{
				// Clean output buffer
				ob_end_clean();

				// Show error
				die(self::toStringForWeb($exception, $bIsIncludeStackTrace));
			}
			else
				header('HTTP/1.1 500 Internal Server Error');

			throw $exception;
		}
		catch (Exception $ex)
		{
		}
	}
	static public function shutdownHandler()
	{
		// http://php.net/manual/ru/function.register-shutdown-function.php

		try
		{
			// Is error?
			$error = error_get_last();

			if (null !== $error && E_ERROR === $error['type'])
			{
				// FATAL_ERROR occured				
				$exception = new FTException($error['message'], $error['type']);
				$exception->file = $error['file'];
				$exception->line = $error['line'];
				self::exceptionHandler($exception, FALSE);
			}
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}

	static public function throwOnTrue($condition, $errorMessage, $errorCode = 0)
	{
		try
		{
			if ($condition)
			{
				// Keep info about caller, but not this method
				$backtrace = debug_backtrace();

				// Create ex and fill params
				$exception = new Exception($errorMessage, $errorCode);
				$exception->file = $backtrace[0]['file'];
				$exception->line = $backtrace[0]['line'];

				throw $exception;
				//throw new FTException($errorMessage, $errorCode);
			}
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}
	static public function throwEx(Exception $ex)
	{
		try
		{
			self::exceptionHandler($ex, TRUE);
		}
		catch (Exception $ex2)
		{
			throw $ex2;
		}
	}
	static public function saveEx(Exception $ex, $errorID = self::defaultErrorID)
	{
		try
		{
			$CRLF = FTStringUtils::getCrlf();

			// Form message
			$message = 'Date: ' . date('Y-m-d H:i:s', time()) . $CRLF;
			$message .= 'File: "' . $ex->getFile() . '", line: ' . $ex->getLine() . $CRLF;
			$message .= 'Message: "' . $ex->getMessage() . '" (Code: ' . $ex->getCode() . ')' . $CRLF;
			$message .= 'Trace:' . $CRLF . str_replace("\n", $CRLF, $ex->getTraceAsString()) . $CRLF;

			// Save (!)
			self::saveMessage($message, $errorID);
		}
		catch (Exception $ex2)
		{
			echo '<pre>';
			print_r($ex2);
			echo '</pre>';

			throw $ex2;
		}
	}
	static public function saveMessage($message, $errorID = self::defaultErrorID)
	{
		try
		{
			// Check logs dir
			if (!file_exists(self::logDirPath) || !is_dir(self::logDirPath))
				die('No logs directory: ' . self::logDirPath);
			if (!is_writable(self::logDirPath))
				die('Logs directory is not writable: ' . self::logDirPath);

			// Get log dir name
			$logDir = FTFileSystem::pathCombine(self::logDirPath, date('Y-m', time()));

			// Check & create if need
			if (!file_exists($logDir) && !is_dir($logDir) && !mkdir($logDir))
				die('Cannot create logs directory: ' . $logDir);
			if (!is_writable($logDir))
				die('Logs directory is not writable: ' . $logDir);

			if (!is_writable($logDir))
				die('Logs directory is not writable: ' . $logDir);

			// Get log file path
			$filePath = FTFileSystem::pathCombine($logDir, date('Y-m-d', time()) . '.txt');

			// Check it
			if (file_exists($filePath) && !is_writable($filePath))
				die('No permissions for log file: ' . $filePath);

			$CRLF = FTStringUtils::getCrlf();

			// Form message
			$strMessage = $CRLF;
			$strMessage .= '[' . $errorID . ']' . $CRLF;
			$strMessage .= $message;

			// Write to file
			$fp = fopen($filePath, 'a');
			fwrite($fp, $strMessage);
			fclose($fp);
		}
		catch (Exception $ex2)
		{
			echo '<pre>';
			print_r($ex2);
			echo '</pre>';

			throw $ex2;
		}
	}
}
