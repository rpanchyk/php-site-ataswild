<?php
require_once dirname(__FILE__) . '/../../inc/cde.inc.php';

class UserModel extends BaseModel
{
	protected function opLogin(ActionRequest & $request, ActionResponse & $response)
	{
		try
		{
			// Check params
			FTException::throwOnTrue(@empty($request->params['email']), 'EMAIL_EMPTY');
			FTException::throwOnTrue(@empty($request->params['password']), 'PASSWORD_EMPTY');

			// Get data
			$params = array();
			$params[ParamsSql::TABLE] = $this->m_entityName;
			$params[ParamsSql::RESTRICTION] = 'email=:email AND password=:password';
			$params[ParamsSql::RESTRICTION_DATA] = array(':email' => urldecode($request->params['email']), ':password' => FTStringUtils::cryptString($request->params['password']));
			$params[ParamsSql::LIMIT] = '1';
			$data = $request->db->get($params);
			FTException::throwOnTrue(!FTArrayUtils::checkData($data), 'USER_NOT_FOUND');

			// Get config
			$config = $this->opGetConfig($request, $response);
			FTException::throwOnTrue($data[0]['status'] == $config['user_status']['block'], 'USER_BLOCKED');
			FTException::throwOnTrue($data[0]['status'] == $config['user_status']['delete'], 'USER_DELETED');

			// Get guid
			$guid = new Guid();

			// Set SID
			$reqUpdate = new ActionRequest($request);
			$reqUpdate->params[Params::OPERATION_NAME] = Params::OPERATION_UPDATE;
			$reqUpdate->params[ParamsSql::RESTRICTION] = '_id=' . $data[0]['_id'];
			$reqUpdate->params[Params::DATA] = array('sid' => $guid->toString());
			$dataUpdate = $this->opUpdate($reqUpdate, $response);
			FTException::throwOnTrue(!FTArrayUtils::checkData($dataUpdate), 'SID not setted');

			// Set cookie
			if (!setcookie($config['cookie']['name'], $guid->toString(), time() + intval($config['cookie']['expire']), $config['cookie']['path'], $config['cookie']['domain'], $config['cookie']['secure'], $config['cookie']['httponly']))
				throw new Exception('Cannot set cookie');

			return $data;
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}

	protected function opGetSession(ActionRequest & $request, ActionResponse & $response)
	{
		try
		{
			// Get config
			$config = $this->opGetConfig($request, $response);

			$cookieName = isset($request->params['cookie_sid_name']) ? $request->params['cookie_sid_name'] : $config['cookie']['name'];

			if (!isset($request->dataWeb->cookie[$cookieName]))
				return array();

			// Get data
			$params = array();
			$params[ParamsSql::TABLE] = $this->m_entityName;
			$params[ParamsSql::RESTRICTION] = 'sid=:sid';
			$params[ParamsSql::RESTRICTION_DATA] = array(':sid' => $request->dataWeb->cookie[$cookieName]);
			$params[ParamsSql::LIMIT] = '1';
			$data = $request->db->get($params);

			return $data;
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}

	protected function opLogout(ActionRequest & $request, ActionResponse & $response)
	{
		try
		{
			$data = array();

			// Get config
			$config = $this->opGetConfig($request, $response);

			$cookieName = isset($request->params['cookie_sid_name']) ? $request->params['cookie_sid_name'] : $config['cookie']['name'];

			// Delete cookie
			if (isset($request->dataWeb->cookie[$cookieName]))
			{
				// Get data
				$params = array();
				$params[ParamsSql::TABLE] = $this->m_entityName;
				$params[ParamsSql::RESTRICTION] = 'sid=:sid';
				$params[ParamsSql::RESTRICTION_DATA] = array(':sid' => $request->dataWeb->cookie[$cookieName]);
				$params[ParamsSql::LIMIT] = '1';
				$data = $request->db->get($params);

				if (FTArrayUtils::checkData($data))
				{
					// Clear sid
					$reqUpdate = new ActionRequest($request);
					$reqUpdate->params[Params::OPERATION_NAME] = Params::OPERATION_UPDATE;
					$reqUpdate->params[ParamsSql::RESTRICTION] = '_id=' . $data[0]['_id'];
					$reqUpdate->params[Params::DATA] = array('sid' => '');
					$dataUpdate = $this->opUpdate($reqUpdate, $response);
					FTException::throwOnTrue(!FTArrayUtils::checkData($dataUpdate), 'SID not setted');
				}

				if (!setcookie($cookieName, '', time() - intval($config['cookie']['expire']), $config['cookie']['path'], $config['cookie']['domain']))
					throw new Exception('Cannot delete cookie');
			}

			return $data;
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}

	protected function opUpdate(ActionRequest & $request, ActionResponse & $response)
	{
		try
		{
			if (isset($request->params[Params::DATA]['password']))
			{
				if (empty($request->params[Params::DATA]['password']))
					unset($request->params[Params::DATA]['password']);
				else
					$request->params[Params::DATA]['password'] = FTStringUtils::cryptString($request->params[Params::DATA]['password']);
			}

			return parent::opUpdate($request, $response);
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}
}
