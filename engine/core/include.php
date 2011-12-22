<?php
require_once dirname(__FILE__) . '/../inc/cde.inc.php';

/**
 * Includes
 */

// Defines
require_once dirname(__FILE__) . '/../inc/define.inc.php';

// Exception class
require_once SLIB_PATH . DS . 'FTException.class.php';

// Functionality
require_once INC_PATH . DS . 'enum.inc.php';
require_once INC_PATH . DS . 'function.inc.php';

// Base class
require_once SLIB_PATH . DS . 'FTFireTrot.class.php';

// Core class
require_once SLIB_PATH . DS . 'FTCore.class.php';

// Time profiler
FTCore::loadClass(SLIB_PATH, 'FTTimeProfiler');

// Server & engine configurations
FTCore::loadConfig(CONF_PATH, 'server');
FTCore::loadConfig(CONF_PATH, 'engine');

// Interfaces
FTCore::loadInterface(INTERFACE_PATH, 'IController');
FTCore::loadInterface(INTERFACE_PATH, 'IModel');
FTCore::loadInterface(INTERFACE_PATH, 'IView');
FTCore::loadInterface(INTERFACE_PATH, 'IArrayable');
FTCore::loadInterface(INTERFACE_PATH, 'IXmlable');
FTCore::loadInterface(INTERFACE_PATH, 'IHtmlable');

// System library classes
FTCore::loadClass(SLIB_PATH, 'FTStringUtils');
FTCore::loadClass(SLIB_PATH, 'FTArrayUtils');
FTCore::loadClass(SLIB_PATH, 'FTFileSystem');

// Library classes
FTCore::loadClass(LIB_PATH, 'ActionRequest');
FTCore::loadClass(LIB_PATH, 'ActionResponse');
FTCore::loadClass(LIB_PATH, 'DatabaseDriver');
FTCore::loadClass(LIB_PATH, 'WebData');
FTCore::loadClass(LIB_PATH, 'MvcData');
FTCore::loadClass(LIB_PATH, 'MvcFactory');
FTCore::loadClass(LIB_PATH, 'Guid');

// Global variables
require_once INC_PATH . DS . 'global.inc.php';
