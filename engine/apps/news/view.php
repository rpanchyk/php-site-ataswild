<?php
require_once dirname(__FILE__) . '/../../inc/cde.inc.php';

class NewsView extends BaseView
{
	public function render($template, $data, $bIsUseTemplateAsMarkup = FALSE)
	{
		try
		{
			$data4render = @$data[ParamsConfig::OBJECT_ATTACH_ENTITY];
			//FTException::throwOnTrue(empty($data4render), 'No ' . ParamsConfig::OBJECT_ATTACH_ENTITY);

			if (empty($data4render))
				return '';

			$strResult = '';
			foreach ($data4render as $row)
			{
				// Change tpl, if need
				$tplSuffix = $row['is_important'] === '0' ? '' : '_important';

				// Change data
				$row['date_pub'] = date('d/m/Y', strtotime($row['date_pub']));

				// Make render
				$strResult .= parent::render($data['template'] . $tplSuffix, $row);
			}
			return $strResult;
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}
}
