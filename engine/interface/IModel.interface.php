<?php
require_once dirname(__FILE__) . '/../inc/cde.inc.php';

/**
 * Interface for models
 */
interface IModel
{
	function execute(ActionRequest & $request, ActionResponse & $response, IController & $controller);
}
