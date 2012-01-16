<?php
require_once dirname(__FILE__) . '/../../inc/cde.inc.php';

/**
 * Base model - provides basic operations
 */
class BaseModel extends FTFireTrot implements IModel
{
	protected $m_entityName;
	protected $m_Controller;

	public function __construct()
	{
		try
		{
			$this->m_entityName = FTStringUtils::trimEnd(get_class($this), ucfirst(ParamsMvc::ENTITY_MODEL));
			$this->m_Controller = NULL;
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}

	/**
	 * @see IModel::execute()
	 */
	public function execute(ActionRequest & $request, ActionResponse & $response, IController & $controller)
	{
		try
		{
			// Get operation name
			FTException::throwOnTrue(empty($request->params[Params::OPERATION_NAME]), 'Operation not setted');

			// Set controller
			$this->m_Controller = $controller;

			// Check for operation exists
			$reqCheckOp = new ActionRequest($request);
			$reqCheckOp->params[Params::OPERATION_CHECK] = $request->params[Params::OPERATION_NAME];
			FTException::throwOnTrue(!$this->opIsCallable($reqCheckOp, $response, $this->m_Controller), 'Operation "' . $request->params[Params::OPERATION_NAME] . '" not found');

			// Do execute (!)
			$opNameInModel = $this->getOperationName($request->params[Params::OPERATION_NAME]);
			return $this->m_Controller->model->$opNameInModel($request, $response);
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}

	/**
	 * Get all operations
	 */
	protected function opGetOperations(ActionRequest & $request, ActionResponse & $response)
	{
		try
		{
			$aOperations = get_class_methods(get_class($this->m_Controller->model));
			$aResult = array();
			foreach ($aOperations as $value)
			{
				if (@$request->params[Params::OPERATION_FILTER] != $value)
					continue;

				$aResult[] = $value;
			}
			return $aResult;
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}

	/**
	 * Check for operation exists
	 */
	protected function opIsCallable(ActionRequest & $request, ActionResponse & $response)
	{
		try
		{
			FTException::throwOnTrue(empty($request->params[Params::OPERATION_CHECK]), 'Input parameters not setted');

			$reqCheck = new ActionRequest($request);
			$reqCheck->params[Params::OPERATION_FILTER] = $this->getOperationName($request->params[Params::OPERATION_CHECK]);
			return count($this->opGetOperations($reqCheck, $response)) > 0;
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}

	/**
	 * Get app config
	 * @deprecated Use Controller->config[...] instead
	 */
	protected function opGetConfig(ActionRequest & $request, ActionResponse & $response)
	{
		try
		{
			// Get application name
			$appName = isset($request->params[ParamsMvc::APP_NAME]) ? $request->params[ParamsMvc::APP_NAME] : $this->m_entityName;
			$appName = strtolower($appName);

			// Check file
			$filePath = FTFileSystem::pathCombine(APP_PATH, $appName, 'app.config.php');
			if (!file_exists($filePath))
				//throw new Exception('No file: ' . $filePath);
				return array();

			// Get config for app
			$config = require $filePath;
			if (!FTArrayUtils::checkData($config, 0))
				throw new Exception('No app config');

			// Get editor id
			$editorID = isset($request->params[ParamsConfig::EDITOR_ID]) ? $request->params[ParamsConfig::EDITOR_ID] : ParamsConfig::EDITOR_DEFAULT;

			// Get editor data
			if (in_array($appName, array('base', 'front', 'handler')))
				$skip_editor = TRUE;
			elseif (FTArrayUtils::checkData(@$config['editor'][$editorID]))
				$configEditor = $config['editor'][$editorID];
			elseif (FTArrayUtils::checkData(@$config[$appName]['editor'][$editorID]))
				$configEditor = $config[$appName]['editor'][$editorID];
			elseif (isset($request->params[ParamsConfig::DATA_OBJECT]))
				$configEditor = @$config[$request->params[ParamsConfig::DATA_OBJECT]]['editor'][$editorID];
			else
				throw new Exception('No editor found for app: ' . $appName);
				//return $config;

			// Check edtor fields
			FTException::throwOnTrue(!FTArrayUtils::checkData(@$configEditor['fields']) && !@$skip_editor, 'No editor fields: ' . $appName . '.' . $editorID);

			// Replace editor (!)
			$config['editor'][$editorID] = @$configEditor;

			if (@$request->params['skip_table_metadata'] || @$skip_editor)
				return $config;

			// Get table metadata
			$dataTableMeta = $request->db->getTableMeta('t' . ucfirst($appName));
			if (!FTArrayUtils::checkData($dataTableMeta))
				throw new Exception('No metadata for app: ' . $appName);

			// Add metadata to editor fields
			foreach ($configEditor['fields'] as $k => $v)
				if (key_exists($k, $dataTableMeta))
					$config['editor'][$editorID]['fields'][$k] = array_merge($configEditor['fields'][$k], $dataTableMeta[$k]);

			return $config;
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}

	/**
	 * CRUD: get
	 */
	protected function opGet(ActionRequest & $request, ActionResponse & $response)
	{
		try
		{
			$params = array();
			$params[ParamsSql::TABLE] = $this->m_entityName;
			$params[ParamsSql::RESTRICTION] = !@empty($request->params[ParamsSql::RESTRICTION]) ? $request->params[ParamsSql::RESTRICTION] : NULL;
			$params[ParamsSql::RESTRICTION_DATA] = !@empty($request->params[ParamsSql::RESTRICTION_DATA]) ? $request->params[ParamsSql::RESTRICTION_DATA] : NULL;
			$params[ParamsSql::GROUP_BY] = isset($request->params[ParamsSql::GROUP_BY]) ? $request->params[ParamsSql::GROUP_BY] : NULL;
			$params[ParamsSql::HAVING] = isset($request->params[ParamsSql::HAVING]) ? $request->params[ParamsSql::HAVING] : NULL;
			$params[ParamsSql::ORDER_BY] = isset($request->params[ParamsSql::ORDER_BY]) ? $request->params[ParamsSql::ORDER_BY] : NULL;
			$params[ParamsSql::LIMIT] = isset($request->params[ParamsSql::LIMIT]) ? $request->params[ParamsSql::LIMIT] : NULL;
			return $request->db->get($params);
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}

	/**
	 * CRUD: add
	 */
	protected function opAdd(ActionRequest & $request, ActionResponse & $response)
	{
		try
		{
			$params = array();
			$params[ParamsSql::TABLE] = $this->m_entityName;
			return $request->db->add($params, @$request->params[Params::DATA]);
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}

	/**
	 * CRUD: update
	 */
	protected function opUpdate(ActionRequest & $request, ActionResponse & $response)
	{
		try
		{
			$params = array();
			$params[ParamsSql::TABLE] = $this->m_entityName;
			$params[ParamsSql::RESTRICTION] = !@empty($request->params[ParamsSql::RESTRICTION]) ? $request->params[ParamsSql::RESTRICTION] : NULL;
			$params[ParamsSql::RESTRICTION_DATA] = !@empty($request->params[ParamsSql::RESTRICTION_DATA]) ? $request->params[ParamsSql::RESTRICTION_DATA] : NULL;
			return $request->db->update($params, @$request->params[Params::DATA]);
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}

	/**
	 * CRUD: delete
	 */
	protected function opDelete(ActionRequest & $request, ActionResponse & $response)
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

	/**
	 * Get data by row ID
	 */
	protected function opGetById(ActionRequest & $request, ActionResponse & $response)
	{
		try
		{
			// Check params
			FTException::throwOnTrue(!isset($request->params[Params::ID]) || !intval($request->params[Params::ID]), 'No ' . Params::ID);

			$params = array();
			$params[ParamsSql::TABLE] = $this->m_entityName;
			$params[ParamsSql::RESTRICTION] = '_id=:_id';
			$params[ParamsSql::RESTRICTION_DATA] = array(':_id' => $request->params[Params::ID]);
			$params[ParamsSql::LIMIT] = '1';
			return $request->db->get($params);
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}

	/**
	 * Return operation name
	 * @param String $alias - operation alias
	 */
	protected function getOperationName($alias)
	{
		try
		{
			$name = 'op';

			$aParts = explode('_', $alias);

			foreach ($aParts as $value)
				$name .= ucfirst(strtolower($value));

			return $name;
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}
}
