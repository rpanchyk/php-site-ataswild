<?php
function showLangs()
{
	global $engineConfig, $request;
	
	if (!@is_array($engineConfig['mvc_data']['langs']) || !count($engineConfig['mvc_data']['langs']))
		return;
	
	?>
	<script type="text/javascript">
	function setCookie(obj){
		// Docs: http://www.electrictoolbox.com/jquery-cookies/
		if (obj != undefined && $(obj).text() != '')
		{
			// Set cookie
			$.cookie("<?=$engineConfig['cookie']['name_lang']?>", 
				$(obj).text(), 
				{ expires:365, path:"/", domain:"<?=$request->dataWeb->server['SERVER_NAME']?>" }
			);

			// Redirect
			$(location).attr('href', "http:\/\/" + "<?=$request->dataWeb->server['SERVER_NAME']?><?=$request->dataWeb->server['REQUEST_URI']?>");
		}
	}
	</script>
	<div>
	<?
	foreach ($engineConfig['mvc_data']['langs'] as $value)
	{
		$styleActive = $value == $request->dataWeb->cookie[$engineConfig['cookie']['name_lang']] ? 'font-weight:bold;background-color:#eee;' : '';
		?><a href="#" onclick="setCookie(this)" style="text-decoration:none;padding:3px;<?=$styleActive?>"><?=$value?></a> | <?
	}
	?></div><?
}
