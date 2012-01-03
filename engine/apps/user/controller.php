<?php
require_once dirname(__FILE__) . '/../../inc/cde.inc.php';

class UserController extends BaseController
{
	public function run(ActionRequest & $request, ActionResponse & $response)
	{
		try
		{
			$reqBase = new ActionRequest($request);
			$reqBase->params[ParamsMvc::IS_NOT_RENDER] = TRUE;
			parent::run($reqBase, $response);

			return $this->m_data;
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}
}
