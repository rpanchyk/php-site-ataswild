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

	/**
	 * Result data from model-execute()
	 * @var Object
	 */
	protected $m_data;

	/**
	 * Constructor for base controller
	 * @param Array $args - input params (default: NULL)
	 */
	public function __construct($args = NULL)
	{
		try
		{
			global $request, $response, $engineConfig;

			// Init and start time profiler
			if ($engineConfig['system']['is_debug'] === TRUE)
				$this->timeProfiler = new FTTimeProfiler(TRUE);

			// Init vars
			$this->m_data = array();
			$this->m_entityName = FTStringUtils::trimEnd(get_class($this), ucfirst(ParamsMvc::ENTITY_CONTROLLER));

			// Set model & view
			$this->model = $this->getModel($args);
			$this->view = $this->getView($args);

			// Get config
			if (!isset($this->config))
			{
				if (!is_null($this->model))
				{
					$reqConfig = new ActionRequest($request);
					$reqConfig->params[Params::OPERATION_NAME] = Params::OPERATION_GET_CONFIG;
					$reqConfig->params[ParamsMvc::APP_NAME] = $this->m_entityName;
					$this->config = $this->model->execute($reqConfig, $response, $this);
				}
				else
				{
					$pathConfig = FTFileSystem::pathCombine(APP_PATH, $this->m_entityName, 'app.' . EntityFileType::CONFIG_TYPE . '.php');
					if (file_exists($pathConfig))
						$this->config = require $pathConfig;
				}
			}
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

			// Execute model (!)
			if (!is_null($this->model) && !@$request->params[ParamsMvc::IS_NOT_EXECUTE])
			{
				$req = new ActionRequest($request);
				$req->params[Params::OPERATION_NAME] = isset($request->params[Params::OPERATION_NAME]) ? $request->params[Params::OPERATION_NAME] : Params::OPERATION_GET;
				$this->m_data = $this->model->execute($req, $response, $this);
			}

			return $this->m_data;
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}

	/**
	 * Creates model
	 * @param Array $args
	 */
	private function getModel($args)
	{
		try
		{
			// No model
			if (@$args[ParamsMvc::NO_MODEL])
				return NULL;

			// Custom model
			if (isset($args[ParamsMvc::CUSTOM_MODEL]))
				$name = $args[ParamsMvc::CUSTOM_MODEL];

			// Check model for this controller & if no - set default one
			if (!isset($name))
			{
				$path = FTFileSystem::pathCombine(APP_PATH, $this->m_entityName, ParamsMvc::ENTITY_MODEL . '.php');
				$name = file_exists($path) ? $this->m_entityName : ParamsMvc::DEFAULT_MODEL_NAME;
			}

			return MvcFactory::create($name, ParamsMvc::ENTITY_MODEL);
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}
	/**
	 * Creates view
	 * @param Array $args
	 */
	private function getView($args)
	{
		try
		{
			// No view
			if (@$args[ParamsMvc::NO_VIEW])
				return NULL;

			// Custom view
			if (isset($args[ParamsMvc::CUSTOM_VIEW]))
				$name = $args[ParamsMvc::CUSTOM_VIEW];

			// Check view for this controller & if no - set default one
			if (!isset($name))
			{
				$path = FTFileSystem::pathCombine(APP_PATH, $this->m_entityName, ParamsMvc::ENTITY_VIEW . '.php');
				$name = file_exists($path) ? $this->m_entityName : ParamsMvc::DEFAULT_VIEW_NAME;
			}

			return MvcFactory::create($name, ParamsMvc::ENTITY_VIEW);
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}
}
