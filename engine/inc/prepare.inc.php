<?php
require_once dirname(__FILE__) . '/../inc/cde.inc.php';

/**
 * Run before engine start
 */

// Check system requirements
FTCore::checkSystemRequirements();

// Prepare dirs
FTCore::createDirs(array(VAR_PATH, CACHE_PATH, LOGS_PATH, UPLOAD_PATH, CACHE_QUERY_PATH, CACHE_CONTENT_PATH));
