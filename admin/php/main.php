<?php

try
{
	// Start output buffering
	ob_start();

	// Start session
	session_start();

	// Set engine is started
	define('ENGINE_IS_STARTED', 'y');

	// Include all files need
	require_once dirname(__FILE__) . '/../../engine/core/include.php';
	//require_once ADMIN_PATH . '/php/inc/auth.inc.php';
	require_once ADMIN_PATH . '/php/inc/tree.inc.php';
	require_once ADMIN_PATH . '/php/inc/langs.inc.php';

	// Prepare dirs
	FTCore::createDirs(array(UPLOAD_PATH, VAR_PATH, LOGS_PATH, CACHE_PATH, CACHE_QUERY_PATH, CACHE_CONTENT_PATH));

	// Create base controller (just for existance)
	$base = MvcFactory::create('base', ParamsMvc::ENTITY_CONTROLLER);

	// Create initial request & response
	$request = new ActionRequest(NULL);
	$response = new ActionResponse();

	// Form-request handler
	$handlerPath = '/admin/php/handler.php';

	// Check auth
	$user = MvcFactory::create('user', ParamsMvc::ENTITY_CONTROLLER);
	$reqAuth = new ActionRequest($request);
	$reqAuth->params[Params::OPERATION_NAME] = Params::OPERATION_USER_GET_SESSION;
	$dataAuth = $user->run($reqAuth, $response);
	$authUser = FTArrayUtils::checkData($dataAuth) ? $dataAuth[0] : NULL;
}
catch (Exception $ex)
{
	FTException::throwEx($ex);
}
