<?php

/** This file is part of KCFinder project
 *
 *      @desc Browser calling script
 *   @package KCFinder
 *   @version 2.51
 *    @author Pavel Tzonkov <pavelc@users.sourceforge.net>
 * @copyright 2010, 2011 KCFinder Project
 *   @license http://www.opensource.org/licenses/gpl-2.0.php GPLv2
 *   @license http://www.opensource.org/licenses/lgpl-2.1.php LGPLv2
 *      @link http://kcfinder.sunhater.com
 */

require "core/autoload.php";
$browser = new browser();

// Check auth
try
{
	// Set engine is started
	if (!defined('ENGINE_IS_STARTED'))
		define('ENGINE_IS_STARTED', 'y');

	// Include all files need
	require_once dirname(__FILE__) . '/../../../../engine/core/include.php';

	// Run handler
	$handler = MvcFactory::create('handler', ParamsMvc::ENTITY_CONTROLLER);
	$reqHandler = new ActionRequest($request, TRUE, TRUE, FALSE);
	$request->dataWeb->request['object_app'] = 'user';
	$request->dataWeb->request['object_operation'] = 'get_session';
	$handler->run($reqHandler, $response);
}
catch (Exception $ex)
{
	FTException::throwEx($ex);
}

$browser->action();
?>