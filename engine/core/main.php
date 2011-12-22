<?php

/**
 * Engine starts here!
 */

// Start output buffering
ob_start();

// Start session
session_start();

// Set engine is started
define('ENGINE_IS_STARTED', 'y');

// Include all files need
require_once dirname(__FILE__) . '/include.php';

// Prepare engine before start
FTCore::loadFile(INC_PATH, 'prepare.inc');

// Create base controller (just for existance)
$base = MvcFactory::create('base', 'controller');

// Create front controller
$front = MvcFactory::create('front', 'controller');

// Create initial request & response
$request = new ActionRequest(NULL);
$response = new ActionResponse();

// Run application!
$front->run($request, $response);

// Show debug info
if ($engineConfig['system']['is_debug'])
{
	$base->timeProfiler->showElapsedTimeStyled();
	$request->dataMvc->showResult();
}

// Send buffer and turn off output buffering
ob_end_flush();
