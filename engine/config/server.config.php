<?php
require_once dirname(__FILE__) . '/../inc/cde.inc.php';

/**
 * Default server configuration
 */

// Set PHP settings
ini_set('error_reporting', E_ALL);
ini_set('display_errors', TRUE);
ini_set('short_open_tag', TRUE);
ini_set('default_charset', 'UTF-8');
ini_set('file_uploads', TRUE);
ini_set('upload_max_filesize', '10M');

// Set own error & exception handlers
set_error_handler('FTException::errorHandler');
set_exception_handler('FTException::exceptionHandler');
register_shutdown_function('FTException::shutdownHandler');

// Set timezone
date_default_timezone_set("Europe/Kiev");

// Set locale
setlocale(LC_ALL, 'ru_RU.UTF-8', 'Russian_Russia.65001', 'UTF-8', 'ru_RU', 'russian');
