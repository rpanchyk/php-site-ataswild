<?php
require_once dirname(__FILE__) . '/../../inc/cde.inc.php';

/**
 * Base view
 */
class BaseView extends FTFireTrot implements IView
{
	public function render($template, $data, $bIsUseTemplateAsMarkup = FALSE)
	{
		try
		{
			global $engineConfig, $request;

			// Result HTML
			$result = '';

			FTException::throwOnTrue(!isset($template) || empty($template), 'No template');

			// Get path to template file
			$filePath = FTFileSystem::pathCombine(TEMPLATE_PATH, $engineConfig['out_data']['template'], 'html', $template . '.html');

			// Get template content
			if (!$bIsUseTemplateAsMarkup)
			{
				FTException::throwOnTrue(!file_exists($filePath), 'File not found: ' . $filePath);
				$result = file_get_contents($filePath);
			}
			else
				$result = $template;

			// @todo - Process CSS files
			// Regexp css
			// Replace url(/xxx) by $engineConfig['out_data']['web_path'] . '/html'
			// Replace <link rel="stylesheet" with css content

			if (!FTArrayUtils::checkData($data))
				return $result;

			// Set global vars
			if (!isset($data['theme']))
				$data['theme'] = $engineConfig['out_data']['web_path'] . '/html';
			if (!isset($data['lang']))
				$data['lang'] = $request->dataMvc->getLanguage();

			// Spec. project vars
			if (!isset($data['txtImage']))
			{
				$txtImage = 'Picture';
				$txtOf = 'of';
				switch ($request->dataMvc->getLanguage())
				{
					case 'ru':
						$txtImage = 'Рисунок';
						$txtOf = 'из';
						break;
					case 'ua':
						$txtImage = 'Малюнок';
						$txtOf = 'з';
						break;
				}
				$data['txtImage'] = $txtImage;
				$data['txtOf'] = $txtOf;
			}

			// Replace placeholders
			$result = $this->renderText($result, $data);

			return $result;
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}

	/**
	 * Replace keys with values in text
	 * @param String $text - input text
	 * @param Array $data - key => value pairs
	 * @return String
	 */
	public function renderText($text, $data = array())
	{
		try
		{
			if (!FTArrayUtils::checkData($data))
				return $text;

			// Replace placeholders
			foreach ($data as $k => $v)
			{
				FTException::throwOnTrue(is_array($v), 'Render text failed for key: ' . $k);
				$text = str_replace('{' . $k . '}', $v, $text);
			}

			return $text;
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}
}
