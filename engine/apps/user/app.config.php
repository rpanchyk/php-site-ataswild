<?php
require_once dirname(__FILE__) . '/../../inc/cde.inc.php';

global $request;

$config = array();

$config['message'] = array(
	'EMAIL_EMPTY' => array('name_ru' => 'Email не задан'),
	'PASSWORD_EMPTY' => array('name_ru' => 'Пароль не задан'),
	'USER_NOT_FOUND' => array('name_ru' => 'Пользователь не найден'),
	'USER_BLOCKED' => array('name_ru' => 'Пользователь заблокирован'),
	'USER_DELETED' => array('name_ru' => 'Пользователь удален'),
	'LOGIN_OK' => array('name_ru' => 'Вход выполнен. Перенаправление...')
);

$config['user_status'] = array(
	'active' => array('name_ru' => 'Активен'),
	'block' => array('name_ru' => 'Заблокирован'),
	'delete' => array('name_ru' => 'Удален')
);

$config['cookie'] = array(
	'name' => 'ftusid',
	'expire' => 3600 * 24 * 14, // ms
	'domain' => $request->dataWeb->server['SERVER_NAME'],
	'path' => '/',
	'secure' => NULL,
	'httponly' => NULL
);

$config['editor']['list']['fields'] = array(
	'email' => array('name_ru' => 'E-mail', 'style' => 'width:30%;'),
	'name' => array('name_ru' => 'Имя', 'style' => 'width:50%;'),
	//	'group_id' => array('name_ru' => 'Группа', 'style' => 'width:10%;'),
	//	'status' => array('name_ru' => 'Статус', 'style' => 'width:10%;')
);

$config['editor']['default']['fields'] = array(
	'name' => array('name_ru' => 'Имя', 'is_null' => '0'),
	'email' => array('name_ru' => 'E-mail', 'is_null' => '0'),
	'password' => array('name_ru' => 'Пароль', 'is_hide_value' => '1'),
	'group_id' => array('name_ru' => 'Группа', 'is_null' => '0', 'is_skip' => '1'),
	'status' => array('name_ru' => 'Статус', 'is_null' => '0', 'is_skip' => '1')
);

return $config;
