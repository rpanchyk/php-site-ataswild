<?php

/**
 * Web request handler
 */

try
{
	// Start output buffering
	@ob_start();

	// Start session
	@session_start();

	// Set engine is started
	if (!defined('ENGINE_IS_STARTED'))
		define('ENGINE_IS_STARTED', 'y');

	// Include all files need
	require_once dirname(__FILE__) . '/engine/core/include.php';

	// Run handler
	$handler = MvcFactory::create('handler', ParamsMvc::ENTITY_CONTROLLER);
	$reqHandler = new ActionRequest($request);
	$handler->run($reqAuth, $response);

	// Send buffer and turn off output buffering
	@ob_end_flush();
}
catch (Exception $ex)
{
	FTException::throwEx($ex);
}
