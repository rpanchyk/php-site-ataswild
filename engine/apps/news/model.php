<?php
require_once dirname(__FILE__) . '/../../inc/cde.inc.php';

class NewsModel extends BaseModel
{
	protected function opGet(ActionRequest & $request, ActionResponse & $response)
	{
		try
		{
			if (isset($request->params[Params::ID]))
				return $this->opGetById($request, $response);

			return $this->opGetList($request, $response);
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}

	protected function opGetList(ActionRequest & $request, ActionResponse & $response)
	{
		try
		{
			throw new Exception('Not implemented');
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}

//	protected function opGetById(ActionRequest & $request, ActionResponse & $response)
//	{
//		try
//		{
//			FTException::throwOnTrue(!isset($request->params[Params::ID]), 'No ' . Params::ID);
//
//			$req = new ActionRequest($request);
//			$req->params[ParamsSql::RESTRICTION] = 'id=:id';
//			$req->params[ParamsSql::RESTRICTION_DATA][':id'] = $request->params[Params::ID];
//			return parent::opGet($req, $response);
//		}
//		catch (Exception $ex)
//		{
//			throw $ex;
//		}
//	}
}
