<?php
require_once dirname(__FILE__) . '/../../inc/cde.inc.php';

/**
 * Controller for including other content
 */
class ContainerController extends BaseController implements IHtmlable
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
			// Check data
			FTException::throwOnTrue(!FTArrayUtils::checkData($this->data, 0), 'No controller data in ' . get_class($this));

			$strResult = '';

			foreach ($this->data as $row)
			{

				if (!isset($row['markup']) || !FTArrayUtils::checkData(@$row[ParamsMvc::MODEL_RESULT_DATA]))
				{
					FTException::saveEx(new Exception('No ' . ParamsMvc::MODEL_RESULT_DATA . ' or empty markup'));
					continue;
				}

				// Render (!)
				$strResult .= $this->view->renderText($row['markup'], $row[ParamsMvc::MODEL_RESULT_DATA]);
			}

			return $strResult;
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}
}
