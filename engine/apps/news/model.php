<?php
require_once dirname(__FILE__) . '/../../inc/cde.inc.php';

class NewsModel extends BaseModel
{
	protected function opGetByAlias(ActionRequest & $request, ActionResponse & $response)
	{
		try
		{
			FTException::throwOnTrue(@empty($request->params[Params::ALIAS]), 'No ' . Params::ALIAS);

			// Get data
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

	protected function opAdd(ActionRequest & $request, ActionResponse & $response)
	{
		try
		{
			if (@$request->dataWeb->request['called_from_operation'] == 'new')
			{
				$request->params[ParamsSql::CUSTOM_TABLE_NAME] = $this->m_Controller->config[ParamsConfig::OBJECT_ATTACH_ENTITY];
				$request->params[Params::DATA]['date_create'] = date('Y-m-d H:i:s', time());
			}
			return parent::opAdd($request, $response);
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}

	protected function opUpdate(ActionRequest & $request, ActionResponse & $response)
	{
		try
		{
			if (@$request->dataWeb->request['called_from_operation'] == 'get_item_by_id')
			{
				$request->params[ParamsSql::CUSTOM_TABLE_NAME] = $this->m_Controller->config[ParamsConfig::OBJECT_ATTACH_ENTITY];
				$request->params[Params::DATA]['date_modify'] = date('Y-m-d H:i:s', time());
			}
			return parent::opUpdate($request, $response);
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}

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
			// Get base data
			$data = $this->opGetByAlias($request, $response);

			// Modify data
			$dataNews = array();
			for ($i = 0; $i < count($data); $i++)
			{
				$req = new ActionRequest($request, FALSE);
				$req->params[ParamsSql::CUSTOM_TABLE_NAME] = $this->m_Controller->config[ParamsConfig::OBJECT_ATTACH_ENTITY];
				$req->params[ParamsSql::RESTRICTION] = '_parent_id=:_parent_id AND is_active=1';
				$req->params[ParamsSql::RESTRICTION_DATA][':_parent_id'] = $data[$i]['_id'];
				$req->params[ParamsSql::ORDER_BY] = isset($request->params[ParamsSql::ORDER_BY]) ? $request->params[ParamsSql::ORDER_BY] : NULL;
				$data[$i][ParamsConfig::OBJECT_ATTACH_ENTITY] = parent::opGet($req, $response);
			}

			// Merge data
			return $data;
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}

	protected function opGetById(ActionRequest & $request, ActionResponse & $response)
	{
		try
		{
			// Get base data
			$dataBase = parent::opGetById($request, $response);

			// Get news data
			$req = new ActionRequest($request);
			$req->params[ParamsSql::CUSTOM_TABLE_NAME] = $this->m_Controller->config['object_attach_entity'];
			$req->params[ParamsSql::RESTRICTION] = '_parent_id=:_parent_id';
			$req->params[ParamsSql::RESTRICTION_DATA][':_parent_id'] = $request->params[Params::ID];
			$dataNews = parent::opGet($req, $response);

			return array_merge($dataBase, $dataNews);
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}

	protected function opGetItemById(ActionRequest & $request, ActionResponse & $response)
	{
		try
		{
			// Get base data
			$dataBase = parent::opGetById($request, $response);

			// Get news data
			$req = new ActionRequest($request);
			$req->params[ParamsSql::CUSTOM_TABLE_NAME] = $this->m_Controller->config['object_attach_entity'];
			$req->params[ParamsSql::RESTRICTION] = '_id=:_id';
			$req->params[ParamsSql::RESTRICTION_DATA][':_id'] = $request->params[Params::ID];
			$dataBase[0][ParamsConfig::OBJECT_ATTACH_ENTITY] = parent::opGet($req, $response);

			return $dataBase; //array_merge($dataBase, $dataNews);
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}

	protected function opGetConfig(ActionRequest & $request, ActionResponse & $response)
	{
		try
		{
			$config = parent::opGetConfig($request, $response);

			$editorID = $config[ParamsConfig::OBJECT_ATTACH_ENTITY];

			// Get table metadata
			$dataTableMeta = $request->db->getTableMeta('t' . ucfirst($config[ParamsConfig::OBJECT_ATTACH_ENTITY]));
			if (!FTArrayUtils::checkData($dataTableMeta))
				throw new Exception('No metadata for app: ' . $appName);

			// Add metadata to editor fields
			foreach ($config['editor'][$editorID]['fields'] as $k => $v)
				if (key_exists($k, $dataTableMeta))
					$config['editor'][$editorID]['fields'][$k] = array_merge($config['editor'][$editorID]['fields'][$k], $dataTableMeta[$k]);

			return $config;
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}
}
