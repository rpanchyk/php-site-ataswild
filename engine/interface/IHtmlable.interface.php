<?php
require_once dirname(__FILE__) . '/../inc/cde.inc.php';

/**
 * Interface for representing controller data as html
 */
interface IHtmlable
{
	function asHtml();
}
