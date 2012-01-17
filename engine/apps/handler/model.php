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
			$ctrl = MvcFactory::create('user', ParamsMvc::ENTITY_CONTROLLER);
			$req = new ActionRequest($request);
			$req->params[Params::OPERATION_NAME] = Params::OPERATION_USER_LOGIN;
			$ctrl->run($req, $response);

			return $ctrl->config['message']['LOGIN_OK']['name_ru'];
		}
		catch (Exception $ex)
		{
			FTException::saveEx($ex);
			return $this->m_Controller->getErrorEdge() . (isset($ctrl->config['message'][$ex->getMessage()]['name_ru']) ? $ctrl->config['message'][$ex->getMessage()]['name_ru'] : $ex->getMessage()) . $this->m_Controller->getErrorEdge();
		}
	}
	/**
	 * Get authorization data
	 * @return Array - user info or empty array, if already logged out
	 */
	protected function opUserGetSession(ActionRequest & $request, ActionResponse & $response)
	{
		try
		{
			$ctrl = MvcFactory::create('user', ParamsMvc::ENTITY_CONTROLLER);
			$req = new ActionRequest($request);
			$req->params[Params::OPERATION_NAME] = Params::OPERATION_USER_GET_SESSION;

			FTException::throwOnTrue(!FTArrayUtils::checkData($ctrl->run($req, $response)), 'USER_NOT_AUTHORIZED');
		}
		catch (Exception $ex)
		{
			throw $ex;
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
			$ctrl = MvcFactory::create('user', ParamsMvc::ENTITY_CONTROLLER);
			$req = new ActionRequest($request);
			$req->params[Params::OPERATION_NAME] = Params::OPERATION_USER_LOGOUT;
			return $ctrl->run($req, $response);
		}
		catch (Exception $ex)
		{
			FTException::saveEx($ex);
			return isset($ctrl->config['message'][$ex->getMessage()]['name_ru']) ? $ctrl->config['message'][$ex->getMessage()]['name_ru'] : $ex->getMessage();
		}
	}

	/**
	 * Get admin tree menu
	 * @return String - html markup
	 */
	protected function opHandlerGetTree(ActionRequest & $request, ActionResponse & $response)
	{
		try
		{
			// Check auth
			$this->opUserGetSession($request, $response);

			return $this->getTree($request, $response);
		}
		catch (Exception $ex)
		{
			throw $ex;
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
		$strTree .= '<div ' . $strButtonStyle . '><a onclick="doajaxContent(\'object_app=container&object_operation=new\', \'is_skip\')" alt="Add container" title="Add container"><img src="/admin/images/tree_add_folder.png" border="0" /></a></div>';
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
		$strTree .= '<span class="file"><a class="treelink" onclick="doajaxContent(\'object_app=settings&object_operation=get_user\', this)">Пользователи</a></span>';
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
				$strTree .= 'object_app=' . $row['app'];
				$strTree .= '&object_alias=' . $row['alias'];
				$strTree .= '&object_operation=get';

				//				$ctrl = MvcFactory::create($row['app'], ParamsMvc::ENTITY_CONTROLLER);
				//				if (isset($ctrl->config[ParamsConfig::OBJECT_ATTACH_ENTITY]))
				//				{
				//					
				//				}
				//				else
				//				{
				//					$strTree .= 'object_app=' . $row['app'];
				//					$strTree .= '&object_alias=' . $row['alias'];
				//					$strTree .= '&object_operation=get';
				//				}

				//				if (isset($row['_parent_id']))
				//					$strTree .= '&_parent_id=' . $row['_parent_id'];
				//				else
				//					$strTree .= '&alias=' . $row['alias'];

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

				$ctrl = MvcFactory::create($row['app'], ParamsMvc::ENTITY_CONTROLLER);
				if (isset($ctrl->config[ParamsConfig::OBJECT_ATTACH_ENTITY]))
				{
					$strTree .= '<div style="float:right; display:inline;">';
					$strTree .= '<a class="treelink" onclick="doajaxContent(\'';
					$strTree .= 'object_app=' . $row['app'];
					$strTree .= '&object_alias=' . $row['alias'];
					$strTree .= '&object_operation=get_settings';
					$strTree .= '\', this)" title="Настройки">' . '<img src="images/tree_settings.png" border="0" style="height:12px;" />' . '</a>';
					$strTree .= '</div>';

					$strTree .= '<div style="float:right; display:inline;">';
					$strTree .= '<a class="treelink" onclick="doajaxContent(\'';
					$strTree .= 'object_app=' . $row['app'];
					$strTree .= '&object_alias=' . $row['alias'];
					$strTree .= '&object_operation=new';
					$strTree .= '\', this)" title="Добавить">' . '<img src="images/tree_add_file_16x16.png" border="0" style="height:12px;" />' . '</a>';
					$strTree .= '</div>';
				}

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

	protected function opContainerGet(ActionRequest & $request, ActionResponse & $response)
	{
		try
		{
			$oAlias = @$request->dataWeb->request['object_alias'];
			FTException::throwOnTrue(empty($oAlias), 'No alias');

			$ctrl = MvcFactory::create('container', ParamsMvc::ENTITY_CONTROLLER);
			$req = new ActionRequest($request);
			$req->params[Params::ALIAS] = $oAlias;
			$req->params[Params::OPERATION_NAME] = Params::OPERATION_GET_BY_ALIAS;
			$data = $ctrl->run($req, $response);

			FTException::throwOnTrue(!FTArrayUtils::checkData(@$data[0]), 'No data');

			// Html form
			$reqForm = new ActionRequest($request);
			$reqForm->params[Params::DATA] = $data[0];
			$reqForm->params[ParamsMvc::ENTITY_CONTROLLER] = $ctrl;
			$reqForm->params[Params::OPERATION_NAME] = Params::OPERATION_UPDATE;
			return $this->opGetDefaultForm($reqForm, $response);
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}
	protected function opContainerNew(ActionRequest & $request, ActionResponse & $response)
	{
		try
		{
			$ctrl = MvcFactory::create('container', ParamsMvc::ENTITY_CONTROLLER);

			// Fill values
			$data = array();
			if (FTArrayUtils::checkData(@$ctrl->config['editor'][ParamsConfig::EDITOR_DEFAULT]['fields']))
				foreach ($ctrl->config['editor'][ParamsConfig::EDITOR_DEFAULT]['fields'] as $confKey => $confValue)
					if (isset($confValue['default_value']))
						$data[$confKey] = $confValue['default_value'];
					else
						$data[$confKey] = '';

			// Hack: ctrl is singleton
			// Change editor params
			$ctrl->config['editor'][ParamsConfig::EDITOR_DEFAULT]['fields']['alias']['is_readonly'] = '0';

			// Html form
			$reqForm = new ActionRequest($request);
			$reqForm->params[Params::DATA] = $data;
			$reqForm->params[ParamsMvc::ENTITY_CONTROLLER] = $ctrl;
			$reqForm->params[Params::OPERATION_NAME] = Params::OPERATION_ADD;
			return $this->opGetDefaultForm($reqForm, $response);
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}
	protected function opContainerAdd(ActionRequest & $request, ActionResponse & $response)
	{
		try
		{
			FTException::throwOnTrue(@empty($request->dataWeb->request['form_submitted']), 'Only http req-s allowed');

			$ctrl = MvcFactory::create('container', ParamsMvc::ENTITY_CONTROLLER);

			// Form data to process
			$dataDecoded = array();

			$formResultMessageColor = 'green';
			$formResultMessageText = 'Данные сохранены успешно';
			$formOperation = Params::OPERATION_UPDATE;

			try
			{
				// Prepare data
				$dataDecoded = $this->prepareHttpData($request->dataWeb->request, $ctrl);

				// Check obligatory fields
				$this->checkObligatoryFields($dataDecoded, $ctrl);

				$req = new ActionRequest($request);
				$req->params[Params::OPERATION_NAME] = Params::OPERATION_ADD;
				$req->params[Params::DATA] = $dataDecoded;
				$dataResult = $ctrl->run($req, $response);

				FTException::throwOnTrue(!FTArrayUtils::checkData($dataResult), 'No record');

				// Set alias
				$request->params['object_alias'] = $dataResult[0]['alias'];
			}
			catch (Exception $ex2)
			{
				$formResultMessageColor = 'red';
				$formResultMessageText = 'Ошибка при сохранении данных:<div>' . $ex2->getMessage() . '</div>';
				$formOperation = Params::OPERATION_ADD;
			}

			// Html form
			$reqForm = new ActionRequest($request);
			$reqForm->params[Params::DATA] = $dataDecoded;
			$reqForm->params[ParamsMvc::ENTITY_CONTROLLER] = $ctrl;
			$reqForm->params[Params::OPERATION_NAME] = $formOperation;
			$reqForm->params['form_result'] = '<div style="color:' . $formResultMessageColor . '; text-align:center; border:1px dotted ' . $formResultMessageColor . '; padding:3px;">' . $formResultMessageText . '</div>';
			return $this->opGetDefaultForm($reqForm, $response);
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}
	protected function opContainerUpdate(ActionRequest & $request, ActionResponse & $response)
	{
		try
		{
			FTException::throwOnTrue(@empty($request->dataWeb->request['form_submitted']), 'Only http req-s allowed');
			FTException::throwOnTrue(@empty($request->params['object_alias']), 'No alias');

			$ctrl = MvcFactory::create('container', ParamsMvc::ENTITY_CONTROLLER);

			// Form data to process
			$dataDecoded = array();

			$formResultMessageColor = 'green';
			$formResultMessageText = 'Данные сохранены успешно';

			try
			{
				// Prepare data
				$dataDecoded = $this->prepareHttpData($request->dataWeb->request, $ctrl);

				// Check obligatory fields
				$this->checkObligatoryFields($dataDecoded, $ctrl);

				$req = new ActionRequest($request);
				$req->params[Params::OPERATION_NAME] = Params::OPERATION_UPDATE;
				$req->params[ParamsSql::RESTRICTION] = 'alias=\'' . $request->dataWeb->request['object_alias'] . '\'';
				$req->params[Params::DATA] = $dataDecoded;
				$dataResult = $ctrl->run($req, $response);

				FTException::throwOnTrue(!FTArrayUtils::checkData($dataResult), 'No record');
			}
			catch (Exception $ex2)
			{
				FTException::saveEx($ex2);

				$formResultMessageColor = 'red';
				$formResultMessageText = 'Ошибка при сохранении данных:<div>' . $ex2->getMessage() . '</div>';
			}

			// Html form
			$reqForm = new ActionRequest($request);
			$reqForm->params[Params::DATA] = $dataDecoded;
			$reqForm->params[ParamsMvc::ENTITY_CONTROLLER] = $ctrl;
			$reqForm->params[Params::OPERATION_NAME] = Params::OPERATION_UPDATE;
			$reqForm->params['form_result'] = '<div style="color:' . $formResultMessageColor . '; text-align:center; border:1px dotted ' . $formResultMessageColor . '; padding:3px;">' . $formResultMessageText . '</div>';
			return $this->opGetDefaultForm($reqForm, $response);
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}

	protected function opStaticGet(ActionRequest & $request, ActionResponse & $response)
	{
		try
		{
			$oAlias = @$request->dataWeb->request['object_alias'];
			FTException::throwOnTrue(empty($oAlias), 'No alias');

			global $engineConfig;

			// Process 4 lang
			// 1 - get by alias and lang
			// 2 - if no => create default record

			$ctrl = MvcFactory::create('static', ParamsMvc::ENTITY_CONTROLLER);
			$req = new ActionRequest($request);
			$req->params[Params::ALIAS] = $oAlias;
			$req->params[Params::OPERATION_NAME] = Params::OPERATION_GET_BY_ALIAS;

			// Add lang restriction
			$req->params[ParamsSql::RESTRICTION] = 'lang=:lang';
			$req->params[ParamsSql::RESTRICTION_DATA][':lang'] = $this->getLang();

			$data = $ctrl->run($req, $response);

			//FTException::throwOnTrue(!FTArrayUtils::checkData(@$data[0]), 'No data');

			if (!FTArrayUtils::checkData($data))
			{
				// Get default data
				$dataForAdd = $this->fillDefaultValues($ctrl, ParamsConfig::EDITOR_DEFAULT);

				// Add some values
				$dataForAdd['alias'] = $oAlias;
				$dataForAdd['lang'] = $this->getLang();

				// Add record
				$reqAdd = new ActionRequest($request);
				$reqAdd->params[Params::OPERATION_NAME] = Params::OPERATION_ADD;
				$reqAdd->params[Params::DATA] = $dataForAdd;
				$dataAdd = $ctrl->run($reqAdd, $response);

				// Check
				FTException::throwOnTrue(!FTArrayUtils::checkData($dataAdd), 'Cannot add app.alias: ' . $request->dataWeb->request['object_app'] . '.' . $oAlias);

				// Put data
				$data[0] = $dataAdd[0];
			}

			// Html form
			$reqForm = new ActionRequest($request);
			$reqForm->params[Params::DATA] = $data[0];
			$reqForm->params[ParamsMvc::ENTITY_CONTROLLER] = $ctrl;
			$reqForm->params[Params::OPERATION_NAME] = Params::OPERATION_UPDATE;
			return $this->opGetDefaultForm($reqForm, $response);
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}
	protected function opStaticUpdate(ActionRequest & $request, ActionResponse & $response)
	{
		try
		{
			FTException::throwOnTrue(@empty($request->dataWeb->request['form_submitted']), 'Only http req-s allowed');
			FTException::throwOnTrue(@empty($request->params['object_alias']), 'No alias');

			$ctrl = MvcFactory::create('static', ParamsMvc::ENTITY_CONTROLLER);

			// Form data to process
			$dataDecoded = array();

			$formResultMessageColor = 'green';
			$formResultMessageText = 'Данные сохранены успешно';

			try
			{
				// Prepare data
				$dataDecoded = $this->prepareHttpData($request->dataWeb->request, $ctrl);

				// Check obligatory fields
				$this->checkObligatoryFields($dataDecoded, $ctrl);

				$req = new ActionRequest($request);
				$req->params[Params::OPERATION_NAME] = Params::OPERATION_UPDATE;
				$req->params[ParamsSql::RESTRICTION] = 'alias=\'' . $request->dataWeb->request['object_alias'] . '\'';

				// Add lang restriction
				$req->params[ParamsSql::RESTRICTION] .= ' AND lang=' . $this->getLang(TRUE);

				$req->params[Params::DATA] = $dataDecoded;
				$dataResult = $ctrl->run($req, $response);

				FTException::throwOnTrue(!FTArrayUtils::checkData($dataResult), 'No record');
			}
			catch (Exception $ex2)
			{
				$formResultMessageColor = 'red';
				$formResultMessageText = 'Ошибка при сохранении данных:<div>' . $ex2->getMessage() . '</div>';
			}

			// Html form
			$reqForm = new ActionRequest($request);
			$reqForm->params[Params::DATA] = $dataDecoded;
			$reqForm->params[ParamsMvc::ENTITY_CONTROLLER] = $ctrl;
			$reqForm->params[Params::OPERATION_NAME] = Params::OPERATION_UPDATE;
			$reqForm->params['form_result'] = '<div style="color:' . $formResultMessageColor . '; text-align:center; border:1px dotted ' . $formResultMessageColor . '; padding:3px;">' . $formResultMessageText . '</div>';
			return $this->opGetDefaultForm($reqForm, $response);
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}

	protected function opGetDefaultForm(ActionRequest & $request, ActionResponse & $response)
	{
		try
		{
			FTException::throwOnTrue(@empty($request->params['object_app']), 'No app');
			FTException::throwOnTrue(@empty($request->params[Params::OPERATION_NAME]), 'No ' . Params::OPERATION_NAME);

			// Result html
			$res = '';

			// Get controller
			$ctrl = $request->params[ParamsMvc::ENTITY_CONTROLLER];

			// Get data
			$data = @$request->params[Params::DATA];
			if (FTArrayUtils::checkData($data, 0))
			{
				// Get form html
				$res .= '<form id="simple_form" action="' . $this->m_Controller->config['web_path'] . '" onsubmit="formSubmit(this); return false;" method="POST">';
				$res .= '<table border="0" cellpadding="0" cellspacing="0" style="width:95%; font:14px Verdana;"><tbody>';

				$editorID = isset($request->params[ParamsConfig::EDITOR_ID]) ? $request->params[ParamsConfig::EDITOR_ID] : ParamsConfig::EDITOR_DEFAULT;

				$strTextareaIds = '';
				foreach ($ctrl->config['editor'][$editorID]['fields'] as $k => $v)
				{
					if (@$v['is_skip'])
						continue;

					$strIsNotNull = isset($v['is_null']) && !$v['is_null'] ? '<div style="display:inline; padding:3px; color:red; cursor:help;" title="Обязательно для заполнения" alt="Обязательно для заполнения">*</div>' : '';

					$strStyleReadOnly = isset($v['is_readonly']) && $v['is_readonly'] ? 'background-color:#E8E8E8;' : '';

					$res .= '<tr>';
					if (!@empty($v['name_ru']) && (!isset($v['is_hidden']) || !$v['is_hidden']))
						$res .= '<td style="width:22%;">' . $v['name_ru'] . $strIsNotNull . '</td>' . '<td style="width:15px; text-align:center;">:</td>';
					else
						$res .= '<td></td><td></td>';

					$res .= '<td style="padding:3px;">';
					if (isset($v['type']))
						switch ($v['type'])
						{
							case 'int':
							case 'tinyint':
							case 'varchar':
								{
									// Add combobox data
									if (@$v['is_bool'] && (!isset($v['is_hidden']) || !$v['is_hidden']))
									{
										// Bool field
										$res .= '<select class="ft_control" id="' . $k . '" name="' . $k . '">';
										foreach (array('1' => 'Да', '0' => 'Нет') as $boolKey => $boolValue)
										{
											$isSelected = '';
											if ($boolKey == (isset($data[$k]) ? $data[$k] : ''))
												$isSelected = ' selected';
											$res .= '<option ' . $isSelected . ' value="' . $boolKey . '">' . $boolValue . '</option>';
										}
										$res .= '</select>';
									}
									else
										$res .= '<input class="ft_control" type="' . (@$v['is_hidden'] ? 'hidden' : 'text') . '" id="' . $k . '" name="' . $k . '" value="' . (isset($data[$k]) && !(isset($v['is_hide_value']) && $v['is_hide_value']) ? $data[$k] : '') . '" size="' . $v['length'] . '" maxlength="' . $v['length'] . '"' . (isset($v['is_readonly']) && $v['is_readonly'] ? ' readonly="readonly"' : '') . ' style="width:100%;' . $strStyleReadOnly . '" />';
								}
								break;
							case 'text':
								$res .= '<textarea class="ft_control" rows="17" id="' . $k . '" name="' . $k . '" maxlength="1000000"' . (@$v['is_readonly'] ? ' readonly="readonly"' : '') . ' style="width:100%;' . $strStyleReadOnly . '">' . (isset($data[$k]) ? $data[$k] : '') . '</textarea>';
								if (@$v['rich_editor'])
								{
									$res .= '<script type="text/javascript">bindEditorFull(\'' . $k . '\');</script>';
									$strTextareaIds .= (!empty($strTextareaIds) ? ',' : '') . '\'' . $k . '\'';
								}
								break;
							case 'datetime':
								{
									$res .= '<input class="ft_control" type="' . (@$v['is_hidden'] ? 'hidden' : 'text') . '" id="' . $k . '" name="' . $k . '" value="' . (isset($data[$k]) && !(isset($v['is_hide_value']) && $v['is_hide_value']) ? $data[$k] : '') . '" size="19" maxlength="19"' . (isset($v['is_readonly']) && $v['is_readonly'] ? ' readonly="readonly"' : '') . ' style="width:20%;' . $strStyleReadOnly . '" />'.' (Формат: "YYYY-MM-DD hh:mm:ss")';
								}
								break;
							default:
								throw new Exception('Not implemented editor field type: '.$v['type']);
								break;
						}
					$res .= '</td>';
					$res .= '</tr>';
				}

				$res .= '<tr style="height:40px;"><td></td><td></td><td><input class="ft_control" type="submit" name="form_submitted" value="Сохранить"/></td></tr>';
				$res .= '</tbody></table>';

				$res .= '<input type="hidden" name="object_app" value="' . $request->params['object_app'] . '" />';
				$res .= '<input type="hidden" name="object_operation" value="' . $request->params[Params::OPERATION_NAME] . '" />';

				$res .= '<input type="hidden" name="called_from_operation" value="' . $request->params['object_operation'] . '" />';

				if (isset($request->params['object_alias']))
					$res .= '<input type="hidden" name="object_alias" value="' . $request->params['object_alias'] . '" />';

				// Set form result
				if (!@empty($request->params['form_result']))
					$res .= '<div id="form_result">' . $request->params['form_result'] . '</div>';

				$res .= '</form>';

				// Change form onSubmit
				$res = str_ireplace('onsubmit="formSubmit(this', 'onsubmit="formSubmit(this, [' . $strTextareaIds . ']', $res);
			}
			else
				$res .= '<div style="line-height:60px; text-align:center; vertical-align:middle;">' . 'Нет данных' . '</div>';

			return $res;
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}

	protected function opGetListForm(ActionRequest & $request, ActionResponse & $response)
	{
		try
		{
			$appName = @$request->params['object_app'];
			FTException::throwOnTrue(empty($appName), 'No app');

			$ctrl = $request->params[ParamsMvc::ENTITY_CONTROLLER];
			FTException::throwOnTrue(is_null($ctrl), 'No ' . ParamsMvc::ENTITY_CONTROLLER);

			// Result html
			$res = '';

			// Get data
			$data = @$request->params[Params::DATA];
			if (FTArrayUtils::checkData($data))
			{
				// Get editor for form
				//$editorID = isset($formParams['editor']) ? $formParams['editor'] : ParamsConfig::EDITOR_LIST;
				$editorID = ParamsConfig::EDITOR_LIST;

				// Get app config
				$config = $ctrl->config;
				FTException::throwOnTrue(!FTArrayUtils::checkData(@$config['editor'][$editorID]['fields']), 'No editor: ' . $editorID);

				$res .= '<div>';
				$res .= '<table style="width:100%; border:1px solid #D4D4D4;" cellspacing="0" cellpadding="0">';

				// Header
				$res .= '<tr style="background-color:#EFEFEF; text-align:center;">';
				foreach ($config['editor'][$editorID]['fields'] as $k => $v)
				{
					$res .= '<td style="' . (isset($v['style']) ? $v['style'] : '') . ' padding:2px;">' . $v['name_ru'] . '</td>';
				}
				$res .= '</tr>';

				//echo '<pre>'; print_r($data); echo '</pre>';

				// Body
				foreach ($data as $row)
				{
					$res .= '<tr onclick="doajaxContent(\'';

					//					if ($appName == 'user')
					//						$res .= 'settings=user';
					//					else
					$res .= 'object_app=' . $appName;

					$res .= '&' . ParamsConfig::OBJECT_ATTACH_ENTITY . '_id=' . $row['_id'];
					$res .= '&object_operation=get_item_by_id';

					$res .= '\', \'is_not_change_bg\')">';
					foreach ($config['editor'][$editorID]['fields'] as $k => $v)
					{
						$res .= '<td style="' . (isset($v['style']) ? $v['style'] : '') . 'border:1px solid #D4D4D4; padding:2px; cursor:pointer;">';
						$res .= $row[$k];
						$res .= '</td>';
					}
					$res .= '</tr>';
				}

				$res .= '</table>';
				$res .= '</div>';
			}
			else
			{
				$data = is_array($data) ? 'Нет данных' : $data;
				$res .= '<div style="line-height:60px; text-align:center; vertical-align:middle;">' . $data . '</div>';
			}

			return $res;
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}

	protected function opNewsGetSettings(ActionRequest & $request, ActionResponse & $response)
	{
		try
		{
			$oAlias = @$request->dataWeb->request['object_alias'];
			FTException::throwOnTrue(empty($oAlias), 'No alias');

			global $engineConfig;

			// Process 4 lang
			// 1 - get by alias and lang
			// 2 - if no => create default record

			// Check auth
			$this->opUserGetSession($request, $response);

			$ctrl = MvcFactory::create('news', ParamsMvc::ENTITY_CONTROLLER);
			$req = new ActionRequest($request);
			$req->params[Params::ALIAS] = $oAlias;
			$req->params[Params::OPERATION_NAME] = Params::OPERATION_GET_BY_ALIAS;

			// Add lang restriction
			$req->params[ParamsSql::RESTRICTION] = 'lang=:lang';
			$req->params[ParamsSql::RESTRICTION_DATA][':lang'] = $this->getLang();

			$data = $ctrl->run($req, $response);

			//FTException::throwOnTrue(!FTArrayUtils::checkData(@$data[0]), 'No data');

			if (!FTArrayUtils::checkData($data))
			{
				// Get default data
				$dataForAdd = $this->fillDefaultValues($ctrl, ParamsConfig::EDITOR_DEFAULT);

				// Add some values
				$dataForAdd['alias'] = $oAlias;
				$dataForAdd['lang'] = $this->getLang();

				// Add record
				$reqAdd = new ActionRequest($request);
				$reqAdd->params[Params::OPERATION_NAME] = Params::OPERATION_ADD;
				$reqAdd->params[Params::DATA] = $dataForAdd;
				$dataAdd = $ctrl->run($reqAdd, $response);

				// Check
				FTException::throwOnTrue(!FTArrayUtils::checkData($dataAdd), 'Cannot add app.alias: ' . $request->dataWeb->request['object_app'] . '.' . $oAlias);

				// Put data
				$data[0] = $dataAdd[0];
			}

			// Html form
			$reqForm = new ActionRequest($request);
			$reqForm->params[Params::DATA] = $data[0];
			$reqForm->params[ParamsMvc::ENTITY_CONTROLLER] = $ctrl;
			$reqForm->params[Params::OPERATION_NAME] = Params::OPERATION_UPDATE;
			return $this->opGetDefaultForm($reqForm, $response);

			return '-';
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}
	protected function opNewsUpdate(ActionRequest & $request, ActionResponse & $response)
	{
		try
		{
			$invokedOp = @$request->dataWeb->request['called_from_operation'];
			FTException::throwOnTrue(empty($invokedOp), 'No called_from_operation');

			switch ($invokedOp)
			{
				case 'get_settings':
					return $this->opNewsUpdateSettings($request, $response);
					break;
				default:
					return $this->opNewsUpdateData($request, $response);
					break;
			}
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}
	protected function opNewsUpdateSettings(ActionRequest & $request, ActionResponse & $response)
	{
		try
		{
			FTException::throwOnTrue(@empty($request->dataWeb->request['form_submitted']), 'Only http req-s allowed');
			FTException::throwOnTrue(@empty($request->params['object_alias']), 'No alias');

			$ctrl = MvcFactory::create('news', ParamsMvc::ENTITY_CONTROLLER);

			// Form data to process
			$dataDecoded = array();

			$formResultMessageColor = 'green';
			$formResultMessageText = 'Данные сохранены успешно';

			try
			{
				// Prepare data
				$dataDecoded = $this->prepareHttpData($request->dataWeb->request, $ctrl);

				// Check obligatory fields
				$this->checkObligatoryFields($dataDecoded, $ctrl);

				$req = new ActionRequest($request);
				$req->params[Params::OPERATION_NAME] = Params::OPERATION_UPDATE;
				$req->params[ParamsSql::RESTRICTION] = 'alias=\'' . $request->params['object_alias'] . '\'';

				// Add lang restriction
				$req->params[ParamsSql::RESTRICTION] .= ' AND lang=' . $this->getLang(TRUE);

				$req->params[Params::DATA] = $dataDecoded;
				$dataResult = $ctrl->run($req, $response);

				FTException::throwOnTrue(!FTArrayUtils::checkData($dataResult), 'No record');
			}
			catch (Exception $ex2)
			{
				$formResultMessageColor = 'red';
				$formResultMessageText = 'Ошибка при сохранении данных:<div>' . $ex2->getMessage() . '</div>';
			}

			// Html form
			$reqForm = new ActionRequest($request);
			$reqForm->params[Params::DATA] = $dataDecoded;
			$reqForm->params[ParamsMvc::ENTITY_CONTROLLER] = $ctrl;
			$reqForm->params[Params::OPERATION_NAME] = Params::OPERATION_UPDATE;
			$reqForm->params['form_result'] = '<div style="color:' . $formResultMessageColor . '; text-align:center; border:1px dotted ' . $formResultMessageColor . '; padding:3px;">' . $formResultMessageText . '</div>';
			$reqForm->params['object_operation'] = @$request->dataWeb->request['called_from_operation'];
			return $this->opGetDefaultForm($reqForm, $response);
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}
	protected function opNewsUpdateData(ActionRequest & $request, ActionResponse & $response)
	{
		try
		{
			FTException::throwOnTrue(@empty($request->dataWeb->request['form_submitted']), 'Only http req-s allowed');
			FTException::throwOnTrue(@empty($request->dataWeb->request['_id']), 'No ID');

			$ctrl = MvcFactory::create('news', ParamsMvc::ENTITY_CONTROLLER);

			// Form data to process
			$dataDecoded = array();

			$formResultMessageColor = 'green';
			$formResultMessageText = 'Данные сохранены успешно';

			try
			{
				// Prepare data
				$dataDecoded = $this->prepareHttpData($request->dataWeb->request, $ctrl, $ctrl->config[ParamsConfig::OBJECT_ATTACH_ENTITY]);

				// Set date
				if (empty($dataDecoded['date_pub']))
					$dataDecoded['date_pub'] = date('Y-m-d H:i:s', time());
				
				// Check obligatory fields
				$this->checkObligatoryFields($dataDecoded, $ctrl);

				$req = new ActionRequest($request);
				$req->params[Params::OPERATION_NAME] = Params::OPERATION_UPDATE;
				$req->params[ParamsSql::RESTRICTION] = '_id=' . $request->dataWeb->request['_id'] . '';
				$req->params[Params::DATA] = $dataDecoded;
				$dataResult = $ctrl->run($req, $response);

				FTException::throwOnTrue(!FTArrayUtils::checkData($dataResult), 'No record');

				$dataDecoded['_id'] = $dataResult[0]['_id'];
			}
			catch (Exception $ex2)
			{
				$formResultMessageColor = 'red';
				$formResultMessageText = 'Ошибка при сохранении данных:<div>' . $ex2->getMessage() . '</div>';
			}

			// Html form
			$reqForm = new ActionRequest($request);
			$reqForm->params[Params::DATA] = $dataDecoded;
			$reqForm->params[ParamsMvc::ENTITY_CONTROLLER] = $ctrl;
			$reqForm->params[Params::OPERATION_NAME] = Params::OPERATION_UPDATE;
			$reqForm->params[ParamsConfig::EDITOR_ID] = $ctrl->config[ParamsConfig::OBJECT_ATTACH_ENTITY];
			$reqForm->params['form_result'] = '<div style="color:' . $formResultMessageColor . '; text-align:center; border:1px dotted ' . $formResultMessageColor . '; padding:3px;">' . $formResultMessageText . '</div>';
			$reqForm->params['object_operation'] = @$request->dataWeb->request['called_from_operation'];
			return $this->opGetDefaultForm($reqForm, $response);
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}
	protected function opNewsGet(ActionRequest & $request, ActionResponse & $response)
	{
		try
		{
			$oAlias = @$request->dataWeb->request['object_alias'];
			FTException::throwOnTrue(empty($oAlias), 'No alias');

			$ctrl = MvcFactory::create('news', ParamsMvc::ENTITY_CONTROLLER);

			$req = new ActionRequest($request);
			$req->params[Params::ALIAS] = $oAlias;
			$req->params[Params::OPERATION_NAME] = Params::OPERATION_GET;

			// Add lang restriction
			$req->params[ParamsSql::RESTRICTION] = 'lang=:lang';
			$req->params[ParamsSql::RESTRICTION_DATA][':lang'] = $this->getLang();
			$req->params[ParamsSql::ORDER_BY] = '_id DESC';

			$data = $ctrl->run($req, $response);

			//			echo '<pre>';
			//			print_r($data);
			//			echo '</pre>';
			//			die();

			FTException::throwOnTrue(!FTArrayUtils::checkData(@$data[0]), 'No data');

			// Html form
			$reqForm = new ActionRequest($request);
			$reqForm->params[Params::DATA] = $data[0][ParamsConfig::OBJECT_ATTACH_ENTITY];
			$reqForm->params[ParamsMvc::ENTITY_CONTROLLER] = $ctrl;
			//$reqForm->params[Params::OPERATION_NAME] = Params::OPERATION_UPDATE;

			return $this->opGetListForm($reqForm, $response);
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}
	protected function opNewsGetItemById(ActionRequest & $request, ActionResponse & $response)
	{
		try
		{
			$ctrl = MvcFactory::create('news', ParamsMvc::ENTITY_CONTROLLER);

			$req = new ActionRequest($request);
			$req->params[Params::OPERATION_NAME] = 'get_item_by_id';
			$req->params[Params::ID] = @$request->dataWeb->request[ParamsConfig::OBJECT_ATTACH_ENTITY . '_id'];
			$data = $ctrl->run($req, $response);

			// Html form
			$reqForm = new ActionRequest($request);
			$reqForm->params[Params::DATA] = $data[0][ParamsConfig::OBJECT_ATTACH_ENTITY][0];
			$reqForm->params[ParamsMvc::ENTITY_CONTROLLER] = $ctrl;
			$reqForm->params[ParamsConfig::EDITOR_ID] = $ctrl->config[ParamsConfig::OBJECT_ATTACH_ENTITY];
			$reqForm->params[Params::OPERATION_NAME] = Params::OPERATION_UPDATE;
			return $this->opGetDefaultForm($reqForm, $response);
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}
	protected function opNewsNew(ActionRequest & $request, ActionResponse & $response)
	{
		try
		{
			$ctrl = MvcFactory::create('news', ParamsMvc::ENTITY_CONTROLLER);

			// Get default data
			$dataForAdd = $this->fillDefaultValues($ctrl, $ctrl->config[ParamsConfig::OBJECT_ATTACH_ENTITY]);

			// Add some values
			//$dataForAdd['alias'] = $oAlias;
			//			$dataForAdd['lang'] = $this->getLang();
			//
			//			// Add record
			//			$reqAdd = new ActionRequest($request);
			//			$reqAdd->params[Params::OPERATION_NAME] = Params::OPERATION_ADD;
			//			$reqAdd->params[Params::DATA] = $dataForAdd;
			//			//$reqAdd->dataWeb->request['called_from_operation'] = 'new_item';
			//			$dataAdd = $ctrl->run($reqAdd, $response);

			// Html form
			$reqForm = new ActionRequest($request);
			$reqForm->params[Params::DATA] = $dataForAdd;
			$reqForm->params[ParamsMvc::ENTITY_CONTROLLER] = $ctrl;
			$reqForm->params[Params::OPERATION_NAME] = Params::OPERATION_ADD;
			$reqForm->params[ParamsConfig::EDITOR_ID] = $ctrl->config[ParamsConfig::OBJECT_ATTACH_ENTITY];
			return $this->opGetDefaultForm($reqForm, $response);
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}
	protected function opNewsAdd(ActionRequest & $request, ActionResponse & $response)
	{
		try
		{
			/*
			 FTException::throwOnTrue(@empty($request->dataWeb->request['form_submitted']), 'Only http req-s allowed');
						
			 $ctrl = MvcFactory::create('news', ParamsMvc::ENTITY_CONTROLLER);
			
			 // Get default data
			 $dataForAdd = $this->fillDefaultValues($ctrl, $ctrl->config[ParamsConfig::OBJECT_ATTACH_ENTITY]);
			
			 // Add some values
			 $dataForAdd['lang'] = $this->getLang();
			
			 // Add record
			 $reqAdd = new ActionRequest($request);
			 $reqAdd->params[Params::OPERATION_NAME] = Params::OPERATION_ADD;
			 $reqAdd->params[Params::DATA] = $dataForAdd;
			 //$reqAdd->dataWeb->request['called_from_operation'] = 'new_item';
			 $dataAdd = $ctrl->run($reqAdd, $response);
						
			 // Html form
			 $reqForm = new ActionRequest($request);
			 $reqForm->params[Params::DATA] = $dataForAdd;
			 $reqForm->params[ParamsMvc::ENTITY_CONTROLLER] = $ctrl;
			 $reqForm->params[Params::OPERATION_NAME] = Params::OPERATION_ADD;
			 $reqForm->params[ParamsConfig::EDITOR_ID] = $ctrl->config[ParamsConfig::OBJECT_ATTACH_ENTITY];
			 return $this->opGetDefaultForm($reqForm, $response);
			 */

			$oAlias = @$request->dataWeb->request['object_alias'];
			FTException::throwOnTrue(empty($oAlias), 'No alias');

			FTException::throwOnTrue(@empty($request->dataWeb->request['form_submitted']), 'Only http req-s allowed');

			$ctrl = MvcFactory::create('news', ParamsMvc::ENTITY_CONTROLLER);

			// Get parent
			$reqParent = new ActionRequest($request);
			$reqParent->params[Params::ALIAS] = $oAlias;
			$reqParent->params[Params::OPERATION_NAME] = Params::OPERATION_GET_BY_ALIAS;
			$reqParent->params[ParamsSql::RESTRICTION] = 'lang=:lang';
			$reqParent->params[ParamsSql::RESTRICTION_DATA][':lang'] = $this->getLang();
			$dataParent = $ctrl->run($reqParent, $response);
			FTException::throwOnTrue(!FTArrayUtils::checkData(@$dataParent), 'No parent record');

			// Form data to process
			$dataDecoded = array();

			$formResultMessageColor = 'green';
			$formResultMessageText = 'Данные сохранены успешно';
			$formOperation = Params::OPERATION_UPDATE;

			try
			{
				// Prepare data
				$dataDecoded = $this->prepareHttpData($request->dataWeb->request, $ctrl, $ctrl->config[ParamsConfig::OBJECT_ATTACH_ENTITY]);
				
				// Set date
				if (empty($dataDecoded['date_pub']))
					$dataDecoded['date_pub'] = date('Y-m-d H:i:s', time());
					
				// Check obligatory fields
				$this->checkObligatoryFields($dataDecoded, $ctrl, $ctrl->config[ParamsConfig::OBJECT_ATTACH_ENTITY]);

				$req = new ActionRequest($request);
				$req->params[Params::OPERATION_NAME] = Params::OPERATION_ADD;
				$req->params[Params::DATA] = $dataDecoded;
				$req->params[Params::DATA]['_parent_id'] = $dataParent[0]['_id'];
				$dataResult = $ctrl->run($req, $response);

				FTException::throwOnTrue(!FTArrayUtils::checkData($dataResult), 'No record');

				// Set alias
				$request->params['object_alias'] = $oAlias;
			}
			catch (Exception $ex2)
			{
				FTException::saveEx($ex2);

				$formResultMessageColor = 'red';
				$formResultMessageText = 'Ошибка при сохранении данных:<div>' . $ex2->getMessage() . '</div>';
				$formOperation = Params::OPERATION_ADD;
			}

			// Html form
			$reqForm = new ActionRequest($request);
			$reqForm->params[Params::DATA] = $dataDecoded;
			$reqForm->params[ParamsMvc::ENTITY_CONTROLLER] = $ctrl;
			$reqForm->params[Params::OPERATION_NAME] = $formOperation;
			$reqForm->params['form_result'] = '<div style="color:' . $formResultMessageColor . '; text-align:center; border:1px dotted ' . $formResultMessageColor . '; padding:3px;">' . $formResultMessageText . '</div>';
			$reqForm->params[ParamsConfig::EDITOR_ID] = $ctrl->config[ParamsConfig::OBJECT_ATTACH_ENTITY];
			//$reqForm->params['object_alias'] = $oAlias;
			$reqForm->params['object_operation'] = 'new';
			return $this->opGetDefaultForm($reqForm, $response);
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}

	private function prepareHttpData($dataInput, $ctrl, $editorID = ParamsConfig::EDITOR_DEFAULT)
	{
		try
		{
			$data = array();

			foreach ($dataInput as $k_encoded => $v_encoded)
			{
				$k = urldecode($k_encoded);
				$v = urldecode($v_encoded);

				if ($k == '_id' || !FTArrayUtils::containsKeyCI($k, $ctrl->config['editor'][$editorID]['fields']))
					continue;
				//if (@$ctrl->config['editor'][$editorID]['fields'][$k]['is_readonly'])
				//	continue;
				if (@$ctrl->config['editor'][$editorID]['fields'][$k]['is_skip'])
					continue;

				// Pack data
				$data[$k] = $v;
			}

			return $data;
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}
	private function checkObligatoryFields($dataInput, $ctrl, $editorID = ParamsConfig::EDITOR_DEFAULT)
	{
		try
		{
			foreach ($dataInput as $k => $v)
				if (isset($ctrl->config['editor'][$editorID]['fields'][$k]['is_null']) && !$ctrl->config['editor'][$editorID]['fields'][$k]['is_null'] && empty($v))
					throw new Exception('Не заполнено поле "' . (!@empty($ctrl->config['editor'][$editorID]['fields'][$k]['name_ru']) ? $ctrl->config['editor'][$editorID]['fields'][$k]['name_ru'] : $k) . '"');
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}
	private function fillDefaultValues($ctrl, $editorID = ParamsConfig::EDITOR_DEFAULT)
	{
		try
		{
			FTException::throwOnTrue(!FTArrayUtils::checkData($ctrl->config['editor'][$editorID]['fields'], 0), 'No editor fields to fill default values');

			$data = array();

			// Fill default values
			if (FTArrayUtils::checkData($ctrl->config['editor'][$editorID]['fields']))
				foreach ($ctrl->config['editor'][$editorID]['fields'] as $confKey => $confValue)
					if (isset($confValue['default_value']))
						$data[$confKey] = $confValue['default_value'];

			return $data;
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}
	private function getLang($bIsQuoted = FALSE)
	{
		try
		{
			global $engineConfig, $request;

			return ($bIsQuoted ? '"' : '') . (!(@empty($request->dataWeb->cookie[$engineConfig['cookie']['name_lang']])) ? $request->dataWeb->cookie[$engineConfig['cookie']['name_lang']] : $engineConfig['mvc_data']['lang_default']) . ($bIsQuoted ? '"' : '');
		}
		catch (Exception $ex)
		{
			throw $ex;
		}

	}
}
