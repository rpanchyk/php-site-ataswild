<?php
require_once dirname(__FILE__) . '/../inc/cde.inc.php';

/**
 * Works with file system
 */
class FTFileSystem extends FTFireTrot
{
	/**
	 * Get file list in specified folder
	 */
	static public function getFileList($dir, $mask = '', $is_recursive = FALSE)
	{
		// http://www.hawkee.com/snippet/1749/

		try
		{
			$result = array();

			if (is_dir($dir))
			{
				if ($handle = opendir($dir))
				{
					while (($file = readdir($handle)) !== FALSE)
					{
						if ($file != "." && $file != ".." && $file != "Thumbs.db" /*pesky windows, images..*/)
						{
							if ($is_recursive && is_dir($dir . '/' . $file))
							{
								$result[] = GetListFiles($dir . '/' . $file, $mask, $is_recursive);
							}
							else
							{
								if (!$mask)
								{
									$result[] = $file;
								}
								else
								{
									$info = pathinfo($dir . '/' . $file);
									if ($info['extension'] === $mask)
									{
										$result[] = $file;
									}
								}
							}
						}
					}
					closedir($handle);
				}
			}

			return $result;
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}

	/**
	 * Get filepath from input args
	 */
	static public function pathCombine()
	{
		try
		{
			$strResult = '';

			$args = func_get_args();
			foreach ($args as $value)
				$strResult .= rtrim($value, DS) . DS;

			return rtrim($strResult, DS);
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}
}
