<?php
require_once dirname(__FILE__) . '/../../inc/cde.inc.php';

class GalleryView extends BaseView
{
	public function render($template, $data, $bIsUseTemplateAsMarkup = FALSE)
	{
		try
		{
			// Prepare content
			$content = $data['content'];
			$content = str_replace(' style="border: 1px dashed rgb(119, 119, 119);"', '', $content);
			$content = str_replace('<li', '<a', $content);
			$content = str_replace('</li', '</a', $content);
			$content = strip_tags($content, '<a><img>');
			$content = preg_replace('/style="(.*?)"/i', '', $content);
			$data['content'] = $content;

			return parent::render($template, $data, $bIsUseTemplateAsMarkup);
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}
}
