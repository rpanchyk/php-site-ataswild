<?php
require_once dirname(__FILE__) . '/../../inc/cde.inc.php';

class StaticModel extends BaseModel
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
				//echo '--- merged!!!!';
			}

			$params[ParamsSql::LIMIT] = '1';
			$data = $request->db->get($params);

			//echo '$params:<pre>'; print_r($params); echo '</pre>';
			//echo '<pre>'; print_r($data); echo '</pre>';

			if (!FTArrayUtils::checkData($data))
			{
				FTException::saveEx(new Exception('No data for entity: ' . $params[ParamsSql::TABLE] . ' with alias: ' . $request->params[Params::ALIAS]));
				return $data;
			}

			if (FTStringUtils::startsWith($request->params[Params::ALIAS], 'menu') || FTStringUtils::startsWith($request->params[Params::ALIAS], 'footer'))
			{
				$controller = MvcFactory::create('container', ParamsMvc::ENTITY_CONTROLLER);
				$reqSections = new ActionRequest($request);
				$reqSections->params[Params::OPERATION_NAME] = Params::OPERATION_GET;
				$reqSections->params[ParamsMvc::IS_NOT_RENDER] = TRUE;
				$reqSections->params[ParamsSql::RESTRICTION] = 'is_section=1 AND is_active=1';
				$dataSections = $controller->run($reqSections, $response);

				//echo '<pre>'; print_r($dataSections); echo '</pre>';
				foreach ($dataSections as $row)
				{
					$data[0]['active_menu_top_' . $row['alias']] = $request->dataMvc->getController() == $row['alias'] ? '3' : '';
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
