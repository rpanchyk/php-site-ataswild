<?php

/**
 * Check direct execute
 */

// Check it!
if (@ENGINE_IS_STARTED !== 'y')
{
	// Clean output buffer
	ob_end_clean();

	// Show message
	die('Xacked by dummy!');
}

// Write this to file:
// require_once dirname(__FILE__) . '/../../inc/cde.inc.php';
