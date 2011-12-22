<?php
require_once dirname(__FILE__) . '/../inc/cde.inc.php';

/**
 * Request
 */
class ActionRequest extends FTFireTrot
{
	public $db;
	public $dataWeb;
	public $dataMvc;
	//public $user;

	public $params;

	public function __construct($request, $bIsShiftParams = TRUE)
	{
		try
		{
			global $engineConfig;

			// Set up database connector
			$this->db = $request != NULL ? $request->db : new DatabaseDriver($engineConfig['database']['type'], $engineConfig['database']['host'], $engineConfig['database']['port'], $engineConfig['database']['name'], $engineConfig['database']['username'], $engineConfig['database']['password'], array(), $engineConfig['database']['cache_dir'], $engineConfig['database']['cache_ttl_default']);

			// Get web data
			$this->dataWeb = $request != NULL ? $request->dataWeb : WebData::getInstance($engineConfig['web_data']['super_globals'], $this->db);

			// Get mvc data
			$this->dataMvc = $request != NULL ? $request->dataMvc : MvcData::getInstance(@$this->dataWeb->request['url'], $engineConfig['mvc_data']['lang_default'], 'html', $engineConfig['mvc_data']['app_alias_default'], $engineConfig['mvc_data']['app_operation_default'], $engineConfig['mvc_data']['langs'], $engineConfig['mvc_data']['formatters'], array(), $this->db); //, $engineConfig['mvc_data']['allowed_webaccess_apps']);

			// Shift params
			if ($bIsShiftParams && isset($request->params))
				$this->params = $request->params;
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}

	static public function getRequestParamValue(ActionRequest $request, $paramName, $regexPattern = '', $bIsCanBeEmptyOrNull = FALSE)
	{
		try
		{
			if (!isset($request->dataWeb->request[$paramName]) || empty($request->dataWeb->request[$paramName]))
			{
				if (!$bIsCanBeEmptyOrNull)
					throw new Exception('No request param: ' . $paramName);
				else
					return @$request->dataWeb->request[$paramName];
			}

			$paramValue = $request->dataWeb->request[$paramName];

			if (!empty($regexPattern) && !preg_match($regexPattern, $paramValue))
				throw new Exception('Request error in param ' . $paramName . ' value: "' . $paramValue . '"');

			return $paramValue;
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}
}
