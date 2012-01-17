<?php
function showLangs()
{
	global $engineConfig, $request;
	
	if (!FTArrayUtils::checkData(@$engineConfig['mvc_data']['langs']))
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
			//$(location).attr('href', "http:\/\/" + "<?=$request->dataWeb->server['SERVER_NAME']?><?=$request->dataWeb->server['REQUEST_URI']?>");
			var lcq = unescape(atob($.cookie("ftlcq")));
			if (lcq != '' && lcq.indexOf('=container') == -1)
				doajaxContent(lcq, null);
		}

		// 'font-weight:bold;background-color:#eee;'
		clearActive();
		$(obj).css("font-weight", "bold");
		$(obj).css("background-color", "#eee");
	}
	function clearActive(){
		$('.lang_link').each(function(){
			$(this).css("font-weight", "");
			$(this).css("background-color", "");
		});
	}
	</script>
	
	<style type="text/css">
	/* <![CDATA[ */
	.lang_link {
		text-decoration:none;padding:3px;
	}
	/* ]]> */
	</style>
	
	<div>
	<?
	foreach ($engineConfig['mvc_data']['langs'] as $value)
	{
		$lang = @$request->dataWeb->cookie[$engineConfig['cookie']['name_lang']] ? $request->dataWeb->cookie[$engineConfig['cookie']['name_lang']] : $engineConfig['mvc_data']['lang_default'];
		$styleActive = ($value == $lang ? 'font-weight:bold;background-color:#eee;' : '');
		?><a href="#" onclick="setCookie(this)" class="lang_link" style="<?=$styleActive?>"><?=$value?></a> | <?
	}
	?></div><?
}
