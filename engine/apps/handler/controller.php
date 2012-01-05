<?php
require_once dirname(__FILE__) . '/../../inc/cde.inc.php';

/**
 * Handler controller
 */
class HandlerController extends BaseController implements IHtmlable, IJsonable
{
	private $m_errorEdge = '<!-- error -->';

	public function run(ActionRequest & $request, ActionResponse & $response)
	{
		try
		{
			$reqBase = new ActionRequest($request);
			$reqBase->params['skip_table_metadata'] = TRUE; // App has no db table
			$reqBase->params[ParamsMvc::IS_NOT_EXECUTE] = TRUE; // Suspend execute default operation
			parent::run($reqBase, $response);

			// Get application
			$objectApp = @$request->dataWeb->request['object_app'];
			FTException::throwOnTrue(empty($objectApp), 'No app');

			// Get object operation
			$objectOperation = @$request->dataWeb->request['object_operation'];
			FTException::throwOnTrue(empty($objectOperation), 'No object operation');

			// Execute
			$req = new ActionRequest($request);
			$req->params = array_merge(is_array(@$req->params) ? $req->params : array(), $request->dataWeb->request);
			$req->params[Params::OPERATION_NAME] = strtolower($objectApp) . '_' . strtolower($objectOperation);
			$this->m_data = $this->model->execute($req, $response, $this);

			// Check error
			FTException::throwOnTrue(!is_array($this->m_data) && isset($this->config[$this->m_data]), @$this->config[$this->m_data]);

			// Show result
			if (!@$request->params[ParamsMvc::IS_NOT_RENDER])
				$this->asHtml();

			return $this->m_data;
		}
		catch (Exception $ex)
		{
			if (!@$request->params[ParamsMvc::IS_NOT_RENDER])
			{
				global $engineConfig;

				// Show error
				echo $this->m_errorEdge . FTException::toStringForWeb($ex, $engineConfig['system']['is_debug']) . $this->m_errorEdge;
			}
			else
				throw $ex;
		}
	}

	/**
	 * Return error edge
	 * @return String
	 */
	public function getErrorEdge()
	{
		return $this->m_errorEdge;
	}

	public function asHtml()
	{
		try
		{
			if (!is_array($this->m_data))
			{
				echo $this->m_data;
			}
			else
			{
				echo 'asHtml() array:<pre>';
				print_r($this->m_data);
				echo '</pre>';
			}
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}
	public function asJson()
	{
		try
		{
			return json_encode($this->m_data);
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}
}
