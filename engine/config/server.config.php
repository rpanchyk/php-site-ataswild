<?php
require_once dirname(__FILE__) . '/../inc/cde.inc.php';

/**
 * Default server configuration
 */

// Set error reporting
ini_set('error_reporting', E_ALL);
ini_set('display_errors', E_ALL);

// Set own error & exception handlers
set_error_handler('FTException::errorHandler');
set_exception_handler('FTException::exceptionHandler');
register_shutdown_function('FTException::shutdownHandler');

// Set timezone
date_default_timezone_set("Europe/Kiev");

// Set locale
setlocale(LC_ALL, 'ru_RU.CP1251', 'rus_RUS.CP1251', 'Russian_Russia.1251', 'russian');
