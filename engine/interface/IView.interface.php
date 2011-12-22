<?php
require_once dirname(__FILE__) . '/../inc/cde.inc.php';

/**
 * Interface for views
 */
interface IView
{
	public function render($template, $data, $bIsMakeOut = TRUE);
}
