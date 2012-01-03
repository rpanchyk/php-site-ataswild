<?php
require_once dirname(__FILE__) . '/../../inc/cde.inc.php';

/**
 * Static controller
 */
class StaticController extends BaseController implements IHtmlable
{
	public function run(ActionRequest & $request, ActionResponse & $response)
	{
		try
		{
			parent::run($request, $response);

			return $this->m_data;
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
			if (!FTArrayUtils::checkData(@$this->m_data[0]))
				return '';

			// Get template
			$template = isset($request->params['template']) ? $request->params['template'] : $this->m_data[0]['template'];

			// Get output
			return $this->view->render($template, $this->m_data[0], FALSE);
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}
}
