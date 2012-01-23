<?php
require_once dirname(__FILE__) . '/../../inc/cde.inc.php';

global $engineConfig;

$config = array();

$config['upload_path'] = FTFileSystem::pathCombine(UPLOAD_PATH, 'gallery');
$config['web_path'] = $engineConfig['out_data']['upload_web_path'] . '/gallery';

$config['editor']['default']['fields'] = array(
	'_id' => array('is_readonly' => '1', 'is_hidden' => '1'),
	'alias' => array('name_ru' => 'Алиас (англ.)', 'is_readonly' => '1', 'is_null' => '0'),
	'name' => array('name_ru' => 'Название', 'is_null' => '0', 'default_value' => 'Новый блок'),
	'template' => array('name_ru' => 'Шаблон', 'is_null' => '0'),
	'content' => array('name_ru' => 'Содержимое блока', 'gallery_editor' => '1'),
	//'is_active' => array('name_ru' => 'Активен?', 'is_bool' => '1')
);

return $config;
