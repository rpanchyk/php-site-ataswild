<?php
require_once dirname(__FILE__) . '/../inc/cde.inc.php';

/**
 * Determine all artefacts for MVC from url
 */
class MvcData extends FTFireTrot
{
	protected $m_strLanguage;
	protected $m_strFormatter;
	protected $m_strController;
	protected $m_strOperation;
	protected $m_strId;
	protected $m_strOther;

	protected $m_db;
	protected $m_bIsStopDetermine;

	static protected $instance = NULL;

	static public function getInstance($strUrl, $strLanguageDefault, $strFormatterDefault, $strControllerDefault, $strOperationDefault, $aLanguages = array(), $aFormatters = array(), $aControllers = array(), $db = NULL)
	{
		try
		{
			if (self::$instance == NULL)
				self::$instance = new self($strUrl, $strLanguageDefault, $strFormatterDefault, $strControllerDefault, $strOperationDefault, $aLanguages, $aFormatters, $aControllers, $db);

			return self::$instance;
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}

	public function showResult()
	{
		$sep = '<div style="display:inline; padding-left:20px;">| </div>';

		$result = '<div style="height:5px;"></div><div style="background: #FFCC99; border:2px solid #FF6633; padding:3px; font:12px Verdana;">'; // . $this->getElapsedTimeAsString($nPrecision) . '</div>';
		$result .= '<b>Language:</b> ' . $this->getLanguage();
		$result .= $sep . '<b>Formatter:</b> ' . $this->getFormatter();
		$result .= $sep . '<b>Controller:</b> ' . $this->getController();
		$result .= $sep . '<b>Operation:</b> ' . $this->getOperation();
		$result .= $sep . '<b>Id:</b> ' . $this->getId();
		$result .= $sep . '<b>Other:</b> ' . $this->getOther();
		$result .= '</div>';

		echo $result;
	}

	protected function __construct($strUrl, $strLanguageDefault, $strFormatterDefault, $strControllerDefault, $strOperationDefault, $aLanguages = array(), $aFormatters = array(), $aControllers = array(), $db = NULL)
	{
		try
		{
			$this->m_db = $db;
			$this->m_bIsStopDetermine = FALSE;

			$this->setDefaults($strLanguageDefault, $strFormatterDefault, $strControllerDefault, $strOperationDefault);

			if (!empty($strUrl))
				$this->parseUrl($strUrl, $aLanguages, $aFormatters, $aControllers);
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}
	protected function __clone()
	{
	}

	public function getLanguage()
	{
		return $this->m_strLanguage;
	}
	public function getFormatter()
	{
		return $this->m_strFormatter;
	}
	public function getController()
	{
		return $this->m_strController;
	}
	public function getOperation()
	{
		return $this->m_strOperation;
	}
	public function getId()
	{
		return $this->m_strId;
	}
	public function getOther()
	{
		return $this->m_strOther;
	}

	protected function setDefaults($strLanguageDefault, $strFormatterDefault, $strControllerDefault, $strOperationDefault)
	{
		try
		{
			$this->m_strLanguage = strtolower($strLanguageDefault);
			$this->m_strFormatter = strtolower($strFormatterDefault);
			$this->m_strController = strtolower($strControllerDefault);
			$this->m_strOperation = strtolower($strOperationDefault);
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}
	protected function parseUrl($strUrl, $aLanguages = array(), $aFormatters = array(), $aControllers = array())
	{
		try
		{
			$strTmpUrl = strtolower($strUrl);

			$strTmpUrl = $this->parseLanguage($strTmpUrl, $aLanguages);
			$strTmpUrl = $this->parseFormatter($strTmpUrl, $aFormatters);
			$strTmpUrl = $this->parseController($strTmpUrl, $aControllers);
			$strTmpUrl = $this->parseOperation($strTmpUrl);
			$strTmpUrl = $this->parseId($strTmpUrl);
			$strTmpUrl = $this->parseOther($strTmpUrl);
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}

	protected function parseLanguage($strUrl, $aLanguages = array())
	{
		try
		{
			$aUrlParts = explode(SLASH, $strUrl, 2);

			if (count($aUrlParts) == 0 || empty($aUrlParts[0]) || !in_array($aUrlParts[0], $aLanguages))
				return $strUrl;

			// Set value
			$this->m_strLanguage = $aUrlParts[0];

			return FTStringUtils::trimStart($strUrl, $aUrlParts[0] . SLASH);
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}
	protected function parseFormatter($strUrl, $aFormatters = array())
	{
		try
		{
			$aUrlParts = explode(SLASH, $strUrl, 2);

			if (count($aUrlParts) == 0 || empty($aUrlParts[0]) || !in_array($aUrlParts[0], $aFormatters))
				return $strUrl;

			// Set value
			$this->m_strFormatter = $aUrlParts[0];

			return FTStringUtils::trimStart($strUrl, $aUrlParts[0] . SLASH);
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}
	protected function parseController($strUrl, $aControllers = array())
	{
		try
		{
			$aUrlParts = explode(SLASH, $strUrl, 2);

			if (count($aUrlParts) == 0 || empty($aUrlParts[0]))
				return $strUrl;

			if (is_null($aControllers) || !is_array($aControllers) || !count($aControllers))
			{
				$aControllers = array();

				// Get objects from DB
				$params = array();
				$params[ParamsSql::TABLE] = 'container';
				$params[ParamsSql::RESTRICTION] = 'alias=:alias';
				$params[ParamsSql::RESTRICTION_DATA] = array(':alias' => strtolower($aUrlParts[0]));
				$dataControllers = $this->m_db->get($params);

				if (!isset($dataControllers) || !is_array($dataControllers) || !count($dataControllers))
				{
					// If no controller - skip operation
					$this->m_bIsStopDetermine = TRUE;

					return $strUrl;
				}
			}

			// Set value
			$this->m_strController = $aUrlParts[0];

			return FTStringUtils::trimStart($strUrl, $aUrlParts[0] . SLASH);
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}
	protected function parseOperation($strUrl)
	{
		try
		{
			if ($this->m_bIsStopDetermine)
				return $strUrl;

			$aUrlParts = explode(SLASH, $strUrl, 2);

			if (count($aUrlParts) == 0 || empty($aUrlParts[0]))
				return $strUrl;

			// Get operations
			// @todo

			//			if (!preg_match("/^[^0-9][a-z]+$/", $aUrlParts[0]))
			//			FTStringUtils::trimStart($strUrl, $aUrlParts[0] . SLASH);

			// Set value
			$this->m_strOperation = $aUrlParts[0];

			return FTStringUtils::trimStart($strUrl, $aUrlParts[0] . SLASH);
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}
	protected function parseId($strUrl)
	{
		try
		{
			if ($this->m_bIsStopDetermine)
				return $strUrl;

			$aUrlParts = explode(SLASH, $strUrl, 2);

			if (count($aUrlParts) == 0)
				return $strUrl;

			// Set value
			$this->m_strId = $aUrlParts[0];

			return FTStringUtils::trimStart($strUrl, $aUrlParts[0] . SLASH);
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}
	protected function parseOther($strUrl)
	{
		try
		{
			if ($this->m_bIsStopDetermine)
				return $strUrl;

			$aUrlParts = explode(SLASH, $strUrl, 2);

			if (count($aUrlParts) == 0)
				return $strUrl;

			// Set value
			$this->m_strOther = $aUrlParts[0];

			return FTStringUtils::trimStart($strUrl, $aUrlParts[0] . SLASH);
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}
}
