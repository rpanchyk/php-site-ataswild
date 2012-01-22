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
	public $params;

	public function __construct(ActionRequest $request = NULL, $bIsShiftParams = TRUE, $isWebDataShiftGlobals = TRUE, $isWebDataRemoveGlobals = TRUE)
	{
		try
		{
			global $engineConfig;

			// Set up database connector
			$this->db = !is_null($request) ? $request->db : new DatabaseDriver($engineConfig['database']['type'], $engineConfig['database']['host'], $engineConfig['database']['port'], $engineConfig['database']['name'], $engineConfig['database']['username'], $engineConfig['database']['password'], array(), $engineConfig['database']['cache_dir'], $engineConfig['database']['cache_ttl']);

			// Set web data
			$this->dataWeb = !is_null($request) ? $request->dataWeb : WebData::getInstance($engineConfig['web_data']['super_globals'], $isWebDataShiftGlobals, $isWebDataRemoveGlobals);

			// Set mvc data
			$this->dataMvc = !is_null($request) ? $request->dataMvc : MvcData::getInstance(@$this->dataWeb->request['url'], $engineConfig['mvc_data']['lang_default'], 'html', $engineConfig['mvc_data']['app_alias_default'], $engineConfig['mvc_data']['app_operation_default'], $engineConfig['mvc_data']['langs'], $engineConfig['mvc_data']['formatters'], array(), $this->db); //, $engineConfig['mvc_data']['allowed_webaccess_apps']);

			// Shift params
			$this->params = !is_null($request) && $bIsShiftParams ? $request->params : array();
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
