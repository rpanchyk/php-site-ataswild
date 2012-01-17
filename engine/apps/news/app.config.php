<?php
require_once dirname(__FILE__) . '/../../inc/cde.inc.php';

$config = array();

$config[ParamsConfig::OBJECT_ATTACH_ENTITY] = 'news_data';

$config['editor'][ParamsConfig::EDITOR_DEFAULT]['fields'] = array(
	'_id' => array('is_readonly' => '1', 'is_hidden' => '1'),
	'alias' => array('name_ru' => 'Алиас (англ.)', 'is_readonly' => '1', 'is_null' => '0'),
	'name' => array('name_ru' => 'Название', 'is_null' => '0', 'default_value' => 'Раздел новостей'),
	'template' => array('name_ru' => 'Шаблон', 'is_null' => '0'),
	'is_active' => array('name_ru' => 'Активен?', 'is_bool' => '1'),
	//'save_settings' => array('is_hidden' => '1', 'default_value' => '1')
);

$config['editor'][ParamsConfig::EDITOR_LIST]['fields'] = array(
	'_id' => array('name_ru' => 'ID', 'style' => 'width:3%; text-align:center;'),
	'title' => array('name_ru' => 'Название', 'is_null' => '0'),
	'date_create' => array('name_ru' => 'Дата создания', 'style' => 'width:20%; text-align:center;'),
	'date_modify' => array('name_ru' => 'Дата изменения', 'style' => 'width:20%; text-align:center;'),
//'email' => array('name_ru' => 'E-mail', 'is_null' => '0', 'style' => 'width:20%;'),
	//'content' => array('name_ru' => 'Комментарий', 'is_null' => '0', 'style' => 'overflow:hidden;'),
	//'is_active' => array('name_ru' => 'Активен?', 'style' => 'width:10%;')
);

$config['editor'][$config[ParamsConfig::OBJECT_ATTACH_ENTITY]]['fields'] = array(
	'_id' => array('is_readonly' => '1', 'is_hidden' => '1'),
	//'_parent_id' => array('is_readonly' => '1', 'is_hidden' => '1'),
	'title' => array('name_ru' => 'Название', 'is_null' => '0'),
	'content' => array('name_ru' => 'Содержимое', 'rich_editor' => '1'),
	'is_active' => array('name_ru' => 'Активен?', 'is_bool' => '1', 'default_value' => '1'),
);

return $config;
