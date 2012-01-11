<?php
require_once dirname(__FILE__) . '/../../inc/cde.inc.php';

/**
 * Front controller - redirect requests to other controllers
 */
class FrontController extends BaseController //implements IHtmlable, IXmlable
{
	public function __construct($args = NULL)
	{
		try
		{
			// Get arguments
			$args = FTArrayUtils::checkData($args, 0) ? $args : array();

			// Remove model
			$args[ParamsMvc::NO_MODEL] = TRUE;

			parent::__construct($args);
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
			parent::run($request, $response);

			switch ($request->dataMvc->getFormatter())
			{
				case 'html':
					$this->processHtmlContent($request, $response);
					break;
				case 'xml':
					$this->processXmlContent($request, $response);
					break;
				default:
					throw new Exception('Unhandled formatter');
					break;
			}

			return $this->m_data;
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}

	protected function processHtmlContent(ActionRequest & $request, ActionResponse & $response)
	{
		try
		{
			$ctrl = MvcFactory::create('container', ParamsMvc::ENTITY_CONTROLLER);

			$req = new ActionRequest($request);
			$req->params[Params::OPERATION_NAME] = Params::OPERATION_GET_BY_ALIAS;
			$req->params[Params::ALIAS] = $request->dataMvc->getController();
			$data = $ctrl->run($req, $response);

			$ctrl->view->render('index', array('content' => $data), TRUE);
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}
	protected function processXmlContent(ActionRequest & $request, ActionResponse & $response)
	{
		try
		{
			throw new Exception('Not implemented: processXmlContent');
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}
}
