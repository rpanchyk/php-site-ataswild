<?php
require_once dirname(__FILE__) . '/../inc/cde.inc.php';

/**
 * Interface for controllers
 */
interface IController
{
	public function run(ActionRequest & $request, ActionResponse & $response);
}
