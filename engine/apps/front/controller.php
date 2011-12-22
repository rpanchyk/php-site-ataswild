<?php
require_once dirname(__FILE__) . '/../../inc/cde.inc.php';

/**
 * Front controller
 */
class FrontController extends BaseController
{
	public function __construct($args = NULL)
	{
		$args = FTArrayUtils::checkData($args, 0) ? $args : array();
		$args[ParamsMvc::NO_MODEL] = TRUE;
		$args[ParamsMvc::CUSTOM_VIEW] = ParamsMvc::DEFAULT_VIEW_NAME;

		parent::__construct($args);
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
			$controller = MvcFactory::create('container', 'controller');

			$req = new ActionRequest($request);
			$req->params[Params::OPERATION_NAME] = 'get_by_alias';
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
			throw new Exception('Not implemented');
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}
}
