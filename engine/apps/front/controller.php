<?php
require_once dirname(__FILE__) . '/../../inc/cde.inc.php';

/**
 * Front controller - redirect requests to other controllers
 */
class FrontController extends BaseController
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
			$controller = MvcFactory::create('container', ParamsMvc::ENTITY_CONTROLLER);

			$req = new ActionRequest($request);
			$req->params[Params::OPERATION_NAME] = Params::OPERATION_GET_BY_ALIAS;
			$req->params[Params::ALIAS] = $request->dataMvc->getController();
			$strOutput = $controller->run($req, $response);

			$this->view->render('index', array('content' => $strOutput));
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
