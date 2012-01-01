<?php
require_once dirname(__FILE__) . '/../../inc/cde.inc.php';

/**
 * Handler controller
 */
class HandlerController extends BaseController implements IHtmlable
{
	public function run(ActionRequest & $request, ActionResponse & $response)
	{
		try
		{
			parent::run($request, $response);

			// Get data
			$req = new ActionRequest($request);
			$req->params[Params::OPERATION_NAME] = isset($request->params[Params::OPERATION_NAME]) ? $request->params[Params::OPERATION_NAME] : Params::OPERATION_GET;
			$this->data = $this->model->execute($req, $response, $this);

			// Render content to concrete data type
			if (!@$request->params[ParamsMvc::IS_NOT_RENDER])
			{
				$methodName = 'as' . ucfirst($request->dataMvc->getFormatter());
				if (!is_callable(array($this, $methodName)))
					throw new Exception('Not implemented method: ' . $methodName);

				return $this->$methodName();
			}

			return $this->data;
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}

	public function asHtml()
	{
		try
		{
			if (!FTArrayUtils::checkData(@$this->data[0]))
				return '';

			// Get template
			$template = isset($request->params['template']) ? $request->params['template'] : $this->data[0]['template'];

			// Get output
			return $this->view->render($template, $this->data[0], FALSE);
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}
}
