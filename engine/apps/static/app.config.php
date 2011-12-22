<?php

$config = array();

$config['editor']['default']['fields'] = array(
	'_id' => array('is_readonly' => '1', 'is_hidden' => '1'),
	'alias' => array('name_ru' => 'Алиас (англ.)', 'is_readonly' => '1', 'is_null' => '0'),
	'name' => array('name_ru' => 'Название', 'is_null' => '0', 'default_value' => 'Новый блок'),
	'template' => array('name_ru' => 'Шаблон', 'is_null' => '0'),
	'content' => array('name_ru' => 'Содержимое блока', 'rich_editor' => '1'),
	//'is_active' => array('name_ru' => 'Активен?', 'is_bool' => '1')
);

return $config;
