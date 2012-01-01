<?php
require_once dirname(__FILE__) . '/../inc/cde.inc.php';

/**
 * Interface for representing controller data as xml
 */
interface IXmlable
{
	function asXml();
}
