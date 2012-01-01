<?php
require_once dirname(__FILE__) . '/../../inc/cde.inc.php';

class CommentsModel extends BaseModel
{
	protected function opGetByAlias(ActionRequest & $request, ActionResponse & $response)
	{
		try
		{
			FTException::throwOnTrue(!isset($request->params[Params::ALIAS]) || empty($request->params[Params::ALIAS]), 'No ' . Params::ALIAS);

			// Get container
			$params = array();
			$params[ParamsSql::TABLE] = $this->m_entityName;
			$params[ParamsSql::RESTRICTION] = 'alias=:alias AND is_active=1';
			$params[ParamsSql::RESTRICTION_DATA] = array(':alias' => $request->params[Params::ALIAS]);
			$params[ParamsSql::LIMIT] = '1';
			$data = $request->db->get($params);

			if (!FTArrayUtils::checkData($data))
			{
				FTException::saveEx(new Exception('No data for entity: ' . $params[ParamsSql::TABLE] . ' with alias: ' . $request->params[Params::ALIAS]));
				return $data;
			}

			return $data;
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}
}
