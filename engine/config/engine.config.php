<?php
require_once dirname(__FILE__) . '/../inc/cde.inc.php';

/**
 * Engine configuration
 */

$engineConfig = array();

$engineConfig['engine']['version'] = '4.0';
$engineConfig['engine']['developer'] = 'Ben';
$engineConfig['engine']['designer'] = 'Geo';
$engineConfig['engine']['company'] = 'FireTrot';
$engineConfig['engine']['develop_date_start'] = '2011-12-23';
$engineConfig['engine']['develop_date_end'] = '2012-xx-xx';

$engineConfig['requirements']['php_min_version'] = '5.1.0';
$engineConfig['requirements']['pdo_enable'] = TRUE;

$engineConfig['system']['is_debug'] = TRUE;

$engineConfig['database']['type'] = 'mysql';
$engineConfig['database']['host'] = 'localhost';
$engineConfig['database']['port'] = '3306';
$engineConfig['database']['name'] = 'portfolio_ataswild';
$engineConfig['database']['username'] = 'user';
$engineConfig['database']['password'] = 'pass';
$engineConfig['database']['cache_dir'] = CACHE_QUERY_PATH;
$engineConfig['database']['cache_ttl_default'] = '0'; // seconds

$engineConfig['web_data']['super_globals'] = array('_GET', '_POST', '_REQUEST', '_FILES', '_ENV', '_SERVER', '_COOKIE', '_SESSION');

$engineConfig['mvc_data']['langs'] = array('ru', 'en');
$engineConfig['mvc_data']['lang_default'] = 'ru';
$engineConfig['mvc_data']['formatters'] = array('array', 'html', 'xml', 'rss');
$engineConfig['mvc_data']['formatter_default'] = 'html';
$engineConfig['mvc_data']['allowed_webaccess_apps'] = array('auth', 'comments', 'faq', 'feedback', 'news', 'profile');
$engineConfig['mvc_data']['app_alias_default'] = 'home';
$engineConfig['mvc_data']['app_operation_default'] = 'get';

$engineConfig['out_data']['template'] = 'Default';
$engineConfig['out_data']['web_path'] = '/public/template/' . $engineConfig['out_data']['template'];

$engineConfig['smtp']['server_host'] = 'smtp.freenet.com.ua';
$engineConfig['smtp']['server_port'] = '25';
$engineConfig['smtp']['server_user'] = '';
$engineConfig['smtp']['server_pass'] = '';
$engineConfig['smtp']['email_address'] = 'email';
$engineConfig['smtp']['email_subject'] = 'Новый отзыв с сайта "Ataswild"';

// Place array to globals
$GLOBALS['engineConfig'] = $engineConfig;
