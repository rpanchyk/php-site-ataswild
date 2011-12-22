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

	private $data; // result data from model-execute()

	public function __construct($args = NULL)
	{
		try
		{
			global $engineConfig;

			$this->data = array();
			$this->config = array();

			// Init time profiler and start it
			if (isset($engineConfig['system']['is_debug']) && $engineConfig['system']['is_debug'] === TRUE)
				$this->timeProfiler = new FTTimeProfiler(TRUE);

			// Init model
			if (isset($args[ParamsMvc::CUSTOM_MODEL]))
				$this->model = MvcFactory::create($args[ParamsMvc::CUSTOM_MODEL], 'model');
			elseif (!isset($args[ParamsMvc::NO_MODEL]) || !$args[ParamsMvc::NO_MODEL])
				$this->model = MvcFactory::create(strtolower(str_replace('Controller', '', get_class($this))), 'model');

			// Init view
			if (isset($args[ParamsMvc::CUSTOM_VIEW]))
				$this->view = MvcFactory::create($args[ParamsMvc::CUSTOM_VIEW], 'view');
			elseif (!isset($args[ParamsMvc::NO_VIEW]) || !$args[ParamsMvc::NO_VIEW])
				$this->view = MvcFactory::create(strtolower(str_replace('Controller', '', get_class($this))), 'view');
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

			return $this->data;
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}
}
