<?php
require_once dirname(__FILE__) . '/../../inc/cde.inc.php';

class GalleryModel extends BaseModel
{
	protected function opGetByAlias(ActionRequest & $request, ActionResponse & $response)
	{
		try
		{
			FTException::throwOnTrue(@empty($request->params[Params::ALIAS]), 'No ' . Params::ALIAS);

			// Get container
			$params = array();
			$params[ParamsSql::TABLE] = $this->m_entityName;
			$params[ParamsSql::RESTRICTION] = 'alias=:alias AND is_active=1';
			$params[ParamsSql::RESTRICTION_DATA] = array(':alias' => $request->params[Params::ALIAS]);

			if (!@empty($request->params[ParamsSql::RESTRICTION]) && FTArrayUtils::checkData(@$request->params[ParamsSql::RESTRICTION_DATA]))
			{
				$params[ParamsSql::RESTRICTION] .= ' AND ' . $request->params[ParamsSql::RESTRICTION];
				$params[ParamsSql::RESTRICTION_DATA] = array_merge($params[ParamsSql::RESTRICTION_DATA], $request->params[ParamsSql::RESTRICTION_DATA]);
			}

			$params[ParamsSql::LIMIT] = '1';
			$data = $request->db->get($params);

			if (!FTArrayUtils::checkData($data))
				FTException::saveEx(new Exception('No data for entity: ' . $params[ParamsSql::TABLE] . ' with alias: ' . $request->params[Params::ALIAS]));

			return $data;
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}
}
