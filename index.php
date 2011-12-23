<?php
try
{
	require_once dirname(__FILE__) . '/engine/core/main.php';
}
catch (Exception $ex)
{
	if ($engineConfig['system']['is_debug'])
	{
		echo '<pre>';
		print_r($ex);
		echo '</pre>';
	}
}
