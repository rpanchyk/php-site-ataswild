<?php
require_once dirname(__FILE__) . '/../inc/cde.inc.php';

/**
 * Works with images
 */
class FTImageUtils extends FTFireTrot
{
	/**
	 * Resize image
	 * @param int $nw - new width
	 * @param int $nh - new height
	 * @param string $source - path to original image
	 * @param string $dest - path to result image
	 */
	static function cropImage($nw, $nh, $source, $dest)
	{
		try
		{
			if (!file_exists($source))
				throw new Exception('File not found: ' . $source);

			// Get width/height of original image
			list($width_orig, $height_orig) = getimagesize($source);

			// Get width/height of new image
			$width_new = $nw;
			$height_new = $nh;

			// Create blank image
			$imgBlank = imagecreatetruecolor($width_new, $height_new);
			if (!$imgBlank)
				throw new Exception('Cannot create blank image');

			// Create new image resource
			$imgNew = self::createNewImageResource($source);

			// Get result image x & y
			$dst_x = 0;
			$dst_y = 0;
			if ($width_orig > $height_orig)
			{
				$hm = $height_orig / $height_new;
				$adjusted_width = $width_orig / $hm;
				$half_width = $adjusted_width / 2;
				$w_height = $width_new / 2;
				$int_width = $half_width - $w_height;

				$dst_x = -$int_width;
			}
			else
			{
				$wm = $width_orig / $width_new;
				$adjusted_height = $height_orig / $wm;
				$half_height = $adjusted_height / 2;
				$h_height = $height_new / 2;
				$int_height = $half_height - $h_height;

				$dst_y = -$int_height;
			}

			// Copy new in blank image & resize it
			$isCopied = imagecopyresampled($imgBlank, $imgNew, $dst_x, $dst_y, 0, 0, $adjusted_width, $height_new, $width_orig, $height_orig);
			if (!$isCopied)
				throw new Exception('Cannot copy new in blank image & resize it');

			// Save image
			$isSave = imagejpeg($imgBlank, $dest, 100);
			if (!$isSave)
				throw new Exception('Cannot save image: ' . $dest);

			return TRUE;
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}

	static function resizeImage($nw, $nh, $source, $dest, $isResizeIfNewIsBigger = FALSE)
	{
		try
		{
			if (!file_exists($source))
				throw new Exception('File not found: ' . $source);

			// Get width/height of original image
			list($width_orig, $height_orig) = getimagesize($source);

			// Get width/height of new image
			$width_new = $nw;
			$height_new = $nh;

			// Is resize perform?
			if ($width_new > $width_orig && $height_new > $height_orig && !$isResizeIfNewIsBigger)
				if (!copy($source, $dest))
					throw new Exception('Cannot copy file: ' . $source . ' to ' . $dest);
				else
					return TRUE;

			// Define image proportions
			$ratio_orig = $width_orig / $height_orig;
			if ($width_new / $height_new > $ratio_orig)
				$width_new = $height_new * $ratio_orig;
			else
				$height_new = $width_new / $ratio_orig;

			// Create blank image
			$imgBlank = imagecreatetruecolor($width_new, $height_new);
			if (!$imgBlank)
				throw new Exception('Cannot create blank image');

			// Create new image resource
			$imgNew = self::createNewImageResource($source);

			// Copy new in blank image & resize it
			$isCopied = imagecopyresampled($imgBlank, $imgNew, 0, 0, 0, 0, $width_new, $height_new, $width_orig, $height_orig);
			if (!$isCopied)
				throw new Exception('Cannot copy new in blank image & resize it');

			// Save image
			$isSave = imagejpeg($imgBlank, $dest, 100);
			if (!$isSave)
				throw new Exception('Cannot save image: ' . $dest);

			return TRUE;
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}

	/**
	 * Create a new image
	 * @param string $source - path to image
	 */
	static protected function createNewImageResource($source)
	{
		try
		{
			if (!file_exists($source))
				throw new Exception('File not found: ' . $source);

			// Result image
			$img = false;

			// Get image type
			$imageType = strtolower(end(explode('.', $source)));

			// Create new image from source
			switch ($imageType)
			{
				case 'gif':
					$img = imagecreatefromgif($source);
					break;
				case 'jpg':
				case 'jpeg':
					$img = imagecreatefromjpeg($source);
					break;
				case 'png':
					$img = imagecreatefrompng($source);
					break;
			}

			if (!$img)
				throw new Exception('Cannot create new image resource from: ' . $source);

			return $img;
		}
		catch (Exception $ex)
		{
			throw $ex;
		}
	}
}
