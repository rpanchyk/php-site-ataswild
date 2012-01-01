<?php

function getTree($httpHandlerPath)
{
	global $request, $response;

	$strTree = '';

	// Get tree data
	$controller = MvcFactory::create('container', ParamsMvc::ENTITY_CONTROLLER);
	$req = new ActionRequest($request);
	$req->params[Params::OPERATION_NAME] = 'get_tree';
	$req->params[ParamsMvc::IS_NOT_RENDER] = TRUE;
	$dataTree = $controller->run($req, $response);
//echo '<pre>'; print_r($dataTree); echo '</pre>';
	// Decoration
	$strButtonStyle = 'style="display:inline; cursor:pointer;"';

	// Add buttons
	$strTree .= '<div style="text-align:left;">';
	$strTree .= '<div ' . $strButtonStyle . '><a onclick="doajaxContent(\'app=container&alias=\', \'is_skip\')" alt="Add container" title="Add container"><img src="/admin/images/tree_add_folder.png" border="0" /></a></div>';
	//$strTree .= '<div style="display:inline; padding-left:5%;"></div>';
	//$strTree .= '<div ' . $strButtonStyle . '><a onclick="doajaxContent(\'app=new&_id=-1\', \'is_skip\')" alt="Add element" title="Add element"><img src="/admin/images/tree_add_file.png" border="0" /></a></div>';
	$strTree .= '<div style="display:inline; padding-left:5%;"></div>';
	$strTree .= '<div ' . $strButtonStyle . '><a onclick="doajaxTree()" alt="Refresh" title="Refresh"><img src="/admin/images/tree_refresh.png" border="0" /></a></div>';
	$strTree .= '</div>';

	// Add separator
	$strTree .= '<div style="margin-top:5px; margin-bottom:10px; border:none; font-size:3px;"><div style="border:1px solid #D8D8D8; background-color:#D8D8D8; font-size:3px;"></div></div>';

	// Add tree
	$strTree .= '<ul id="browser" class="filetree" style="font-family:Arial; font-size:13px;">';
	$strTree .= '<li class="collapsable"><div class="hitarea collapsable-hitarea"></div><span class="folder"><a class="treelink">' . 'Сайт' . '</a></span>';
	$strTree .= '<ul>';
	$strTree .= getTreeBranch($dataTree, $httpHandlerPath);
	$strTree .= '</ul>';
	$strTree .= '</li>';
	$strTree .= '</ul>';

	$strTree .= '<ul id="settings" class="filetree" style="font-family:Arial; font-size:13px;">';
	$strTree .= '<li class="collapsable"><div class="hitarea collapsable-hitarea"></div><span class="folder"><a class="treelink">Настройки</a></span>';
	$strTree .= '<ul>';
	$strTree .= '<span class="file"><a class="treelink" onclick="doajaxContent(\'settings=user\', this)">Пользователи</a></span>';
	$strTree .= '</ul>';
	$strTree .= '</li>';
	$strTree .= '</ul>';
	
	return $strTree;
}

function getTreeBranch($data, $httpHandlerPath)
{
	try
	{
		global $engineConfig, $request;
		
		$strTree = '';
//echo '<pre>'; print_r($data); echo '</pre>';
		foreach ($data as $row)
		{
			if (!FTArrayUtils::checkData($row))
			{
				FTException::saveEx(new Exception('No branch row'));
				continue;
			}

			$bHasChilds = FTArrayUtils::checkData(@$row['childs']);

			$strTree .= ($bHasChilds ? '<li class="closed expandable"><div class="hitarea closed-hitarea expandable-hitarea"></div>' : '<li>');
			//$strTree .= ($bHasChilds ? '<li class="collapsable"><div class="hitarea collapsable-hitarea"></div>' : '<li>');
			$strTree .= '<span class="' . ($bHasChilds ? 'folder' : 'file') . '">';
			$strTree .= '<a class="treelink" onclick="doajaxContent(\'';
			$strTree .= 'app=' . $row['app'];
			
			if (isset($row['_parent_id']))
			$strTree .= '&_parent_id=' . $row['_parent_id'];
			else 
			$strTree .= '&alias=' . $row['alias'];
			
			//if (!$bHasChilds)
			//	$strTree .= '&lang=' . (!(@empty($request->dataWeb->cookie[$engineConfig['cookie']['name_lang']])) ? $request->dataWeb->cookie[$engineConfig['cookie']['name_lang']] : $engineConfig['mvc_data']['lang_default']);
			
			$strTree .= '\', this)">' . $row['name'];
			$strTree .= '</a>';
			/*
			if ($row['app'] == 'comments')
			{
				$strTree .= '<a class="treelink" onclick="doajaxContent(\'';
				$strTree .= 'app=' . $row['app'];
				$strTree .= '&data_object=' . $row['app'].'settings';
				$strTree .= '&_parent_id=' . $row['_parent_id'];
				$strTree .= '\', this)">' . '[*]';
				$strTree .= '</a>';
			}
			*/
			$strTree .= '</span>';
			
			if ($bHasChilds)
			{
				$strTree .= '<ul>';
				$strTree .= getTreeBranch($row['childs'], $httpHandlerPath);
				$strTree .= '</ul>';
			}
			
			$strTree .= '</li>';
		}

		return $strTree;
	}
	catch (Exception $ex)
	{
		throw $ex;
	}
}
