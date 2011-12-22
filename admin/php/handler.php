<?php

try
{
	// Start output buffering
	ob_start();

	// Start session
	@session_start();

	// Set engine is started
	if (!defined('ENGINE_IS_STARTED'))
		define('ENGINE_IS_STARTED', 'y');

	// Include all files need
	require_once dirname(__FILE__) . '/../../engine/core/include.php';
	require_once dirname(__FILE__) . '/inc/form.inc.php';
	//require_once ADMIN_PATH . '/php/inc/auth.inc.php';
	require_once ADMIN_PATH . '/php/inc/tree.inc.php';

	// Create base controller (just for existance)
	$base = MvcFactory::create('base', 'controller');

	// Create initial request & response
	$request = new ActionRequest(NULL);
	$response = new ActionResponse();

	$handlerPath = '/admin/php/handler.php';

	// Init vars
	$request = new ActionRequest(NULL);
	$regexPatternRequestParamValue = "/^[-a-zA-Z0-9_]+$/";

	$user = MvcFactory::create('user', 'controller');
	$reqAuth = new ActionRequest($request);
	$reqAuth->params[Params::OPERATION_NAME] = Params::OPERATION_USER_GET_SESSION;
	$dataAuth = $user->run($reqAuth, $response);
	$strNoAuthMessage = 'Пользователь не авторизован.<br /><a href="/admin/">Войти</a>';

	if (isset($request->dataWeb->request['do_login']))
	{
		// Do auth
		$reqLogin = new ActionRequest($request);
		$reqLogin->params['email'] = $request->dataWeb->request['email'];
		$reqLogin->params['password'] = $request->dataWeb->request['password'];
		$reqLogin->params[Params::OPERATION_NAME] = Params::OPERATION_USER_LOGIN;
		try
		{
			$dataLogin = $user->run($reqLogin, $response);
		}
		catch (Exception $ex)
		{
			echo '<!-- error -->' . (isset($user->config['error'][$ex->getMessage()]['name_ru']) ? $user->config['error'][$ex->getMessage()]['name_ru'] : $ex->getMessage());
		}
	}
	elseif (isset($request->dataWeb->request['do_logout']))
	{
		$reqLogout = new ActionRequest($request);
		$reqLogout->params[Params::OPERATION_NAME] = Params::OPERATION_USER_LOGOUT;
		$dataLogout = $user->run($reqLogout, $response);

		header('Location: http://' . $request->dataWeb->server['SERVER_NAME'] . '/admin/');
		exit;
	}
	elseif (isset($request->dataWeb->request['tree']))
	{
		if (!FTArrayUtils::checkData($dataAuth))
		{
			echo $strNoAuthMessage;
			return;
		}

		// Process menu
		echo getTree($handlerPath);
	}
	elseif (isset($request->dataWeb->request['app']))
	{
		if (!FTArrayUtils::checkData($dataAuth))
		{
			echo $strNoAuthMessage;
			return;
		}

		// Process content
		$paramApp = ActionRequest::getRequestParamValue($request, 'app', $regexPatternRequestParamValue);
		$paramId = ActionRequest::getRequestParamValue($request, '_id', $regexPatternRequestParamValue, TRUE);
		$paramParentId = ActionRequest::getRequestParamValue($request, '_parent_id', $regexPatternRequestParamValue, TRUE);
		$paramDataObject = ActionRequest::getRequestParamValue($request, 'data_object', $regexPatternRequestParamValue, TRUE);
		$paramDataObjectID = ActionRequest::getRequestParamValue($request, 'data_object_id', $regexPatternRequestParamValue, TRUE);

		$data = 'Нет данных';
		$formResult = array();

		// Process data
		if (isset($request->dataWeb->request['send']))
			$formResult = processForm($paramApp, $paramId, $paramDataObject);

		$controller = MvcFactory::create($paramApp, 'controller');

		if (FTArrayUtils::checkData($formResult))
		{
			if (@$formResult['is_error'])
			{
				$data = array();
				foreach ($request->dataWeb->request as $k => $v)
					$data[$k] = urldecode($v);
			}
			elseif (FTArrayUtils::checkData(@$formResult['data']))
				$data = $formResult['data'];
		}
		elseif (!@empty($paramId))
		{
			if ($paramId != - 1)
			{
				// Get data
				$req = new ActionRequest($request);
				$req->params[Params::OPERATION_NAME] = 'get_by_id';
				$req->params[Params::ID] = $paramId;
				$req->params[ParamsMvc::IS_NOT_RENDER] = TRUE;
			
				$req->params[ParamsConfig::DATA_OBJECT] = $paramApp;
				//$req->params[ParamsConfig::EDITOR_ID] = ParamsConfig::EDITOR_DEFAULT;
				
			$dataController = $controller->run($req, $response);
				FTException::throwOnTrue(!FTArrayUtils::checkData($dataController), 'No data for ' . $paramApp);

				$data = $dataController[0];
			}
			else
			{
				// Add new record

				// Get app config
				$reqGetConfig = new ActionRequest($request);
				$reqGetConfig->params[Params::OPERATION_NAME] = 'get_config';
				$reqGetConfig->params[ParamsMvc::IS_NOT_RENDER] = TRUE;
				$reqGetConfig->params[ParamsMvc::APP_NAME] = $paramApp;
				$resGetConfig = $controller->run($reqGetConfig, $response);

				$data = array();

				// Fill values
				if (FTArrayUtils::checkData(@$resGetConfig['editor']['default']['fields']))
					foreach ($resGetConfig['editor']['default']['fields'] as $confKey => $confValue)
						if (isset($confValue['default_value']))
							$data[$confKey] = $confValue['default_value'];
						else
							$data[$confKey] = '';
				$data['name'] = '';
			}
		}
		elseif (!@empty($paramParentId))
		{
			$controller = MvcFactory::create($paramApp, 'controller');
			$reqGetList = new ActionRequest($request);
			$reqGetList->params[Params::OPERATION_NAME] = Params::OPERATION_GET;
			$reqGetList->params[ParamsMvc::IS_NOT_RENDER] = TRUE;
			$reqGetList->params[ParamsSql::RESTRICTION] = '_parent_id='.$paramParentId . ' AND is_active=1';
			$reqGetList->params[ParamsConfig::DATA_OBJECT] = $paramApp;
			$data = $controller->run($reqGetList, $response);
			
			$data = FTArrayUtils::checkData($data) ? $data : 'Нет данных';
		}

		// Add hidden data
		$dataHidden = array();
		$dataHidden['data_object'] = $paramDataObject;
		$dataHidden['data_object_id'] = $paramDataObjectID;
		$dataHidden['data_object_parent_id'] = $paramId ? $paramId : NULL;

		// Add form params
		$dataFormParams = array();
		$dataFormParams['form_result'] = isset($formResult['message']) ? $formResult['message'] : '';

		// Show form
		if (@empty($paramParentId) && !@empty($paramId) && (!$paramDataObject || $paramDataObjectID))
		{
			$dataHidden[ParamsConfig::EDITOR_ID] = ParamsConfig::EDITOR_DEFAULT;
			echo getForm($paramApp, $paramId, $data, $dataHidden, $dataFormParams);
		}
		else
		{
			$dataHidden[ParamsConfig::EDITOR_ID] = ParamsConfig::EDITOR_LIST;
			echo getList($paramApp, $data, $dataHidden, $dataFormParams);
		}
	}
	elseif (isset($request->dataWeb->request['settings']))
	{
		if (!FTArrayUtils::checkData($dataAuth))
		{
			echo $strNoAuthMessage;
			return;
		}

		$paramApp = ActionRequest::getRequestParamValue($request, 'settings', $regexPatternRequestParamValue);

		$data = 'Нет данных';
		//$formResult = array();

		// Process data
		//if (isset($request->dataWeb->request['send']))
		//$formResult = processForm($paramApp, $paramId);

		$controller = MvcFactory::create($paramApp, 'controller');

		if (!isset($request->dataWeb->request['_id']))
		{
			// Get list
			$reqGetList = new ActionRequest($request);
			$reqGetList->params[Params::OPERATION_NAME] = Params::OPERATION_GET;
			$reqGetList->params[ParamsMvc::IS_NOT_RENDER] = TRUE;
			$data = $controller->run($reqGetList, $response);

			echo getList($paramApp, $data);
		}
		else
		{
			$paramId = ActionRequest::getRequestParamValue($request, '_id', $regexPatternRequestParamValue);

			// Get data
			$reqGetById = new ActionRequest($request);
			$reqGetById->params[Params::OPERATION_NAME] = 'get_by_id';
			$reqGetById->params[Params::ID] = $paramId;
			$reqGetById->params[ParamsMvc::IS_NOT_RENDER] = TRUE;
			$dataGetById = $controller->run($reqGetById, $response);

			FTException::throwOnTrue(!FTArrayUtils::checkData($dataGetById), 'No data for ' . $paramApp);

			$data = $dataGetById[0];

			// Show form
			echo getForm($paramApp, $paramId, $data, array(), array('form_result' => isset($formResult['message']) ? $formResult['message'] : ''));
		}
	}
	elseif (isset($request->dataWeb->request['feedback_send']))
	{
		// Send email to moderator
		require_once EXTERNAL_PATH . '/Net_SMTP/send.php';
		
		define('ANTIBOT_VALUE', '5');
		define('ERROR_NAME_EMPTY', 'Вы не заполнили поле Имя!');
		define('ERROR_EMAIL_EMPTY', 'Вы не заполнили поле E-mail!');
		define('ERROR_EMAIL_INVALID', 'Вы неправильно заполнили поле E-mail!');
		define('ERROR_MESSAGE_EMPTY', 'Вы не ввели поле Сообщение!');
		define('ERROR_CHECKCODE_INVALID', 'Вы неправильно заполнили проверочный код!');
		define('GOTOBACK', BR . BR . '<a href="javascript: history.go(-1)"> Вернитесь назад и повторите попытку </a>');
		define('GOTOMAIN', BR . BR . '<a href="/"> Перейти на главную </a>');
		define('SEND_OK', "Ваше сообщение было успешно отправленно.");
		define('ERROR_SEND_FAILED', "Ваше сообщение не удалось отправить.");
		
		$error = '';
		
		// Обработка полей
		$name = trim($request->dataWeb->request['name']);
		$email_from = trim($request->dataWeb->request['email']);
		$message = trim($request->dataWeb->request['content']);
		$antibot = trim($request->dataWeb->request['anti_spam_code']);
		
		$error .= (empty($name) ? ($error == '' ? '' : BR) . ERROR_NAME_EMPTY : '');
		$error .= (empty($email_from) ? ($error == '' ? '' : BR) . ERROR_EMAIL_EMPTY : '');
		$error .= (empty($message) ? ($error == '' ? '' : BR) . ERROR_MESSAGE_EMPTY : '');

		$error .= ($request->dataWeb->session['AntiSpamImage'] != $antibot ? ($error == '' ? '' : BR) . ERROR_CHECKCODE_INVALID : '');
		
		$_SESSION['AntiSpamImage'] = rand(1,9999999);
		if (strpos($error, ERROR_EMAIL_EMPTY) === false && !preg_match("/^[a-zA-Z0-9_\-\.]+@[a-zA-Z0-9\-]+\.[a-zA-Z0-9\-\.]+$/", $email_from)) 
			$error .= ($error == '' ? '' : BR) . ERROR_EMAIL_INVALID;
		
		// Add comment
		$dataAdd = array();
		$dataAdd = $request->dataWeb->request;
		$dataAdd['is_active'] = '0';
		$dataAdd['content'] = str_replace(CRLF, BR, $dataAdd['content']);
		$dataAdd['_date_create'] = $dataAdd['_date_modify'] = date('YmdHis');
		unset($dataAdd['anti_spam_code']);
		unset($dataAdd['feedback_send']);

		$ctrlComments = MvcFactory::create('comments', 'controller');
		$reqAddComments = new ActionRequest($request);
		$reqAddComments->params[Params::OPERATION_NAME] = Params::OPERATION_ADD;
		$reqAddComments->params[ParamsMvc::IS_NOT_RENDER] = TRUE;
		$reqAddComments->params[Params::DATA] = $dataAdd;
		$reqAddComments->params[ParamsConfig::DATA_OBJECT] = 'comments';
		$dataAddComments = $ctrlComments->run($reqAddComments, $response);
		FTException::throwOnTrue(!FTArrayUtils::checkData($dataAddComments), 'Cannot add comment');

		?>
		<html>
		<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title> FeedBack </title>
		</head>
		<body>
		<p>
		<?php
		
		if (empty($error))
		{
			// Accept/Deacept comment link
			$link = strtolower(substr($request->dataWeb->server['SERVER_PROTOCOL'],0, strlen($request->dataWeb->server['SERVER_PROTOCOL']) -strpos($request->dataWeb->server['SERVER_PROTOCOL'], '/')) ). '://' . $request->dataWeb->server['SERVER_NAME'] . ':' . $request->dataWeb->server['SERVER_PORT'] .$request->dataWeb->server['REQUEST_URI'];
			$link .= '?feedback_confirmation=1&_id='.$dataAddComments[0]['_id'];
			
			$msg  = 'Сообщение с сайта ' . $request->dataWeb->server['SERVER_NAME'];
			$msg .= CRLF . 'Отправитель: ' . $name . ' (' . $email_from . ')';
			$msg .= CRLF . 'Сообщение:' . CRLF . '------------------------------' . CRLF . $message . CRLF . '------------------------------' . CRLF;
			$msg .= CRLF . 'Принять: ' . $link.'&accept=1';
			$msg .= CRLF . 'Удалить: ' . $link.'&accept=0';
				
			// Let's rock!
			$email_result = custom_mail($email_from, $engineConfig['smtp']['email_address'], $engineConfig['smtp']['email_subject'], $msg, $engineConfig['smtp']['server_host'], $engineConfig['smtp']['server_port'],$engineConfig['smtp']['server_user'],$engineConfig['smtp']['server_pass']);
			
			if ($email_result)
			echo SEND_OK . GOTOMAIN;
			else
			echo ERROR_SEND_FAILED . GOTOBACK;
		}
		else
		echo $error . GOTOBACK;
		?>
		</p>
		</body>
		</html>
		<?php
	}
	elseif (isset($request->dataWeb->request['feedback_confirmation']))
	{
		if (!FTArrayUtils::checkData($dataAuth))
		{
			echo $strNoAuthMessage;
			FTException::throwOnTrue(TRUE, $strNoAuthMessage);
		}
		
		$paramId = ActionRequest::getRequestParamValue($request, '_id', $regexPatternRequestParamValue);
		
		// Update comment
		$ctrlComments = MvcFactory::create('comments', 'controller');
		$reqUpdateComments = new ActionRequest($request);
		$reqUpdateComments->params[Params::OPERATION_NAME] = Params::OPERATION_UPDATE;
		$reqUpdateComments->params[ParamsMvc::IS_NOT_RENDER] = TRUE;
		$reqUpdateComments->params[Params::DATA] = array('is_active' => ($request->dataWeb->request['accept'] ? '1' : '0'), '_date_modify' => date('YmdHis'));
		$reqUpdateComments->params[ParamsConfig::DATA_OBJECT] = 'comments';
		$reqUpdateComments->params[ParamsSql::RESTRICTION] = '_id='.$paramId;
		$dataAddComments = $ctrlComments->run($reqUpdateComments, $response);
		FTException::throwOnTrue(!FTArrayUtils::checkData($dataAddComments), 'Cannot update comment');
		
		?>
		<html>
		<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title> FeedBack </title>
		</head>
		<body>
		<p>
		<?php
		echo 'Комментарий '. ($request->dataWeb->request['accept'] ? 'принят' : 'отклонен');
		?>
		</p>
		</body>
		</html>
		<?php
	}
}
catch (Exception $ex)
{
	throw $ex;
}
