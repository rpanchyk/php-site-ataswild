<?php
require_once dirname(__FILE__) . '/../../inc/cde.inc.php';

class ContainerModel extends BaseModel
{
	protected function opGetByAlias(ActionRequest & $request, ActionResponse & $response)
	{
		try
		{
			FTException::throwOnTrue(!isset($request->params[Params::ALIAS]) || empty($request->params[Params::ALIAS]), 'No ' . Params::ALIAS);

			// Get container
			$params = array();
			$params[ParamsSql::TABLE] = $this->m_entityName;
			$params[ParamsSql::RESTRICTION] = 'alias=:alias AND is_active=1';
			$params[ParamsSql::RESTRICTION_DATA] = array(':alias' => $request->params[Params::ALIAS]);
			$params[ParamsSql::LIMIT] = '1';
			$data = $request->db->get($params);

			if (!FTArrayUtils::checkData($data))
			{
				FTException::saveEx(new Exception('No data for entity: ' . $params[ParamsSql::TABLE] . ' with alias: ' . $request->params[Params::ALIAS]));
				return $data;
			}

			if (@$request->params['is_not_process_markup'])
				return $data;

			for ($i = 0; $i < count($data); $i++)
			{
				// Get data by row
				$reqGetByRow = new ActionRequest($request);
				$reqGetByRow->params[Params::DATA_ROW] = $data[$i];
				$dataGetByRow = $this->opGetByRow($reqGetByRow, $response);

				if (!FTArrayUtils::checkData($dataGetByRow))
				{
					FTException::saveEx(new Exception('No data for row: #' . $i));
					continue;
				}

				// Pack result
				$data[$i]['model_result_data'] = $dataGetByRow[0];
			}

			return $data;
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}

	protected function opGetByRow(ActionRequest & $request, ActionResponse & $response)
	{
		try
		{
			FTException::throwOnTrue(!FTArrayUtils::checkData($request->params[Params::DATA_ROW]), 'No ' . Params::DATA_ROW);

			// Get tags
			$reqTags = new ActionRequest($request);
			$reqTags->params[Params::MARKUP] = isset($request->params[Params::DATA_ROW]['markup']) ? $request->params[Params::DATA_ROW]['markup'] : '';
			$aTagDataPair = $this->opGetTags($reqTags, $response);

			// Parse tags
			$reqParsedTags = new ActionRequest($request);
			$reqParsedTags->params[Params::DATA] = $aTagDataPair;
			$aParsedTags = $this->opParseTags($reqParsedTags, $response);

			// Go through tags and fill content
			if (FTArrayUtils::checkData($aParsedTags))
				foreach ($aParsedTags as $k => $v)
				{
					// Call controller each of item
					$ctrl = MvcFactory::create($v['app'], 'controller');
					$req = new ActionRequest($request);
					$req->params[Params::OPERATION_NAME] = Params::OPERATION_GET_BY_ALIAS;
					$req->params[Params::ALIAS] = $v['alias'];
					$strResult = $ctrl->run($req, $response);
					
					if (FTArrayUtils::checkData(@$v['relations']))
					{
						foreach ($v['relations'] as $relKey => $relVal)
						{
							if ($relKey == 'comments')
							{
								//echo '<pre>'; print_r($relVal); echo '</pre>';
								
								$req->params[ParamsMvc::IS_NOT_RENDER] = TRUE;
								$dataResult = $ctrl->run($req, $response);
								
								$ctrlComments = MvcFactory::create($relVal['app'], 'controller');
								
								// Add form
								$dataFormParams = array('alias' => 'feedback', 'header_text' => 'Оставить отзыв','_parent_id'=>$dataResult[0]['_id']);
								$strResult .= $ctrlComments->view->render('feedback_form', $dataFormParams, FALSE);
								
								$reqComments = new ActionRequest($request);
								$reqComments->params[Params::OPERATION_NAME] = Params::OPERATION_GET;
								$reqComments->params[ParamsMvc::IS_NOT_RENDER] = TRUE;
								$reqComments->params[ParamsSql::RESTRICTION] = 'is_active=1 AND _parent_id='.$dataResult[0]['_id'];
								$reqComments->params[ParamsSql::ORDER_BY]='_date_create DESC';
								$reqComments->params[ParamsConfig::DATA_OBJECT] = $relVal['app'];
								$dataComments = $ctrlComments->run($reqComments, $response);
								
								//$this->view->render('index', array('content' => $strOutput));
								//echo '<pre>'; print_r($dataComments); echo '</pre>';
								
								foreach ($dataComments as $aComment)
								{
									$aComment['alias'] = 'feedback';
									$aComment['date'] = date('d/m/Y', strtotime($aComment['_date_create']));
									// Render (!)
									$strResult .= $ctrlComments->view->render('feedback', $aComment, FALSE);
									//$this->view->renderText($row['markup'], $row[ParamsMvc::MODEL_RESULT_DATA]);
								}
							}
						}
						//$strResult .= '-0-0-0-0-';
					}
					
					// Pack result
					$aTagDataPair[$k] = $strResult;
				}
				//echo '<pre>'; print_r(array($aTagDataPair)); echo '</pre>';
			return array($aTagDataPair);
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}

	protected function opGetTags(ActionRequest & $request, ActionResponse & $response)
	{
		try
		{
			$strMarkup = isset($request->params[Params::MARKUP]) ? $request->params[Params::MARKUP] : '';

			// Array of tag -> html pairs
			$aTagDataPair = array();

			// Get tags
			if (preg_match_all("/\{(.*?)\}/", $strMarkup, $matches) > 0 && FTArrayUtils::checkData($matches, 2))
			{
				foreach ($matches[1] as $match)
					$aTagDataPair[$match] = '';
			}
			else
				FTException::saveEx(new Exception('No tag matches in: ' . $strMarkup));

			return $aTagDataPair;
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}

	protected function opParseTags(ActionRequest & $request, ActionResponse & $response)
	{
		try
		{
			// Ex: {static.feedback|relation:comments.feedback_comments;_parent_id;_id}
			
			if (!isset($request->params[Params::DATA]) || !FTArrayUtils::checkData($request->params[Params::DATA]))
				return array();

			$aTags = array();

			foreach ($request->params[Params::DATA] as $tag => $v)
			{
				// Get parts of tag
				$aParts = explode('|', $tag);

				// Check
				FTException::throwOnTrue(!FTArrayUtils::checkData($aParts), 'No tag data');

				$aValue = array();

				for ($i = 0; $i < count($aParts); $i++)
				{
					if (empty($aParts[$i]))
					{
						FTException::saveEx(new Exception('No part tag data in part: ' . $i));
						continue;
					}

					switch ($i)
					{
						case 0:
							// Get app and alias
							$aAppAlias = explode('.', $aParts[$i]);

							// Check
							if (!FTArrayUtils::checkData($aAppAlias, 2))
							{
								$aTags[$tag] = array();
								FTException::saveEx(new Exception('No data to determine application and alias'));
								continue;
							}

							// Set value
							$aValue['app'] = $aAppAlias[0];
							$aValue['alias'] = $aAppAlias[1];
							break;
						case 1:
							$aRelation = array();
							$aRelationParts = explode(';', str_ireplace('relation:', '', $aParts[$i]));

							if (!FTArrayUtils::checkData($aRelationParts))
							{
								$aTags[$tag] = array();
								FTException::saveMessage(new Exception('Error relation: ' . $aParts[$i]));
								continue;
							}

							// Get app and alias
							//$aAppAlias = explode('.', $aRelationParts[0]);

							// Check
							//if (!FTArrayUtils::checkData($aAppAlias, 2))
							//{
							//	$aTags[$tag] = array();
							//	FTException::saveEx(new Exception('No data to determine application and alias'));
							//	continue;
							//}

							// Set value
							$aRelation['app'] = $aRelationParts[0];
							//$aRelation['app'] = $aAppAlias[0];
							//$aRelation['alias'] = $aAppAlias[1];

							if (count($aRelationParts) >= 2)
								$aRelation[Params::RELATION_SOURCE_FIELD] = $aRelationParts[1];
							else
								$aRelation[Params::RELATION_SOURCE_FIELD] = Params::RELATION_SOURCE_FIELD_DEFAULT_NAME;
							$aRelation[Params::RELATION_SOURCE_FIELD] = $request->db->getTableName($aRelation['app']) . '.' . $aRelation[Params::RELATION_SOURCE_FIELD];

							if (count($aRelationParts) >= 3)
								$aRelation[Params::RELATION_DESTINATION_FIELD] = $aRelationParts[2];
							else
								$aRelation[Params::RELATION_DESTINATION_FIELD] = Params::RELATION_DESTINATION_FIELD_DEFAULT_NAME;
							$aRelation[Params::RELATION_DESTINATION_FIELD] = $request->db->getTableName($aValue['app']) . '.' . $aRelation[Params::RELATION_DESTINATION_FIELD];

							$aValue[Params::RELATIONS][$aRelation['app']] = $aRelation;
							break;
						default:
							throw new Exception('Not implemented tag part(' . $i . '): ' . $aParts[$i] . ' in tag: ' . $tag);
							break;
					}
				}

				// Pack result
				$aTags[$tag] = $aValue;
			}

			return $aTags;
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}

	protected function opGetTree(ActionRequest & $request, ActionResponse & $response)
	{
		try
		{
			$params = array();
			$params[ParamsSql::TABLE] = $this->m_entityName;
			$params[ParamsSql::RESTRICTION] = isset($request->params[ParamsSql::RESTRICTION]) ? $request->params[ParamsSql::RESTRICTION] : 'is_section=1 AND is_active=1';
			$params[ParamsSql::RESTRICTION_DATA] = isset($request->params[ParamsSql::RESTRICTION_DATA]) ? $request->params[ParamsSql::RESTRICTION_DATA] : NULL;
			$params[ParamsSql::LIMIT] = isset($request->params[ParamsSql::LIMIT]) ? $request->params[ParamsSql::LIMIT] : NULL;
			$data = $request->db->get($params);

			if (!FTArrayUtils::checkData($data))
			{
				FTException::saveEx(new Exception('No data for entity: ' . $params[ParamsSql::TABLE]));
				return $data;
			}

			// Result
			$aTree = $this->prepareTreeBranch($data);

			// Get childs
			for ($i = 0; $i < count($data); $i++)
			{
				// Get tags
				$reqTags = new ActionRequest($request);
				$reqTags->params[Params::MARKUP] = $data[$i]['markup'];
				$aTree[$i]['app'] = 'container';
				$aTree[$i]['childs'] = $this->opGetTags($reqTags, $response);

				if (!FTArrayUtils::checkData($aTree[$i]['childs']))
					continue;

				// Parse tags
				$reqParsedTags = new ActionRequest($request);
				$reqParsedTags->params[Params::DATA] = $aTree[$i]['childs'];
				$aParsedTags = $this->opParseTags($reqParsedTags, $response);
//echo '<pre>'; print_r($aParsedTags); echo '</pre>';
				if (FTArrayUtils::checkData($aParsedTags))
					foreach ($aParsedTags as $k => $v)
					{
						// Call controller each of item
						$controller = MvcFactory::create($v['app'], 'controller');
						$model = MvcFactory::create($v['app'], 'model');
						$req = new ActionRequest($request);
						$req->params[Params::OPERATION_NAME] = Params::OPERATION_GET_BY_ALIAS;
						$req->params[Params::ALIAS] = $v['alias'];
						$req->params[ParamsMvc::IS_NOT_RENDER] = TRUE;
						$dataModel = $model->execute($req, $response, $controller);

						if (!FTArrayUtils::checkData($dataModel))
						{
							$aTree[$i]['childs'][$k] = array();
							continue;
						}

						// Pack result
						$dataPrep = $this->prepareTreeBranch($dataModel);
						$aTree[$i]['childs'][$k] = $dataPrep[0];
						$aTree[$i]['childs'][$k]['app'] = $v['app'];

						if ($v['app'] == 'container')
						{
							// Get child container data
							$reqChilds = new ActionRequest($request);
							$reqChilds->params[Params::ALIAS] = $v['alias'];
							$reqChilds->params[ParamsSql::RESTRICTION] = 'alias=:alias AND is_active=1';
							$reqChilds->params[ParamsSql::RESTRICTION_DATA] = array(':alias' => $reqChilds->params[Params::ALIAS]);
							$reqChilds->params[ParamsSql::LIMIT] = '1';
							$dataChilds = $this->opGetTree($reqChilds, $response);

							if (!FTArrayUtils::checkData($dataChilds))
							{
								$aTree[$i]['childs'][$k] = array();
								continue;
							}

							$aTree[$i]['childs'][$k] = $dataChilds[0];
							//echo '<pre>'; print_r($aTree[$i]['childs'][$k]); echo '</pre>';
						}
						
						//echo '<pre>'; print_r(@$v['relations']); echo '</pre>';
						
						if (FTArrayUtils::checkData(@$v['relations']))
						{
							foreach ($v['relations'] as $relKey => $relVal)
							{
								//echo '<pre>'; print_r($relVal); echo '</pre>';
								
								$aTree[$i]['childs'][$k]['childs'][$relKey]['app'] = $relVal['app'];
								$aTree[$i]['childs'][$k]['childs'][$relKey]['_parent_id'] = $dataPrep[0]['_id'];
								$aTree[$i]['childs'][$k]['childs'][$relKey]['name'] = $dataPrep[0]['name'];
								
								//$aTree[$i]['childs'][] = $relVal;
								//$aTree[$i]['childs'][]['app'] = $relVal['app'];
								// Get child container data
/*
								// Call controller each of item
								$controller = MvcFactory::create($relVal['app'], 'controller');
								$model = MvcFactory::create($relVal['app'], 'model');
								$req = new ActionRequest($request);
								$req->params[Params::OPERATION_NAME] = Params::OPERATION_GET_BY_ALIAS;
								$req->params[Params::ALIAS] = $relVal['alias'];
								$req->params[ParamsMvc::IS_NOT_RENDER] = TRUE;
								$dataModel = $model->execute($req, $response, $controller);
		
								if (!FTArrayUtils::checkData($dataModel))
								{
									$aTree[$i]['childs'][$k]['childs'][$relKey] = array();
									continue;
								}
		//echo '<pre>'; print_r($dataModel); echo '</pre>';
								// Pack result
								$dataPrep = $this->prepareTreeBranch($dataModel);
								$aTree[$i]['childs'][$k]['childs'][$relKey] = $dataPrep[0];
								$aTree[$i]['childs'][$k]['childs'][$relKey]['app'] = $relVal['app'];
	*/							
								//echo '<pre>'; print_r($aTree[$i]['childs'][$relKey]); echo '</pre>';
							}
						}
					}
			}

			return $aTree;
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
			$request->db->beginTransaction();

			$data = parent::opUpdate($request, $response);

			if (!@$request->params['is_not_process_markup'] && FTArrayUtils::checkData($data))
			{
				foreach ($data as $row)
				{
					// Get tags
					$reqTags = new ActionRequest($request);
					$reqTags->params[Params::MARKUP] = $row['markup'];
					$aTags = $this->opGetTags($reqTags, $response);

					// Parse tags
					$reqParsedTags = new ActionRequest($request);
					$reqParsedTags->params[Params::DATA] = $aTags;
					$aParsedTags = $this->opParseTags($reqParsedTags, $response);

					if (FTArrayUtils::checkData($aParsedTags))
						foreach ($aParsedTags as $v)
						{
							$reqAddApp = new ActionRequest($request);
							$reqAddApp->params['app_data'] = $v;
							$this->opAddApp($reqAddApp, $response);

							// Process relations
							//if (FTArrayUtils::checkData(@$v[Params::RELATIONS]))
							//	foreach ($v[Params::RELATIONS] as $relation)
							//	{
							//		$reqRelationAddApp = new ActionRequest($request);
							//		$reqRelationAddApp->params['app_data'] = $relation;
							//		$this->opAddApp($reqRelationAddApp, $response);
							//	}
						}
				}
			}

			$request->db->commitTransaction();

			return $data;
		}
		catch (Exception $ex)
		{
			$request->db->rollbackTransaction();
			throw $ex;
		}
	}

	protected function opAddApp(ActionRequest & $request, ActionResponse & $response)
	{
		try
		{
			FTException::throwOnTrue(!FTArrayUtils::checkData($request->params['app_data']), 'No relation');

			$aAppAlias = $request->params['app_data'];

			FTException::throwOnTrue(!isset($aAppAlias['app']), 'No app');
			FTException::throwOnTrue(!isset($aAppAlias['alias']), 'No app alias');

			$aResult = array();

			$controller = MvcFactory::create($aAppAlias['app'], 'controller');
			$reqGet = new ActionRequest($request);
			$reqGet->params[Params::OPERATION_NAME] = Params::OPERATION_GET_BY_ALIAS;
			$reqGet->params[Params::ALIAS] = $aAppAlias['alias'];
			$reqGet->params['is_not_process_markup'] = TRUE;
			$reqGet->params[ParamsMvc::IS_NOT_RENDER] = TRUE;
			$dataGet = $controller->run($reqGet, $response);

			// Add new item	
			if (!FTArrayUtils::checkData($dataGet))
			{
				// Get default data
				$dataAdd = array();
				// Get app config
				$reqGetConfig = new ActionRequest($request);
				$reqGetConfig->params[Params::OPERATION_NAME] = Params::OPERATION_GET_CONFIG;
				$reqGetConfig->params[ParamsMvc::IS_NOT_RENDER] = TRUE;
				$reqGetConfig->params[ParamsMvc::APP_NAME] = $aAppAlias['app'];
				$resGetConfig = $controller->run($reqGetConfig, $response);

				// Fill values
				if (FTArrayUtils::checkData(@$resGetConfig['editor']['fields']))
					foreach ($resGetConfig['editor']['fields'] as $confKey => $confValue)
						if (isset($confValue['default_value']))
							$dataAdd[$confKey] = $confValue['default_value'];

				$dataAdd['alias'] = $aAppAlias['alias'];

				if ($aAppAlias['app'] == 'container')
					$dataAdd['is_section'] = '0';

				// Add record
				$reqAdd = new ActionRequest($request);
				$reqAdd->params[Params::OPERATION_NAME] = Params::OPERATION_ADD;
				$reqAdd->params[ParamsMvc::IS_NOT_RENDER] = TRUE;
				$reqAdd->params[Params::DATA] = $dataAdd;
				$resAdd = $controller->run($reqAdd, $response);

				// Check
				FTException::throwOnTrue(!FTArrayUtils::checkData($resAdd), 'Cannot add app.alias: ' . $aAppAlias['app'] . '.' . $aAppAlias['alias']);

				// Set to result
				$aResult[] = $resAdd;
			}

			return $aResult;
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}

	private function prepareTreeBranch(Array $data)
	{
		try
		{
			$aTree = array();

			foreach ($data as $k => $v)
				$aTree[$k] = array('_id' => $v['_id'], 'alias' => $v['alias'], 'name' => $v['name']);

			return $aTree;
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}
}
