<?php
require_once dirname(__FILE__) . '/../../inc/cde.inc.php';

/**
 * Controller for including other content
 */
class ContainerController extends BaseController //implements IHtmlable
{
	/*
	public function run(ActionRequest & $request, ActionResponse & $response)
	{
		try
		{
			parent::run($request, $response);
//echo '<pre>'; print_r($this->m_data); echo '</pre>';
			return $this->m_data;
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}
*/
	/*
	public function asHtml()
	{
		try
		{
			// Check data
			FTException::throwOnTrue(!FTArrayUtils::checkData($this->m_data, 0), 'No controller data in ' . get_class($this));

			$strResult = '';

			foreach ($this->m_data as $row)
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
	*/
}
