<?php

/**
 * Engine starts here!
 */

try
{
	// Start output buffering
	@ob_start();

	// Start session
	@session_start();

	// Set engine started
	if (!defined('ENGINE_IS_STARTED'))
		define('ENGINE_IS_STARTED', 'y');

	// Include all files need
	require_once dirname(__FILE__) . '/include.php';

	if (!FTStringUtils::startsWith($_SERVER['REQUEST_URI'], '/admin/'))
	{
		// Create front controller & run application!
		$front = MvcFactory::create('front', ParamsMvc::ENTITY_CONTROLLER);
		$front->run($request, $response);

		// Show debug info
		if ($engineConfig['system']['is_debug'])
		{
			$base->timeProfiler->showElapsedTimeStyled();
			$request->dataMvc->showResult();
		}

		// Send buffer and turn off output buffering
		@ob_end_flush();
	}
}
catch (Exception $ex)
{
	FTException::throwEx($ex);
}
