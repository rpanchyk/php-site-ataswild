<?php

function getForm($appName, $rowId, $data, $dataHidden = array(), $formParams = array())
{
	global $request, $response, $handlerPath;

	if (empty($appName))
		throw new Exception('No app');

	if (!intval($rowId))
		throw new Exception('No ID');

	// Result html
	$res = '';

	if (FTArrayUtils::checkData($data))
	{
		$strOnSubmit = '';

		// Get editor for form
		$editorID = isset($formParams['editor']) ? $formParams['editor'] : 'default';

		// Get app config
		$controller = MvcFactory::create($appName, 'controller');
		$reqGetConfig = new ActionRequest($request);
		$reqGetConfig->params[Params::OPERATION_NAME] = 'get_config';
		$reqGetConfig->params[ParamsMvc::IS_NOT_RENDER] = TRUE;
		$reqGetConfig->params[ParamsMvc::APP_NAME] = $appName;
		$reqGetConfig->params[ParamsConfig::DATA_OBJECT] = isset($dataHidden['data_object']) ? $dataHidden['data_object'] : NULL;
		$reqGetConfig->params[ParamsConfig::EDITOR_ID] = $editorID;
		$config = $controller->run($reqGetConfig, $response);

		// Get form html
		$res .= '<form id="simple_form" action="' . $handlerPath . '" onsubmit="formSubmit(this); return false;" method="' . (isset($formParams) && is_array($formParams) && isset($formParams['method']) && !empty($formParams['method']) ? strtoupper($formParams['method']) : 'POST') . '">';
		$res .= '<table border="0" cellpadding="0" cellspacing="0" style="width:95%; font:14px Verdana;"><tbody>';
		foreach ($config['editor'][$editorID]['fields'] as $k => $v)
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
							if (isset($v['dependency']) && (!isset($v['is_hidden']) || !$v['is_hidden']))
							{
								/*
								 $dependency = $v['dependency'];
																																																																																																																																																																																																							
								 // Check settings
								 if (!FTArrayUtils::checkData($dependency))
								 throw new Exception('No dependency settings');
								 if (!isset($dependency['app']) || empty($dependency['app']))
								 throw new Exception('No dependency app');
								 if (!isset($dependency[ParamsSql::RESTRICTION]) || empty($dependency[ParamsSql::RESTRICTION]))
								 throw new Exception('No dependency restriction');
								 if (!isset($dependency['key_field']) || empty($dependency['key_field']))
								 throw new Exception('No dependency key_field');
								 if (!isset($dependency['value_field']) || empty($dependency['value_field']))
								 throw new Exception('No dependency value_field');
																																																																																																																																																																																																							
								 // Get restriction
								 $depRestriction = $dependency[ParamsSql::RESTRICTION];
								 if (strpos($depRestriction, ':') !== FALSE)
								 {
								 // Regex field name and replace it with $data[$k]
								 throw new Exception('Not implemented complex dependency');
								 }
																																																																																																																																																																																																							
								 // Get dependecy data
								 $dataDependency = $request->db->get('SELECT ' . $dependency['key_field'] . ',' . $dependency['value_field'] . ' FROM t' . ucfirst($dependency['app']) . ' WHERE ' . $depRestriction . ' LIMIT 1000');
								 if (!isset($dataDependency) || !is_array($dataDependency) || !count($dataDependency))
								 throw new Exception('No dependency data');
																																																																																																																																																																																																							
								 // Fill html control
								 $res .= '<select class="ft_control" id="' . $k . '" name="' . $k . '">';
								 foreach ($dataDependency as $dep)
								 {
								 $isSelected = '';
								 if ($dep[$dependency['key_field']] == (isset($data[$k]) ? $data[$k] : ''))
								 $isSelected = ' selected';
								 $res .= '<option ' . $isSelected . ' value="' . $dep[$dependency['key_field']] . '">' . $dep[$dependency['value_field']] . '</option>';
								 }
								 $res .= '</select>';
								 */
							}
							elseif (@$v['is_bool'] && (!isset($v['is_hidden']) || !$v['is_hidden']))
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
							$strOnSubmit .= (!empty($strOnSubmit) ? ',' : '') . '\'' . $k . '\'';
						}
						break;
					default:
						throw new Exception('Not implemented editor field type');
						break;
				}
			$res .= '</td>';
			$res .= '</tr>';
		}

		$res .= '<tr style="height:40px;"><td></td><td></td><td><input class="ft_control" type="submit" id="send" name="send" value="Сохранить"/></td></tr>';
		$res .= '</tbody></table>';

		// Add additional hidden fields
		foreach ($dataHidden as $k => $v)
			$res .= '<input type="hidden" id="' . $k . '" name="' . $k . '" value="' . $v . '" />';

		$res .= '<input type="hidden" id="app" name="app" value="' . $appName . '" />';
		$res .= '<input type="hidden" id="_id" name="_id" value="' . $rowId . '" />';

		// Set form result
		if (isset($formParams['form_result']) && !empty($formParams['form_result']))
			$res .= '<div id="form_result">' . $formParams['form_result'] . '</div>';

		$res .= '</form>';

		// Change form onSubmit
		$res = str_ireplace('onsubmit="formSubmit(this', 'onsubmit="formSubmit(this, [' . $strOnSubmit . ']', $res);
	}
	else
	{
		$res .= '<div style="line-height:60px; text-align:center; vertical-align:middle;">' . $data . '</div>';
	}

	return $res;
}

function getList($appName, $data, $dataHidden = array(), $formParams = array())
{
	try
	{
		global $request, $response, $handlerPath;

		FTException::throwOnTrue(empty($appName), 'No app');

		// Result html
		$res = '';

		if (FTArrayUtils::checkData($data))
		{
			// Get editor for form
			$editorID = isset($formParams['editor']) ? $formParams['editor'] : ParamsConfig::EDITOR_LIST;

			// Get app config
			$controller = MvcFactory::create($appName, 'controller');
			$reqGetConfig = new ActionRequest($request);
			$reqGetConfig->params[Params::OPERATION_NAME] = 'get_config';
			$reqGetConfig->params[ParamsMvc::IS_NOT_RENDER] = TRUE;
			$reqGetConfig->params[ParamsMvc::APP_NAME] = $appName;
			$reqGetConfig->params[ParamsConfig::DATA_OBJECT] = isset($dataHidden['data_object']) ? $dataHidden['data_object'] : NULL;
			$reqGetConfig->params[ParamsConfig::EDITOR_ID] = $editorID;
			$config = $controller->run($reqGetConfig, $response);
			FTException::throwOnTrue(!FTArrayUtils::checkData(@$config['editor'][$editorID]['fields']), 'No editor');

			$res .= '<div>';
			$res .= '<table style="width:100%; border:1px solid #D4D4D4;" cellspacing="0" cellpadding="0">';

			// Header
			$res .= '<tr style="background-color:#EFEFEF; text-align:center;">';
			foreach ($config['editor'][$editorID]['fields'] as $k => $v)
			{
				$res .= '<td style="' . (isset($v['style']) ? $v['style'] : '') . ' padding:2px;">' . $v['name_ru'] . '</td>';
			}
			$res .= '</tr>';

			// Body
			foreach ($data as $row)
			{
				$res .= '<tr onclick="doajaxContent(\'';

				if ($appName == 'user')
					$res .= 'settings=user';
				else
					$res .= 'app=' . $appName;

				$res .= '&_id=' . $row['_id'];
				$res .= '\', this)">';
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
			$res .= '<div style="line-height:60px; text-align:center; vertical-align:middle;">' . $data . '</div>';
		}

		return $res;
	}
	catch (Exception $ex)
	{
		throw $ex;
	}
}

function processForm($appName, $rowId = -1, $dataObject = NULL, $formParams = array())
{
	global $request, $response;

	$result = array();

	$controller = MvcFactory::create($appName, 'controller');

	// Get editor
	if (isset($formParams['editor']))
		$editorID = $formParams['editor'];
	else
		$editorID = isset($request->dataWeb->request[ParamsConfig::EDITOR_ID]) ? $request->dataWeb->request[ParamsConfig::EDITOR_ID] : ParamsConfig::EDITOR_DEFAULT;

	// Get app config
	$reqGetConfig = new ActionRequest($request);
	$reqGetConfig->params[Params::OPERATION_NAME] = 'get_config';
	$reqGetConfig->params[ParamsMvc::IS_NOT_RENDER] = TRUE;
	$reqGetConfig->params[ParamsMvc::APP_NAME] = $appName;
	$reqGetConfig->params[ParamsConfig::DATA_OBJECT] = isset($request->dataWeb->request['data_object']) ? $request->dataWeb->request['data_object'] : NULL;
	$reqGetConfig->params[ParamsConfig::EDITOR_ID] = $editorID;
	$config = $controller->run($reqGetConfig, $response);

	// Form data to process
	$data = array();

	foreach ($request->dataWeb->request as $k_encoded => $v_encoded)
	{
		$k = urldecode($k_encoded);
		$v = urldecode($v_encoded);

		if ($k == '_id' || !key_exists($k, $config['editor'][$editorID]['fields']))
			continue;
		if (@$config['editor'][$editorID]['fields'][$k]['is_readonly'])
			continue;
		if (@$config['editor'][$editorID]['fields'][$k]['is_skip'])
			continue;

		// Check obligatory fields
		if (isset($config['editor'][$editorID]['fields'][$k]['is_null']) && !$config['editor'][$editorID]['fields'][$k]['is_null'] && empty($v))
		{
			$result['is_error'] = TRUE;
			$result['message'] = '<div style="color:red; text-align:center; border:1px dotted red; padding:3px;">Ошибка при сохранении данных: Не заполнено поле "' . (!@empty($config['editor'][$editorID]['fields'][$k]['name_ru']) ? $config['editor'][$editorID]['fields'][$k]['name_ru'] : $k) . '"</div>';
			return $result;
		}

		// Pack data
		$data[$k] = $v;
	}

	if (!FTArrayUtils::checkData($data))
		throw new Exception('No data to process');

	$req = new ActionRequest($request);
	$req->params[ParamsMvc::IS_NOT_RENDER] = TRUE;
	$req->params[ParamsConfig::DATA_OBJECT] = isset($request->dataWeb->request['data_object']) ? $request->dataWeb->request['data_object'] : NULL;
	$req->params[ParamsConfig::EDITOR_ID] = $editorID;
	$req->params[Params::DATA] = $data;

	if ($rowId != - 1)
	{
		if (!intval($rowId) || $rowId <= 0)
			throw new Exception('RowID is invalid');

		// Update record
		$req->params[Params::OPERATION_NAME] = 'update';
		$req->params[ParamsSql::RESTRICTION] = '_id=' . $rowId;
	}
	else
	{
		// Insert record
		$req->params[Params::OPERATION_NAME] = 'add';
	}

	$res = NULL;
	$exMessage = '';
	try
	{
		// Execute (!)
		$res = $controller->run($req, $response);
	}
	catch (Exception $ex)
	{
		FTException::saveEx($ex);
		//echo '<div style="color:red; font-weight:bold;">' . $ex->getMessage() . '</div>';
		$exMessage = $ex->getMessage();
	}

	if (!FTArrayUtils::checkData($res))
	{
		// Error!
		$result['is_error'] = TRUE;
		$result['message'] = '<div style="color:red; text-align:center; border:1px dotted red; padding:3px;">Ошибка при сохранении данных! ' . (!empty($exMessage) ? '<br />' . $exMessage : '') . '</div>';
	}
	else
	{
		// OK!
		$result['data'] = $res[0];
		$result['message'] = '<div style="color:#009900; text-align:center; border:1px dotted #009900; padding:3px;">Данные сохранены успешно</div>';
	}

	return $result;
}
