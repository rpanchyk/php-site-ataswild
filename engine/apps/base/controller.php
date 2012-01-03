<?php
require_once dirname(__FILE__) . '/../../inc/cde.inc.php';

/**
 * Base controller for all controllers
 */
class BaseController extends FTFireTrot implements IController
{
	public $timeProfiler;

	public $model;
	public $view;
	public $config;

	private $m_entityName;
	protected $m_data; // result data from model-execute()

	public function __construct($args = NULL)
	{
		try
		{
			global $engineConfig;

			// Init time profiler and start it
			if (isset($engineConfig['system']['is_debug']) && $engineConfig['system']['is_debug'] === TRUE)
				$this->timeProfiler = new FTTimeProfiler(TRUE);

			// Init vars
			$this->m_data = array();
			$this->config = array();
			$this->m_entityName = FTStringUtils::trimEnd(get_class($this), ucfirst(ParamsMvc::ENTITY_CONTROLLER));

			// Set model
			$this->model = $this->getModel($args);

			// Set view
			$this->view = $this->getView($args);
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}

	public function run(ActionRequest & $request, ActionResponse & $response)
	{
		try
		{
			if (isset($request->params[ParamsMvc::MVC_MODEL]))
				$this->model = new $$request->params[ParamsMvc::MVC_MODEL];

			if (isset($request->params[ParamsMvc::MVC_VIEW]))
				$this->view = new $$request->params[ParamsMvc::MVC_VIEW];

			// Get config
			if (isset($this->model) && !FTArrayUtils::checkData($this->config))
			{
				$reqConfig = new ActionRequest($request);
				$reqConfig->params[Params::OPERATION_NAME] = Params::OPERATION_GET_CONFIG;
				$this->config = $this->model->execute($reqConfig, $response, $this);
			}

			if (!is_null($this->model) && !@$request->params[ParamsMvc::IS_NOT_EXECUTE])
			{
				// Get data
				$req = new ActionRequest($request);
				$req->params[Params::OPERATION_NAME] = isset($request->params[Params::OPERATION_NAME]) ? $request->params[Params::OPERATION_NAME] : Params::OPERATION_GET;
				$this->m_data = $this->model->execute($req, $response, $this);

				// Render content to concrete data type
				if (!@$request->params[ParamsMvc::IS_NOT_RENDER])
				{
					$methodName = 'as' . ucfirst($request->dataMvc->getFormatter());
					FTException::throwOnTrue(!is_callable(array($this, $methodName)), 'Not implemented method: ' . $methodName);

					$this->m_data = $this->$methodName();
				}
			}

			return $this->m_data;
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}

	private function getModel($args)
	{
		try
		{
			$modelName = $this->m_entityName;

			if (isset($args[ParamsMvc::CUSTOM_MODEL]))
			{
				// Custom model
				$modelName = $args[ParamsMvc::CUSTOM_MODEL];
			}
			elseif (isset($args[ParamsMvc::NO_MODEL]) && $args[ParamsMvc::NO_MODEL])
			{
				// No model
				$modelName = NULL;
			}

			return MvcFactory::create($modelName, ParamsMvc::ENTITY_MODEL);
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}
	private function getView($args)
	{
		try
		{
			$viewName = $this->m_entityName;

			if (isset($args[ParamsMvc::CUSTOM_VIEW]))
			{
				// Custom view
				$viewName = $args[ParamsMvc::CUSTOM_VIEW];
			}
			elseif (isset($args[ParamsMvc::NO_VIEW]) && $args[ParamsMvc::NO_VIEW])
			{
				// No view
				$viewName = NULL;
			}
			else
			{
				// Check view for this controller & if no - set default name
				$path = FTFileSystem::pathCombine(APP_PATH, $viewName, ParamsMvc::ENTITY_VIEW . '.php');
				if (!file_exists($path))
					$viewName = ParamsMvc::DEFAULT_VIEW_NAME;
			}

			return MvcFactory::create($viewName, ParamsMvc::ENTITY_VIEW);
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}
}
