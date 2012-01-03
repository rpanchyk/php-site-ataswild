<?php
require_once dirname(__FILE__) . '/../inc/cde.inc.php';

/**
 * Database driver
 */
class DatabaseDriver extends FTFireTrot
{
	// DB params
	protected $m_dbConn;
	protected $m_dbType;
	protected $m_dbName;
	protected $m_bIsInTransaction;
	protected $m_strTablePrefix;

	// Cache params
	protected $m_bIsUseCache;
	protected $m_cacheDirPath;
	protected $m_cacheTtl;

	protected $m_aBindTypes = array(
		'mysql' => array(
			'BOOL' => PDO::PARAM_BOOL,
			'LONG' => PDO::PARAM_INT,
			'VAR_STRING' => PDO::PARAM_STR,
			'BLOB' => PDO::PARAM_STR
		)
	);

	public function __construct($dbtype, $host, $port, $dbname, $username = '', $passwd = '', $options = array(), $cacheDirPath = '', $cacheTtl = '0', $charset = 'utf8')
	{
		try
		{
			// Connection string
			$dsn = "$dbtype:host=$host;port=$port;dbname=$dbname";

			// Default PDO class name
			$class = 'PDO';

			// Get driver name
			$driver = strtolower(trim(substr($dsn, 0, strpos($dsn, ':'))));

			// Check PDO
			if (!$driver || !class_exists('PDO') || !extension_loaded('pdo_' . $driver))
			{
				// Docs: http://sourceforge.net/projects/phppdo/
				require_once EXTERNAL_PATH . '/phppdo/phppdo.php';
				$class = 'PHPPDO';
			}

			// Init connection
			$this->m_dbConn = new $class($dsn, $username, $passwd, $options);

			// Set some vars for convenience
			$this->m_dbType = $dbtype;
			$this->m_dbName = $dbname;
			$this->m_bIsInTransaction = FALSE;
			$this->m_strTablePrefix = 't';

			// Set error handling
			$this->m_dbConn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

			// Set cache
			$this->m_cacheDirPath = $cacheDirPath;
			$this->m_cacheTtl = intval($cacheTtl);
			$this->m_bIsUseCache = $this->m_cacheTtl > 0 ? TRUE : FALSE;

			// Set encoding
			$this->m_dbConn->exec('SET NAMES ' . $charset);
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}

	public function quote($string, $parameter_type = null)
	{
		try
		{
			return $this->m_dbConn->quote($string, $parameter_type);
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}
	public function beginTransaction()
	{
		try
		{
			if ($this->m_bIsInTransaction)
				return $this->m_bIsInTransaction;

			if (!$this->m_dbConn->beginTransaction())
				throw new Exception('Cannot begin transaction');

			return $this->m_bIsInTransaction = TRUE;
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}
	public function commitTransaction()
	{
		try
		{
			if (!$this->m_bIsInTransaction)
				return $this->m_bIsInTransaction;

			if (!$this->m_dbConn->commit())
				throw new Exception('Cannot commit transaction');

			return $this->m_bIsInTransaction = FALSE;
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}
	public function rollbackTransaction()
	{
		try
		{
			if (!$this->m_bIsInTransaction)
				return $this->m_bIsInTransaction;

			if (!$this->m_dbConn->rollBack())
				throw new Exception('Cannot rollback transaction');

			return $this->m_bIsInTransaction = FALSE;
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}
	public function isInTransaction()
	{
		try
		{
			return $this->m_bIsInTransaction;
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}

	public function getTables($strFilter = '', $bIsUseLeftPersent = FALSE, $bIsUseRightPersent = FALSE, $cacheTtl = NULL)
	{
		try
		{
			$sql = 'SHOW TABLES' . ((isset($strFilter) && !empty($strFilter)) ? ' FROM ' . $this->m_dbName . ' LIKE ' . ($bIsUseLeftPersent ? '\'%' : '\'') . $strFilter . ($bIsUseRightPersent ? '%\'' : '\'') : '');
			$sth = $this->m_dbConn->prepare($sql);
			return $this->executePDOStatement($sth, '', PDO::FETCH_COLUMN, $cacheTtl);
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}
	public function getTablePrefix()
	{
		return $this->m_strTablePrefix;
	}
	public function getTableName($name, $bIsWithPrefix = TRUE)
	{
		try
		{
			$name = strtolower($name);

			$tableName = '';

			// Get all tables
			$aTables = $this->getTables($name, TRUE);

			// Check table
			if ($bIsWithPrefix && FTArrayUtils::containsValueCI('t' . $name, $aTables))
				$tableName = 't' . $name;
			elseif (FTArrayUtils::containsValueCI($name, $aTables))
				$tableName = $name;
			else
				throw new Exception('Table not found: ' . $name);

			return $tableName;
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}
	public function getTableMeta($tableName, $cacheTtl = NULL)
	{
		try
		{
			$res = array();

			switch ($this->m_dbType)
			{
				case 'mysql':
					{
						// http://www.sitepoint.com/forums/showthread.php?497257-PDO-getColumnMeta-bug

						// Fetch all columns and parse them
						$sth = $this->m_dbConn->prepare('SHOW COLUMNS FROM ' . $this->getTableName($tableName));
						$columns = $this->executePDOStatement($sth, '', PDO::FETCH_ASSOC, $cacheTtl);
						foreach ($columns as $key => $col)
						{
							// Insert into fields array
							$colname = $col['Field'];
							$colType = $col['Type'];

							$colInfo = array();
							$colParts = explode(' ', $colType);
							if ($fparen = strpos($colParts[0], '('))
							{
								$colInfo['type'] = substr($colParts[0], 0, $fparen);
								$colInfo['pdoType'] = '';
								$colInfo['length'] = str_replace(')', '', substr($colParts[0], $fparen + 1));
								$colInfo['attributes'] = isset($colParts[1]) ? $colParts[1] : NULL;
							}
							else
								$colInfo['type'] = $colParts[0];

							$pdoBindTypes = array(
								'char' => PDO::PARAM_STR,
								'int' => PDO::PARAM_INT,
								'bool' => PDO::PARAM_BOOL,
								'date' => PDO::PARAM_STR,
								'time' => PDO::PARAM_INT,
								'text' => PDO::PARAM_STR,
								'blob' => PDO::PARAM_LOB,
								'binary' => PDO::PARAM_LOB
							);

							foreach ($pdoBindTypes as $pKey => $pType)
							{
								if (strpos(' ' . strtolower($colInfo['type']) . ' ', $pKey))
								{
									$colInfo['pdoType'] = $pType;
									break;
								}
								else
									$colInfo['pdoType'] = PDO::PARAM_STR;
							}

							// Set field types
							$res[$colname] = $colInfo;
						}
					}
					break;
				default:
					throw new Exception('Not implemented db type');
					break;
			}

			return $res;
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}

	public function executeQuery($sql, $pdoFetchType = NULL)
	{
		try
		{
			if ($pdoFetchType != NULL)
				return $this->m_dbConn->query($sql, $pdoFetchType);

			return $this->m_dbConn->query($sql);
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}
	public function executePDOStatement(PDOStatement $sth, $cacheAdditionalUniqueKey = '', $pdoFetchType = PDO::FETCH_ASSOC, $cacheTtl = NULL)
	{
		try
		{
			$data = NULL;

			if ($this->m_bIsUseCache)
			{
				// Get chache params
				$cacheFilePath = $this->m_cacheDirPath . '/' . md5($sth->queryString . $cacheAdditionalUniqueKey);
				$ttl = !is_null($cacheTtl) ? $cacheTtl : $this->m_cacheTtl;

				if (self::isCacheValid($cacheFilePath, $ttl))
				{
					try
					{
						// Get data from cache
						return self::getCache($cacheFilePath);
					}
					catch (Exception $ex2)
					{
						// Suspend exception
					}
				}
			}

			// Execute query (!)
			$sth->execute();
			$data = $sth->fetchAll($pdoFetchType);

			// Create cache
			if ($this->m_bIsUseCache)
				self::setCache($cacheFilePath, $data);

			return $data;
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}

	/**
	 * Execute sql SELECT statement
	 */
	public function get($params, $pdoFetchType = PDO::FETCH_ASSOC, $cacheTtl = NULL)
	{
		try
		{
			if (!FTArrayUtils::checkData($params))
				throw new Exception('No query params');

			if (!isset($params[ParamsSql::TABLE]) || empty($params[ParamsSql::TABLE]))
				throw new Exception('No ' . ParamsSql::TABLE);

			// Get table name
			$tableName = $this->getTableName($params[ParamsSql::TABLE]);

			// Get fields
			$strFields = isset($params[ParamsSql::FIELDS]) && !empty($params[ParamsSql::FIELDS]) ? $params[ParamsSql::FIELDS] : '*';

			// Assemble query
			$sql = 'SELECT ' . $strFields . ' FROM ' . $tableName;
			$sql .= isset($params[ParamsSql::RESTRICTION]) && !empty($params[ParamsSql::RESTRICTION]) ? ' WHERE ' . $params[ParamsSql::RESTRICTION] : '';
			$sql .= isset($params[ParamsSql::GROUP_BY]) && !empty($params[ParamsSql::GROUP_BY]) ? ' GROUP BY ' . $params[ParamsSql::GROUP_BY] : '';
			$sql .= isset($params[ParamsSql::HAVING]) && !empty($params[ParamsSql::HAVING]) ? ' HAVING ' . $params[ParamsSql::HAVING] : '';
			$sql .= isset($params[ParamsSql::ORDER_BY]) && !empty($params[ParamsSql::ORDER_BY]) ? ' ORDER BY ' . $params[ParamsSql::ORDER_BY] : '';
			$sql .= isset($params[ParamsSql::LIMIT]) && !empty($params[ParamsSql::LIMIT]) ? ' LIMIT ' . $params[ParamsSql::LIMIT] : '';

			// Prepare query
			$sth = $this->m_dbConn->prepare($sql);

			// Key-info for cache file
			$cacheAddKey = '';

			// Bind values
			if (isset($params[ParamsSql::RESTRICTION]) && !empty($params[ParamsSql::RESTRICTION]) && isset($params[ParamsSql::RESTRICTION_DATA]) && count($params[ParamsSql::RESTRICTION_DATA]))
			{
				$pdoParams = $this->getTableMeta($tableName);
				foreach ($params[ParamsSql::RESTRICTION_DATA] as $k => $v)
				{
					$sth->bindValue($k, $v, (isset($pdoParams[$k]['pdoType']) ? $pdoParams[$k]['pdoType'] : NULL));

					$cacheAddKey .= CRLF . $k . '=' . $v;
				}
			}

			return FTStringUtils::stripSlashes($this->executePDOStatement($sth, $cacheAddKey, $pdoFetchType, $cacheTtl));
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}
	/**
	 * Execute sql INSERT statement
	 */
	public function add($params, $data)
	{
		// Syntax SQL:
		// 1: INSERT [LOW_PRIORITY | DELAYED] [IGNORE]
		// 		[INTO] tbl_name [(col_name,...)]
		// 		VALUES (expression,...),(...),...
		// 		[ ON DUPLICATE KEY UPDATE col_name=expression, ... ]
		// 2: INSERT [LOW_PRIORITY | DELAYED] [IGNORE]
		// 		[INTO] tbl_name [(col_name,...)]
		// 		SELECT ...
		// 3: INSERT [LOW_PRIORITY | DELAYED] [IGNORE]
		// 		[INTO] tbl_name
		// 		SET col_name=(expression | DEFAULT), ...
		// 		[ ON DUPLICATE KEY UPDATE col_name=expression, ... ]

		try
		{
			if (!FTArrayUtils::checkData($params))
				throw new Exception('No query params');

			if (!FTArrayUtils::checkData($data))
				throw new Exception('No data');

			if (@empty($params[ParamsSql::TABLE]))
				throw new Exception('No ' . ParamsSql::TABLE);

			// Get table name
			$tableName = $this->getTableName($params[ParamsSql::TABLE]);

			// Get table meta
			$pdoParams = $this->getTableMeta($tableName);

			$strFieldNames = '';
			$strFieldValues = '';
			foreach ($data as $k => $v)
			{
				if (!array_key_exists($k, $pdoParams))
					continue;

				$strFieldNames .= (empty($strFieldNames) ? '' : ', ') . $k;
				$strFieldValues .= (empty($strFieldValues) ? '' : ', ') . ':' . $k;
			}

			// Local transaction - dont commit if external one setted (!)
			$bIlocalTran = TRUE;

			if (!$this->m_bIsInTransaction)
				$this->beginTransaction();
			else
				$bIlocalTran = FALSE;

			$sqlInsert = 'INSERT INTO ' . $tableName . ' (' . $strFieldNames . ') VALUES (' . $strFieldValues . ')';

			$sth = $this->m_dbConn->prepare($sqlInsert);

			foreach ($data as $k => $v)
			{
				if (!array_key_exists($k, $pdoParams))
					continue;
				$sth->bindValue(':' . $k, $v, (isset($pdoParams[$k]['pdoType']) ? $pdoParams[$k]['pdoType'] : NULL));
			}

			$sth->execute();
			$sth->closeCursor();

			if ($sth->errorCode() == '00000')
			{
				$sqlSelect = 'SELECT * FROM ' . $tableName . ' WHERE _id=' . $this->m_dbConn->lastInsertId();
				$res = $this->executeQuery($sqlSelect)->fetchAll(PDO::FETCH_ASSOC);

				if ($bIlocalTran)
					$this->commitTransaction();

				// Remove cache
				self::clearCache($this->m_cacheDirPath);

				return $res;
			}

			$this->rollbackTransaction();
			return FALSE;
		}
		catch (Exception $ex)
		{
			if ($this->m_bIsInTransaction)
				$this->rollbackTransaction();
			throw $ex;
		}
	}
	/**
	 * Execute sql UPDATE statement
	 */
	public function update($params, $data)
	{
		// Syntax SQL: 	
		// UPDATE [LOW_PRIORITY] [IGNORE] tbl_name
		// 		SET col_name1=expr1 [, col_name2=expr2, ...]
		// 		[WHERE where_definition]
		// 		[LIMIT #]

		try
		{
			if (!FTArrayUtils::checkData($params))
				throw new Exception('No query params');

			if (!FTArrayUtils::checkData($data))
				throw new Exception('No data');

			if (@empty($params[ParamsSql::TABLE]))
				throw new Exception('No ' . ParamsSql::TABLE);

			if (@empty($params[ParamsSql::RESTRICTION]))
				throw new Exception('Restriction cannot be empty');

			// Get table name
			$tableName = $this->getTableName($params[ParamsSql::TABLE]);

			// Get table meta
			$pdoParams = $this->getTableMeta($tableName);

			// Get field=>value pairs
			$strNameValuePair = '';
			foreach ($data as $k => $v)
			{
				if (!array_key_exists($k, $pdoParams))
					continue;
				$strNameValuePair .= (empty($strNameValuePair) ? '' : ', ') . $k . '=' . ':' . $k;
			}

			$sqlUpdate = 'UPDATE ' . $tableName . ' SET ' . $strNameValuePair . ' WHERE ' . $params[ParamsSql::RESTRICTION];

			$sth = $this->m_dbConn->prepare($sqlUpdate);

			// Bind values
			foreach ($data as $k => $v)
			{
				if (!array_key_exists($k, $pdoParams))
					continue;
				$sth->bindValue(':' . $k, $v, (isset($pdoParams[$k]['pdoType']) ? $pdoParams[$k]['pdoType'] : NULL));
			}

			$sth->execute();
			$sth->closeCursor();

			if ($sth->errorCode() == '00000')
			{
				// Remove cache
				self::clearCache($this->m_cacheDirPath);

				$sqlSelect = 'SELECT * FROM ' . $tableName . ' WHERE ' . $params[ParamsSql::RESTRICTION];
				return $this->executeQuery($sqlSelect)->fetchAll(PDO::FETCH_ASSOC);
			}
			//else
			//{
			//echo '<pre>'; print_r($sth->errorInfo()); echo '</pre>';
			//}

			return FALSE;
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}

	/**
	 * Check cache data
	 * @param String $cacheFilePath - path to file
	 * @param String $cacheTtl - time to live (seconds)
	 * @return boolean
	 */
	static protected function isCacheValid($cacheFilePath, $cacheTtl)
	{
		try
		{
			if (!is_readable($cacheFilePath))
			{
				//FTException::saveEx(new Exception('Cannot read cache file: ' . $cacheFilePath));
				return FALSE;
			}

			if ((time() - filemtime($cacheFilePath)) >= $cacheTtl)
				return FALSE;

			return TRUE;
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}
	/**
	 * Read data from cache file
	 * @param String $cacheFilePath - path to file
	 * @return String data or FALSE on failure
	 */
	static protected function getCache($cacheFilePath)
	{
		try
		{
			if (@$fp = fopen($cacheFilePath, 'r'))
			{
				@flock($fp, LOCK_SH);

				$oData = @fread($fp, filesize($cacheFilePath));

				if ($oData === FALSE)
					FTException::saveEx(new Exception('Cannot read cache file: ' . $cacheFilePath));

				@flock($fp, LOCK_UN);
				@fclose($fp);

				return unserialize($oData);
			}

			return FALSE;
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}
	/**
	 * Save data to cache file
	 * @param String $cacheFilePath - path to file
	 * @param Object $strData - string data to save
	 * @return boolean
	 */
	static protected function setCache($cacheFilePath, $oData)
	{
		try
		{
			if (@$fp = fopen($cacheFilePath, 'w'))
			{
				@flock($fp, LOCK_EX);

				$writeResult = @fwrite($fp, serialize($oData));

				if (!$writeResult)
					FTException::saveEx(new Exception('Cannot serialize data: ' . $oData));

				@flock($fp, LOCK_UN);
				@fclose($fp);

				return TRUE;
			}

			FTException::saveEx(new Exception('Cannot write cache file: ' . $cacheFilePath));
			return FALSE;
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}
	/**
	 * Clear all cache files
	 * @param String $cacheDirPath - directory path
	 */
	static protected function clearCache($cacheDirPath)
	{
		try
		{
			// Dir path
			$dirPath = $cacheDirPath;

			// Check dir
			FTException::throwOnTrue(!is_dir($dirPath), 'Not a directory: ' . $dirPath);

			// Add end slash
			if (substr($dirPath, strlen($dirPath) - 1, 1) != '/' && substr($dirPath, strlen($dirPath) - 1, 1) != '\\')
				$dirPath .= '/';

			// Get files
			$files = glob($dirPath . '*', GLOB_MARK);

			// Remove folders & files
			foreach ($files as $file)
				if (is_dir($file))
					self::clearCache($file);
				else
					unlink($file);
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}
}
