<?php
require_once dirname(__FILE__) . '/../../inc/cde.inc.php';

class ContainerView extends BaseView
{
	public function renderText($text, $data = array())
	{
		try
		{
			// Modify $data before render
			// All actions here, because data structure is known!
			foreach ($data as $dataKey => $dataValue)
			{
				if (FTArrayUtils::checkData(@$dataValue[0]))
				{
					// Data to modify
					$data4render = @$dataValue[0][ParamsMvc::MODEL_RESULT_DATA];
					if (FTArrayUtils::checkData($data4render))
					{
						// Get markup
						FTException::throwOnTrue(!FTArrayUtils::containsKeyCI('markup', $dataValue[0]), 'No markup');
						$strResult = $dataValue[0]['markup'];

						foreach ($data4render as $key => $aRows)
						{
							$row = $aRows[0];
							if (!FTArrayUtils::containsKeyCI(ParamsMvc::MODEL_RESULT_DATA, $row))
								$strResult = str_replace('{' . $key . '}', parent::render($row['template'], $row), $strResult);
							else
								$strResult = $this->renderText($row['markup'], $row[ParamsMvc::MODEL_RESULT_DATA]);
						}

						// Replace data
						$data[$dataKey] = $strResult;
					}
				}
			}

			return parent::renderText($text, $data);
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}
}
