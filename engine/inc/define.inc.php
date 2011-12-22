<?php
require_once dirname(__FILE__) . '/../inc/cde.inc.php';

/**
 * Constants
 */

// Utilities
define('DS', DIRECTORY_SEPARATOR);
define('SLASH', '/');
define('BACK_SLASH', '\\');
define('TWO_DOTS', '..');
define('THREE_DOTS', '...');
define('CRLF', "\r\n");
define('BR', '<br />');

// Pathes
define('ROOT_PATH', realpath(dirname(__FILE__) . SLASH . TWO_DOTS . DS . SLASH . TWO_DOTS));

define('ADMIN_PATH', ROOT_PATH . DS . 'admin');
define('ENGINE_PATH', ROOT_PATH . DS . 'engine');
define('PUBLIC_PATH', ROOT_PATH . DS . 'public');
define('TEMPLATE_PATH', ROOT_PATH . DS . 'template');
define('VAR_PATH', ROOT_PATH . DS . 'var');

define('APP_PATH', ENGINE_PATH . DS . 'apps');
define('CONF_PATH', ENGINE_PATH . DS . 'config');
define('CORE_PATH', ENGINE_PATH . DS . 'core');
define('EDITOR_PATH', ENGINE_PATH . DS . 'editor');
define('EXTERNAL_PATH', ENGINE_PATH . DS . 'external');
define('INC_PATH', ENGINE_PATH . DS . 'inc');
define('INTERFACE_PATH', ENGINE_PATH . DS . 'interface');
define('LIB_PATH', ENGINE_PATH . DS . 'lib');
define('SLIB_PATH', ENGINE_PATH . DS . 'slib');

define('CACHE_PATH', VAR_PATH . DS . 'cache');
define('LOGS_PATH', VAR_PATH . DS . 'logs');
define('UPLOAD_PATH', VAR_PATH . DS . 'upload');

define('CACHE_QUERY_PATH', CACHE_PATH . DS . 'query');
define('CACHE_CONTENT_PATH', CACHE_PATH . DS . 'content');
