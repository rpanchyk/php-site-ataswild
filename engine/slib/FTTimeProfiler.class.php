<?php
require_once dirname(__FILE__) . '/../inc/cde.inc.php';

/**
 * Time profiler
 */
class FTTimeProfiler extends FTFireTrot
{
	const defaultPrecision = 3;

	private $m_timeStart;
	private $m_timeEnd;

	public function __construct($bIsStart = FALSE)
	{
		if ($bIsStart)
			$this->start();
	}

	public function start()
	{
		$this->m_timeStart = microtime(TRUE);
	}
	public function finish()
	{
		$this->m_timeEnd = microtime(TRUE);
	}

	public function getElapsedTime($nPrecision = self::defaultPrecision)
	{
		if (is_null($this->m_timeEnd))
			$this->finish();

		return round(($this->m_timeEnd - $this->m_timeStart), $nPrecision);
	}

	public function getElapsedTimeAsString($nPrecision = self::defaultPrecision)
	{
		return '<b>Time elapsed:</b> ' . $this->getElapsedTime($nPrecision) . ' seconds';
	}

	public function showElapsedTime($nPrecision = self::defaultPrecision)
	{
		echo $this->getElapsedTimeAsString($nPrecision);
	}

	public function showElapsedTimeStyled($nPrecision = self::defaultPrecision)
	{
		echo '<div style="height:5px;"></div><div style="background: #FFCC99; border:2px solid #FF6633; padding:3px; font:12px Verdana;">' . $this->getElapsedTimeAsString($nPrecision) . '</div>';
	}
}
