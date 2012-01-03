<?php
require_once dirname(__FILE__) . '/../inc/cde.inc.php';

/**
 * Enumerations
 */

/**
 * File suffix
 */
class EntityFileType
{
	const EMPTY_TYPE = '';
	const CONFIG_TYPE = 'config';
	const CLASS_TYPE = 'class';
	const INCLUDE_TYPE = 'inc';
	const INTERFACE_TYPE = 'interface';
}

/**
 * Used in ActionRequest->params[]
 */
class Params
{
	// Operations
	const OPERATION_NAME = 'operation_name';
	const GET_OPERATIONS = 'get_operations';
	const IS_CALLABLE = 'is_callable';
	const OPERATION_CHECK = 'operation_check';
	const OPERATION_GET = 'get';
	const OPERATION_ADD = 'add';
	const OPERATION_UPDATE = 'update';
	const OPERATION_DELETE = 'delete';
	const OPERATION_FILTER = 'operation_filter';
	const OPERATION_USER_LOGIN = 'login';
	const OPERATION_USER_GET_SESSION = 'get_session';
	const OPERATION_USER_LOGOUT = 'logout';
	const OPERATION_GET_CONFIG = 'get_config';
	const OPERATION_GET_BY_ALIAS = 'get_by_alias';

	// Params
	const ID = 'id';
	const PARENT_ID = 'parent_id';
	const ALIAS = 'alias';
	const MARKUP = 'markup';

	// Relation
	const RELATIONS = 'relations';
	const RELATION_SOURCE_FIELD = 'relation_source_field';
	const RELATION_SOURCE_FIELD_DEFAULT_NAME = '_parent_id';
	const RELATION_DESTINATION_FIELD = 'relation_destination_field';
	const RELATION_DESTINATION_FIELD_DEFAULT_NAME = '_id';

	// Data values
	const DATA = 'data';
	const DATA_ROW = 'data_row';
}

class ParamsConfig
{
	const DATA_OBJECT = 'data_object';

	const EDITOR_ID = 'editor_id';
	const EDITOR_DEFAULT = 'default';
	const EDITOR_LIST = 'list';
}

class ParamsMvc
{
	const ENTITY_CONTROLLER = 'controller';
	const ENTITY_MODEL = 'model';
	const ENTITY_VIEW = 'view';

	const MVC_MODEL = 'mvc_model';
	const MVC_VIEW = 'mvc_view';

	const NO_MODEL = 'no_model';
	const NO_VIEW = 'no_view';

	const CUSTOM_MODEL = 'custom_model';
	const CUSTOM_VIEW = 'custom_view';

	const DEFAULT_MODEL_NAME = 'base';
	const DEFAULT_VIEW_NAME = 'base';

	const IS_NOT_RENDER = 'is_not_render';
	const IS_NOT_EXECUTE = 'is_not_execute';
	const MODEL_RESULT_DATA = 'model_result_data';
	const APP_NAME = 'app_name';
}

class ParamsSql
{
	const TABLE = 'table';
	const FIELDS = 'fields';
	const JOIN = 'join';
	const RESTRICTION = 'restriction';
	const RESTRICTION_DATA = 'restriction_data'; // :key => 'value' pair for PDO->bindValue()
	const ORDER_BY = 'order_by';
	const GROUP_BY = 'group_by';
	const HAVING = 'having';
	const LIMIT = 'limit';
	const PROCEDURE = 'procedure';
}
