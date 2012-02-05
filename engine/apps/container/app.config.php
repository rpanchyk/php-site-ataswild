<?php
require_once dirname(__FILE__) . '/../../inc/cde.inc.php';

$config = array();

$config['editor'][ParamsConfig::EDITOR_DEFAULT]['fields'] = array(
	'_id' => array('is_readonly' => '1', 'is_hidden' => '1'),
	'alias' => array('name_ru' => 'Алиас (англ.)', 'is_null' => '0', 'is_readonly' => '1'),
	'name' => array('name_ru' => 'Имя (рус.)', 'is_null' => '0', 'default_value' => 'Новый контейнер'),
	'markup' => array('name_ru' => 'Html разметка'),
	'ord' => array('name_ru' => 'Сортировка', 'default_value' => '1000'),
	'is_section' => array('name_ru' => 'Раздел сайта?', 'is_bool' => '1', 'default_value' => '1'),
	'is_default' => array('name_ru' => 'По умолчанию?', 'is_bool' => '1'),
	'is_active' => array('name_ru' => 'Активен?', 'is_bool' => '1', 'default_value' => '1')
);

return $config;
