<?php
require_once dirname(__FILE__) . '/../../inc/cde.inc.php';

$config = array();

$config['message'] = array(
	'USER_NOT_AUTHORIZED' => array('name_ru' => 'Пользователь не авторизован.<br /><a href="/admin/">Войти</a>')
);

$config['web_path'] = '/handler.php';
$config['regex_web_param'] = "/^[-a-zA-Z0-9_]+$/";

return $config;
