<?php
require_once dirname(__FILE__) . '/../engine/core/main.php';

// Create handler
$handler = MvcFactory::create('handler', ParamsMvc::ENTITY_CONTROLLER);
$handlerPath = $handler->config['web_path'];

// Check auth
$user = MvcFactory::create('user', ParamsMvc::ENTITY_CONTROLLER);
$reqAuth = new ActionRequest($request);
$reqAuth->params[Params::OPERATION_NAME] = Params::OPERATION_USER_GET_SESSION;
$dataAuth = $user->run($reqAuth, $response);

// Send buffer and turn off output buffering
@ob_end_flush();
?>
<html>
<head>

<meta http-equiv="Content-Language" content="ru-RU" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="Content-Style-Type" content="text/css" />
<meta http-equiv="Content-Script-Type" content="text/javascript" />
<meta name="keywords" content="" />
<meta name="description" content="" />

<title>Control panel</title>

<link type="text/css" rel="stylesheet" href="css/layout.css" />
<link type="text/css" rel="stylesheet" href="css/style.css" />
<link type="text/css" rel="stylesheet" href="css/jquery.treeview.css" />
<link type="text/css" rel="stylesheet" href="css/jquery.ui.all.css">

<script type="text/javascript" src="js/jquery.js"></script>
<script type="text/javascript" src="js/jquery-ui.js"></script>
<script type="text/javascript" src="js/jquery.layout.js"></script>
<script type="text/javascript" src="js/jquery.treeview.js"></script>
<script type="text/javascript" src="js/jquery.ui.core.js"></script>
<script type="text/javascript" src="js/webtoolkit.url.js"></script>
<script type="text/javascript" src="../engine/editor/ckeditor_3.6.2/ckeditor.js"></script>
<script type="text/javascript" src="js/jquery.cookie.js"></script>

<script type="text/javascript">
$(document).ready(function () {
	if (document.getElementById('container') == null)
		return;
	
	$('#container').layout({ 
		defaults: {
			fxName:						"slide",
			spacing_open:				3,
			spacing_closed:				3,
			applyDefaultStyles:			true
			}
		, north: {
			resizable:					false,
			closable:					false
			}
		, west: {
			size:						"20%"
			}
	});

	// Load tree
	doajaxTree();
});

function doajaxContent(params, element)
{
	if (element != null)
	{
		// Highlight item
		$('a[class=treelink]').each(function(){
			$(this).css("background-color","").css("border","");
		});
		if (element != 'is_skip')
			$(element).css("background-color","#FFFF99").css("border","1px solid #D8D8D8");
	}

	$.ajax({
		url: '<?php echo $handlerPath; ?>'+'?'+params,
		beforeSend: function(){
			showLoading('cnt');
		},
		success: function(data) {
			$('#cnt').html(data);
		},
		error: function(jqXHR, textStatus, errorThrown) {
			alert(textStatus + ': ' + errorThrown);
		}
	});
}

function doajaxTree()
{
	$.ajax({
		url: '<?php echo $handlerPath; ?>'+'?object_app=handler&object_operation=get_tree',
		beforeSend: function(){
			$('#cnt').html('');
			showLoading('cptree');
		},
		success: function(data) {
			$('#cptree').html(data);
			$("#browser").treeview();
			$("#settings").treeview();
		},
		error: function(jqXHR, textStatus, errorThrown) {
			alert(textStatus + ': ' + errorThrown);
		}
	});
}

function formSubmit(obj, textareaIds)
{
	var dataParams = unparam($.param( $('form input,textarea,select') ));

	for (var i = 0; i < textareaIds.length; i++) {
		//eval('CKEDITOR.instances.'+textareaIds[i]+'.updateElement();');
		dataParams[textareaIds[i]] = Url.encode(eval('CKEDITOR.instances.'+textareaIds[i]+'.getData()'));
	}
	
	$.ajax({
		type: 'POST',
		url: '<?php echo $handlerPath; ?>',
		data: dataParams,
		success: function(data) {
			$('#cnt').html(data);
		},
		error: function(jqXHR, textStatus, errorThrown) {
			alert(textStatus + ': ' + errorThrown);
		}
	});
}

function formLoginSubmit()
{
	$.ajax({
		type: 'POST',
		url: '<?php echo $handlerPath; ?>',
		data: unparam($.param( $('form input,textarea,select') )),
		success: function(data) {
			if (data.indexOf('<!-- error -->') != -1)
				$('#login_result').html(data);
			else
			{
				$('#login_result').html('Login OK. Redirecting...');
				$(location).attr('href','/admin/');
			}
		},
		error: function(jqXHR, textStatus, errorThrown) {
			alert(textStatus + ': ' + errorThrown);
		}
	});
}
function doLogout()
{
	$.ajax({
		url: '<?php echo $handlerPath; ?>'+'?object_app=user&object_operation=logout',
		success: function(data) {
			if (data.indexOf('<!-- error -->') != -1)
				$('#cnt').html(data);
			else
				$(location).attr('href','/admin/');
		},
		error: function(jqXHR, textStatus, errorThrown) {
			alert(textStatus + ': ' + errorThrown);
		}
	});

	return false;
}

function showLoading(element_id)
{
	$('#'+element_id).html('');
	setTimeout('if ($(\'#'+element_id+'\').html() == "") { $(\'#'+element_id+'\').html(\'<img src="/admin/images/loading1.gif" border="0" />\'); }', 500);
}

function unparam(p)
{
	// http://stackoverflow.com/questions/1131630/javascript-jquery-param-inverse-function/4764403#4764403
	
    var ret = {}, seg = p.replace(/^.*\?/,'').split('&'), len = seg.length, i = 0, s;
    for (;i<len;i++) {
        if (!seg[i]) { continue; }
        s = seg[i].split('=');
        ret[s[0]] = s[1];
    }
    return ret;
}

function bindEditorFull(elementid)
{
	// http://docs.cksource.com/ckeditor_api/symbols/CKEDITOR.config.html#.toolbar
	
	delete CKEDITOR.instances[elementid];
    CKEDITOR.replace(elementid, { 
        toolbar: 
	    	[
	    	    { name: 'document',    items : [ '-Source','--','-Save','-NewPage','-DocProps','Preview','-Print','-','Templates' ] },
	    	    { name: 'clipboard',   items : [ 'Cut','Copy','Paste','PasteText','PasteFromWord','-','Undo','Redo' ] },
	    	    { name: 'editing',     items : [ 'Find','Replace','-','SelectAll','--','-SpellChecker', '-Scayt' ] },
	       	    { name: 'tools',       items : [ 'Maximize','ShowBlocks','--','-About','Source' ] },
	    	    //{ name: 'forms',       items : [ 'Form', 'Checkbox', 'Radio', 'TextField', 'Textarea', 'Select', 'Button', 'ImageButton', 'HiddenField' ] },
	    	    //'/',
	    	    { name: 'basicstyles', items : [ 'Bold','Italic','Underline','Strike','Subscript','Superscript','-','RemoveFormat' ] },
	    	    { name: 'paragraph',   items : [ 'NumberedList','BulletedList','-','Outdent','Indent','-','Blockquote','CreateDiv','-','JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock','--','-BidiLtr','-BidiRtl' ] },
	     	    { name: 'links',       items : [ 'Link','Unlink','Anchor' ] },
	    	    { name: 'insert',      items : [ 'Image','-Flash','Table','HorizontalRule','-Smiley','SpecialChar','-PageBreak' ] },
	    	    { name: 'colors',      items : [ 'TextColor','BGColor' ] },
	       	    //'/',
	    	    { name: 'styles',      items : [ 'Styles','Format','Font','FontSize' ] }
	    	]
	    , on :
	        {
	            instanceReady : function( ev )
	            {
	                // Output paragraphs as <p>Text</p> - http://docs.cksource.com/CKEditor_3.x/Developers_Guide/Output_Formatting
	                this.dataProcessor.writer.setRules( 'p',
                    {
                        indent : false,
                        breakBeforeOpen : true,
                        breakAfterOpen : false,
                        breakBeforeClose : false,
                        breakAfterClose : true
                    });
	            }
	        }
    	, skin: 'kama_firetrot'
    	, language: 'ru'
        , toolbarCanCollapse: false
        , image_previewText: ' '
        , height: '285px'
        , enterMode: CKEDITOR.ENTER_BR // p | div | br
        , shiftEnterMode: CKEDITOR.ENTER_BR // p | div | br
        , autoParagraph: false
	    , filebrowserBrowseUrl: '/engine/editor/ckeditor_3.6.2/kcfinder/browse.php?type=files'
	    , filebrowserImageBrowseUrl: '/engine/editor/ckeditor_3.6.2/kcfinder/browse.php?type=images'
	    , filebrowserFlashBrowseUrl: '/engine/editor/ckeditor_3.6.2/kcfinder/browse.php?type=flash'
	    , filebrowserUploadUrl: '/engine/editor/ckeditor_3.6.2/kcfinder/upload.php?type=files'
	    , filebrowserImageUploadUrl: '/engine/editor/ckeditor_3.6.2/kcfinder/upload.php?type=images'
	    , filebrowserFlashUploadUrl: '/engine/editor/ckeditor_3.6.2/kcfinder/upload.php?type=flash'     	            
    });
}
</script>

<style type="text/css">
/* <![CDATA[ */

.section {
	width: 100%;
	background: #EFEFEF;
}
ul.tabs {
	height: 28px;
	line-height: 25px;
	list-style: none;
	border-bottom: 1px solid #DDD;
	background: #FFF;
}
.tabs li {
	float: left;
	display: inline;
	margin: 0 1px -1px 0;
	padding: 0 13px 1px;
	color: #777;
	cursor: pointer;
	background: #EFEFEF;
	border: 1px solid #E4E4E4;
	border-bottom: 1px solid #F9F9F9;
	position: relative;
}
.tabs li:hover,
.tabs li.current {
	color: #444;
	background: #FFF;
	padding: 0 13px 2px;
	border: 1px solid #D4D4D4;
	border-bottom: 1px solid #EFEFEF;
}
.box {
	/*display: none;*/
	border: 1px solid #D4D4D4;
	border-width: 0 1px 1px;
	background: #FFF;
	padding: 0 12px;
}
.box.visible {
	display: block;
}

a.treelink {
	color: #0000CC;
	padding-left:3px;
	padding-right:3px;
	cursor:pointer;
}

/* ]]> */
</style>

<style type="text/css">
/* <![CDATA[ */

input.ft_control, textarea.ft_control {
	border:solid 1px #848388;
}
input.ft_control:focus, textarea.ft_control:focus {
	background-color:#FFFFBB;
}

/* ]]> */
</style>
</head>
<body>
<?php if (!FTArrayUtils::checkData(@$dataAuth))
{
?>
<div style="padding-top:150px; text-align:center;">
	<div style="display:block; width:350px; border:1px solid #DDDDDD; box-shadow:1px 1px 7px #CCCCCC; font-size:16px; margin:0 auto;">
	<form action="/admin/" method="post" onsubmit="formLoginSubmit(); return false;">
		<table border="0" cellpadding="0" cellspacing="0" style="width:100%;">
			<tr>
				<td style="padding:20px; width:30%;">E-mail:</td>
				<td><input type="text" name="email" class="ft_control" style="width:90%;" /></td>
			</tr>
			<tr>
				<td style="padding:20px;">Password:</td>
				<td><input type="password" name="password" class="ft_control" style="width:90%;" /></td>
			</tr>
			<tr>
				<td></td>
				<td><input type="submit" class="ft_control" value="Войти" /></td>
			</tr>
		</table>
		<input type="hidden" name="object_app" value="user" />
		<input type="hidden" name="object_operation" value="login" />
	</form>
	</div>
	<div id="login_result" style="padding:10px;">&nbsp;</div>
</div>
<?php
}
else
{
?>
	<div id="container">
		<div class="ui-layout-north">
			<a>Здравствуйте, <?php echo @$dataAuth[0]['name']; ?>!</a> [<a href="#" onclick="doLogout();" style="text-decoration:none;">Выход</a>]
			<div style="float:right;"><?php //showLangs(); ?></div>
		</div>
		<div class="ui-layout-west" id="cptree"></div>
		<div class="ui-layout-center"><div id="cnt"></div></div>
	</div>
<?php
}
?>
</body>
</html>