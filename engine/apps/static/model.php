<?php
require_once dirname(__FILE__) . '/../../inc/cde.inc.php';

/**
 * Model
 */
class StaticModel extends BaseModel
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
			
			if (FTStringUtils::startsWith($request->params[Params::ALIAS], 'menu') || FTStringUtils::startsWith($request->params[Params::ALIAS], 'footer'))
			{
				$controller = MvcFactory::create('container', 'controller');
				$reqSections = new ActionRequest($request);
				$reqSections->params[Params::OPERATION_NAME] = Params::OPERATION_GET;
				$reqSections->params[ParamsMvc::IS_NOT_RENDER] = TRUE;
				$reqSections->params[ParamsSql::RESTRICTION] = 'is_section=1 AND is_active=1';
				$dataSections = $controller->run($reqSections, $response);
				
				//echo '<pre>'; print_r($dataSections); echo '</pre>';
				foreach ($dataSections as $row)
				{
					$data[0]['active_menu_top_'.$row['alias']] = $request->dataMvc->getController() == $row['alias'] ? '3'  : '';
				}
			}

			return $data;
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}
}
