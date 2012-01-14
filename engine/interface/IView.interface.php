<?php
require_once dirname(__FILE__) . '/../inc/cde.inc.php';

/**
 * Interface for views
 */
interface IView
{
	/**
	 * Return HTML as result of processing template and input data
	 * @param String $template - template name or markup
	 * @param Array $data - input data
	 * @param Boolean $bIsUseTemplateAsMarkup - don't include template file but use it as markup (default: FALSE)
	 * @return String
	 */
	function render($template, $data, $bIsUseTemplateAsMarkup = FALSE);
}
