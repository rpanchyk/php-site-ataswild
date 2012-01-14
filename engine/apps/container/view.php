<?php
require_once dirname(__FILE__) . '/../../inc/cde.inc.php';

class ContainerView extends BaseView
{
	public function render($template, $data, $bIsUseTemplateAsMarkup = FALSE)
	{
		FTException::throwOnTrue(!FTArrayUtils::checkData($data, 0), 'No data for view');

		// Row, for replacing array
		$strRow = '';

		// Go through input data
		foreach ($data as $dataKey => $dataValue)
		{
			// If string, don't process value
			if (!is_array($dataValue))
				continue;

			if (!FTArrayUtils::containsKeyCI(ParamsMvc::CONTAINER_DATA, $dataValue))
			{
				// Here value must be simple 1-dimens. array
				$strRow = parent::render($dataValue['template'], $dataValue);
			}
			else
			{
				// Unwind a ball of container data

				// Init markup
				$strMarkup = $dataValue['markup'];

				// Process container data
				foreach ($dataValue[ParamsMvc::CONTAINER_DATA] as $itemKey => $itemValue)
				{
					$dataItem = $itemValue[0];
					if (!FTArrayUtils::containsKeyCI(ParamsMvc::CONTAINER_DATA, $dataItem))
					{
						$str = $this->render($dataItem['template'], $dataItem);
						$strMarkup = str_replace('{' . $itemKey . '}', $str, $strMarkup);
					}
					else
					{
						// Render and use template as markup
						$strMarkup = $this->render($strMarkup, array($itemKey => $dataItem), TRUE);
					}
				}

				// Set result HTML for key
				$strRow = $strMarkup;
			}

			// Replace data with string
			$data[$dataKey] = $strRow;
		}

		// Get result html
		return parent::render($template, $data, $bIsUseTemplateAsMarkup);
	}
}
