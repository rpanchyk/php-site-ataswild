<?php
require_once dirname(__FILE__) . '/../../inc/cde.inc.php';

$config = array();

$config['web_path'] = '/handler.php';
$config['regex_web_param'] = "/^[-a-zA-Z0-9_]+$/";
$config['not_authorized'] = 'Пользователь не авторизован.<br /><a href="/admin/">Войти</a>';

return $config;
