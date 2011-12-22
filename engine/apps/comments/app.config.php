<?php

$config = array();

$config['data_objects'] = array(
	'comments' => array('name_ru' => 'Данные', 'table_name' => 'tComments'),
	'settings' => array('name_ru' => 'Настройки', 'table_name' => 'tCommentsSettings')
);

$config['comments']['editor']['list']['fields'] = array(
	'name' => array('name_ru' => 'Имя', 'is_null' => '0', 'style' => 'width:20%;'),
	'email' => array('name_ru' => 'E-mail', 'is_null' => '0', 'style' => 'width:20%;'),
	'content' => array('name_ru' => 'Комментарий', 'is_null' => '0', 'style' => 'overflow:hidden;'),
	//'is_active' => array('name_ru' => 'Активен?', 'style' => 'width:10%;')
);

$config['comments']['editor']['default']['fields'] = array(
	'name' => array('name_ru' => 'Имя', 'is_null' => '0'),
	'email' => array('name_ru' => 'E-mail', 'is_null' => '0'),
	'info' => array('name_ru' => 'Инфо'),
	'content' => array('name_ru' => 'Комментарий', 'is_null' => '0', 'rich_editor' => '1'),
	'is_active' => array('name_ru' => 'Активен?', 'is_bool' => '1')
);

$config['settings']['editor']['default']['fields'] = array(
	'smtp_server_host' => array('name_ru' => 'SMTP сервер', 'is_null' => '0'),
	'smtp_server_port' => array('name_ru' => 'SMTP порт', 'is_null' => '0', 'default_value' => '25'),
	'email' => array('name_ru' => 'E-mail', 'is_null' => '0'),
	'email_subject' => array('name_ru' => 'Тема письма', 'is_null' => '0'),
	'is_use_moderation' => array('name_ru' => 'Модерация', 'is_null' => '0', 'is_bool' => '1')
);

$config['message'] = array(
	'NAME_EMPTY' => array('name_ru' => 'Имя не задано'),
	'EMAIL_EMPTY' => array('name_ru' => 'Email не задан'),
	'CONTENT_EMPTY' => array('name_ru' => 'Комментарий не задан')
);

return $config;
