<?php
require_once dirname(__FILE__) . '/../../inc/cde.inc.php';

class UserController extends BaseController
{
	public function run(ActionRequest & $request, ActionResponse & $response)
	{
		try
		{
			parent::run($request, $response);

			$req = new ActionRequest($request);
			$req->params[Params::OPERATION_NAME] = isset($request->params[Params::OPERATION_NAME]) ? $request->params[Params::OPERATION_NAME] : $request->dataMvc->getOperation();
			$this->data = $this->model->execute($req, $response, $this);

			return $this->data;
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}
}
