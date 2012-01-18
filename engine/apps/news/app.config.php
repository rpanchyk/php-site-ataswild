<?php
require_once dirname(__FILE__) . '/../../inc/cde.inc.php';

$config = array();

$config[ParamsConfig::OBJECT_ATTACH_ENTITY] = 'news_data';
$config['list_order'] = 'is_important DESC, date_pub DESC';

$config['editor'][ParamsConfig::EDITOR_DEFAULT]['fields'] = array(
	'_id' => array('is_readonly' => '1', 'is_hidden' => '1'),
	'alias' => array('name_ru' => 'Алиас (англ.)', 'is_readonly' => '1', 'is_null' => '0'),
	'name' => array('name_ru' => 'Название', 'is_null' => '0', 'default_value' => 'Раздел новостей'),
	'template' => array('name_ru' => 'Шаблон', 'is_null' => '0'),
	'is_active' => array('name_ru' => 'Активен?', 'is_bool' => '1'),
);

$config['editor'][ParamsConfig::EDITOR_LIST]['fields'] = array(
	'_id' => array('name_ru' => 'ID', 'style' => 'width:3%; text-align:center;'),
	'title' => array('name_ru' => 'Название', 'is_null' => '0'),
	'_date_create' => array('name_ru' => 'Дата создания', 'style' => 'width:20%; text-align:center;'),
	'_date_modify' => array('name_ru' => 'Дата изменения', 'style' => 'width:20%; text-align:center;'),
);

$config['editor'][$config[ParamsConfig::OBJECT_ATTACH_ENTITY]]['fields'] = array(
	'_id' => array('is_readonly' => '1', 'is_hidden' => '1'),
	'title' => array('name_ru' => 'Название', 'is_null' => '0'),
	'content' => array('name_ru' => 'Содержимое', 'rich_editor' => '1'),
	'date_pub' => array('name_ru' => 'Дата публикации'),
	'is_important' => array('name_ru' => 'Важная?', 'is_bool' => '1', 'default_value' => '0'),
	'is_active' => array('name_ru' => 'Активен?', 'is_bool' => '1', 'default_value' => '1'),
);

return $config;
