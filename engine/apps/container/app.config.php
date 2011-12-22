<?php

$config = array();

$config['editor']['default']['fields'] = array(
	'_id' => array('is_readonly' => '1', 'is_hidden' => '1'),
	'alias' => array('name_ru' => 'Алиас (англ.)', 'is_null' => '0'),
	'name' => array('name_ru' => 'Имя (рус.)', 'is_null' => '0', 'default_value' => 'Новый контейнер'),
	'markup' => array('name_ru' => 'Html разметка'),
	'is_section' => array('name_ru' => 'Раздел сайта?', 'is_bool' => '1', 'default_value' => '1'),
	'is_default' => array('name_ru' => 'По умолчанию?', 'is_bool' => '1'),
	//'is_active' => array('name_ru' => 'Активен?', 'is_bool' => '1')
);

return $config;
