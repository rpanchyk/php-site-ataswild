<?php
require_once dirname(__FILE__) . '/../../inc/cde.inc.php';

/**
 * Base view
 */
class BaseView extends FTFireTrot implements IView
{
	protected $result;

	public function __construct()
	{
		try
		{
			$this->result = '';
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}

	public function render($template, $data, $bIsMakeOut = FALSE)
	{
		try
		{
			global $engineConfig;

			FTException::throwOnTrue(!isset($template) || empty($template), 'No template');

			// Get path to template file
			$filePath = FTFileSystem::pathCombine(TEMPLATE_PATH, $engineConfig['out_data']['template'], 'html', $template . '.html');

			FTException::throwOnTrue(!file_exists($filePath), 'File not found: ' . $filePath);

			// Get template content
			$this->result = file_get_contents($filePath);

			// @todo - Process CSS files
			// Regexp css
			// Replace url(/xxx) by $engineConfig['out_data']['web_path'] . '/html'
			// Replace <link rel="stylesheet" with css content

			if (!FTArrayUtils::checkData($data))
				return $this->result;

			// Global vars
			if (!isset($data['theme']))
				$data['theme'] = $engineConfig['out_data']['web_path'] . '/html';

			// Replace placeholders
			$this->result = $this->renderText($this->result, $data);

			// Show output
			if ($bIsMakeOut)
				echo $this->result;

			return $this->result;
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}

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
