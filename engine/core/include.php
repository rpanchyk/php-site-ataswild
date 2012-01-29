<?php
require_once dirname(__FILE__) . '/../inc/cde.inc.php';

/**
 * Includes
 */

// Defines
require_once dirname(__FILE__) . '/../inc/define.inc.php';

// Base & Core classes
require_once SLIB_PATH . DS . 'FTFireTrot.class.php';
require_once SLIB_PATH . DS . 'FTCore.class.php';

// Global functionality
FTCore::loadInclude(INC_PATH, 'enum');
FTCore::loadInclude(INC_PATH, 'function');
FTCore::loadInclude(INC_PATH, 'global');

// Interfaces
FTCore::loadInterface(INTERFACE_PATH, 'IController');
FTCore::loadInterface(INTERFACE_PATH, 'IModel');
FTCore::loadInterface(INTERFACE_PATH, 'IView');
FTCore::loadInterface(INTERFACE_PATH, 'IArrayable');
FTCore::loadInterface(INTERFACE_PATH, 'IHtmlable');
FTCore::loadInterface(INTERFACE_PATH, 'IJsonable');
FTCore::loadInterface(INTERFACE_PATH, 'IXmlable');

// System library classes
FTCore::loadClass(SLIB_PATH, 'FTStringUtils');
FTCore::loadClass(SLIB_PATH, 'FTArrayUtils');
FTCore::loadClass(SLIB_PATH, 'FTFileSystem');
FTCore::loadClass(SLIB_PATH, 'FTImageUtils');
FTCore::loadClass(SLIB_PATH, 'FTTimeProfiler');
FTCore::loadClass(SLIB_PATH, 'FTException');

// Application library classes
FTCore::loadClass(LIB_PATH, 'ActionRequest');
FTCore::loadClass(LIB_PATH, 'ActionResponse');
FTCore::loadClass(LIB_PATH, 'DatabaseDriver');
FTCore::loadClass(LIB_PATH, 'WebData');
FTCore::loadClass(LIB_PATH, 'MvcData');
FTCore::loadClass(LIB_PATH, 'MvcFactory');
FTCore::loadClass(LIB_PATH, 'Guid');

// Server & engine configurations
FTCore::loadConfig(CONF_PATH, 'server');
FTCore::loadConfig(ROOT_PATH, 'engine');

// Prepare engine before start
FTCore::loadInclude(INC_PATH, 'prepare');

// Create initial request & response
$request = new ActionRequest(NULL);
$response = new ActionResponse();

// Create base controller (just for existance)
$base = MvcFactory::create('base', ParamsMvc::ENTITY_CONTROLLER);
