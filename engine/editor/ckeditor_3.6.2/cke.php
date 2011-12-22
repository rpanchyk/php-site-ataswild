<textarea id="CKEditor1" style="display:none;">qeqeqweqwewq</textarea>

<?php
ini_set('error_reporting', E_ALL);
ini_set('display_errors', E_ALL);

include "ckeditor.php";


$config = array();
$config['image_previewText'] = " ";
$config['toolbar'] = array(
	//'name' => 'document', 'items' => array('-Source','--','-Save','-NewPage','-DocProps','Preview','-Print','-','Templates')
);

$ckeditor = new CKEditor();
$ckeditor->basePath = '/engine/editor/ckeditor_3.6.2/';
$ckeditor->replace('CKEditor1', $config);

?>

<script type="text/javascript">
	//alert( CKEDITOR.instances.CKEditor1.getData() );
	
	CKEDITOR.plugins.load('pgrfilemanager');
</script>