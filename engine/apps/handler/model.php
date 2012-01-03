<?php
require_once dirname(__FILE__) . '/../../inc/cde.inc.php';

class HandlerModel extends BaseModel
{
	/**
	 * Login user by email & password
	 * @return Array - user info or String - message error
	 */
	protected function opUserLogin(ActionRequest & $request, ActionResponse & $response)
	{
		try
		{
			$user = MvcFactory::create('user', ParamsMvc::ENTITY_CONTROLLER);
			$reqLogin = new ActionRequest($request);
			$reqLogin->params[Params::OPERATION_NAME] = Params::OPERATION_USER_LOGIN;
			return $user->run($reqLogin, $response);
		}
		catch (Exception $ex)
		{
			FTException::saveEx($ex);
			return isset($user->config['error'][$ex->getMessage()]['name_ru']) ? $user->config['error'][$ex->getMessage()]['name_ru'] : $ex->getMessage();
		}
	}

	/**
	 * Logout user
	 * @return Array - user info or empty array, if already logged out
	 */
	protected function opUserLogout(ActionRequest & $request, ActionResponse & $response)
	{
		try
		{
			$user = MvcFactory::create('user', ParamsMvc::ENTITY_CONTROLLER);
			$reqLogin = new ActionRequest($request);
			$reqLogin->params[Params::OPERATION_NAME] = Params::OPERATION_USER_LOGOUT;
			return $user->run($reqLogin, $response);
		}
		catch (Exception $ex)
		{
			FTException::saveEx($ex);
			return isset($user->config['error'][$ex->getMessage()]['name_ru']) ? $user->config['error'][$ex->getMessage()]['name_ru'] : $ex->getMessage();
		}
	}

	protected function opHandlerTree(ActionRequest & $request, ActionResponse & $response)
	{
		try
		{
			// Check auth
			FTException::throwOnTrue(!FTArrayUtils::checkData($this->opDoGetSession($request, $response)), 'not_authorized');

			return $this->getTree($request, $response);
		}
		catch (Exception $ex)
		{
			FTException::saveEx($ex);
			return $ex->getMessage();
		}
	}

	/**
	 * Get authorization data
	 * @return Array - user info or empty array, if already logged out
	 */
	protected function opDoGetSession(ActionRequest & $request, ActionResponse & $response)
	{
		try
		{
			$user = MvcFactory::create('user', ParamsMvc::ENTITY_CONTROLLER);
			$reqAuth = new ActionRequest($request);
			$reqAuth->params[Params::OPERATION_NAME] = Params::OPERATION_USER_GET_SESSION;
			return $user->run($reqAuth, $response);
		}
		catch (Exception $ex)
		{
			FTException::saveEx($ex);
			return $ex->getMessage();
		}
	}

	private function getTree(ActionRequest & $request, ActionResponse & $response)
	{
		$httpHandlerPath = $this->m_Controller->config['web_path'];

		$strTree = '';

		// Get tree data
		$controller = MvcFactory::create('container', ParamsMvc::ENTITY_CONTROLLER);
		$req = new ActionRequest($request);
		$req->params[Params::OPERATION_NAME] = 'get_tree';
		$req->params[ParamsMvc::IS_NOT_RENDER] = TRUE;
		$dataTree = $controller->run($req, $response);

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
		$strTree .= $this->getTreeBranch($dataTree, $httpHandlerPath);
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

	private function getTreeBranch($data, $httpHandlerPath)
	{
		try
		{
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
					$strTree .= $this->getTreeBranch($row['childs'], $httpHandlerPath);
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
}
