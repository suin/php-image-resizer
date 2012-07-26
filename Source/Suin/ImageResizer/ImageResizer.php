<?php

namespace Suin\ImageResizer;

use \RuntimeException;

class ImageResizer implements \Suin\ImageResizer\ImageResizerInterface
{
	protected static $supportTypes = array(
		IMAGETYPE_GIF,
		IMAGETYPE_JPEG,
		IMAGETYPE_PNG,
	);

	/** @var string */
	protected $filename;
	/** @var int */
	protected $originalHeight;
	/** @var int */
	protected $originalWidth;
	/** @var int */
	protected $type;
	/** @var string */
	protected $mime;
	/** @var null|int */
	protected $maxHeight;
	/** @var null|int */
	protected $maxWidth;

	/**
	 * Return new ImageResizer object
	 * @param string $filename Image file name to resize
	 * @throws \RuntimeException
	 */
	public function __construct($filename)
	{
		if ( is_file($filename) === false )
		{
			throw new RuntimeException(sprintf("No such file: %s", $filename));
		}

		if ( is_readable($filename) === false )
		{
			throw new RuntimeException(sprintf("Not readable: %s", $filename));
		}

		if ( is_writable($filename) === false )
		{
			throw new RuntimeException(sprintf("Not writable: %s", $filename));
		}

		$info = @getimagesize($filename);

		if ( $info === false )
		{
			throw new RuntimeException(sprintf("Not supported type of image: %s", $filename));
		}

		if ( in_array($info[2], static::$supportTypes) === false )
		{
			throw new RuntimeException(sprintf("Not supported type of image: %s", $filename));
		}

		$this->filename = $filename;
		$this->originalHeight = $info[1];
		$this->originalWidth  = $info[0];
		$this->type = $info[2];
		$this->mime = $info['mime'];
	}

	/**
	 * Set max height
	 * @param int $height Max height(pixel)
	 * @return $this Must return self instance
	 */
	public function maxHeight($height)
	{
		$this->maxHeight = $height;
		return $this;
	}

	/**
	 * Set max width
	 * @param int $width Max width(pixel)
	 * @return $this Must return self instance
	 */
	public function maxWidth($width)
	{
		$this->maxWidth = $width;
		return $this;
	}

	/**
	 * Update image size
	 * @return bool Returns TRUE on success, otherwise returns FALSE
	 */
	public function resize()
	{
		if ( $this->_needsResize() === false )
		{
			return true;
		}

		$newSize = $this->_calculateNewSizeByMaxSize();

		// TODO >> Consider open closed principle
		switch ( $this->type )
		{
			case IMAGETYPE_JPEG:
				$source = imagecreatefromjpeg($this->filename);
				break;
			case IMAGETYPE_GIF:
				$source = imagecreatefromgif($this->filename);
				break;
			case IMAGETYPE_PNG:
				$source = imagecreatefrompng($this->filename);
				break;
			default:
				throw new RuntimeException(sprintf("Not supported type of image: %s", $this->filename));
		}

		$canvas = imagecreatetruecolor($newSize['width'], $newSize['height']); // Requires GD 2.0.28 or later

		if ( imagecopyresampled($canvas, $source, 0, 0, 0, 0, $newSize['width'], $newSize['height'], $this->originalWidth, $this->originalHeight) === false )
		{
			return false;
		}

		// TODO >> Consider open closed principle
		switch ( $this->type )
		{
			case IMAGETYPE_JPEG:
				imagejpeg($canvas, $this->filename);
				break;
			case IMAGETYPE_GIF:
				imagegif($canvas, $this->filename);
				break;
			case IMAGETYPE_PNG:
				imagepng($canvas, $this->filename);
				break;
			default:
				throw new RuntimeException(sprintf("Not supported type of image: %s", $this->filename));
		}

		return true;
	}

	/**
	 * Determine if this image needs to be resized
	 * @return bool Returns TRUE if it needs to be resized, otherwise returns FALSE
	 */
	protected function _needsResize()
	{
		if ( $this->maxHeight > 0 and $this->originalHeight > $this->maxHeight )
		{
			return true;
		}

		if ( $this->maxWidth > 0 and $this->originalWidth > $this->maxWidth )
		{
			return true;
		}

		return false;
	}

	/**
	 * Calculate and return new height and width size based on mas sizes
	 * @return int[]
	 */
	protected function _calculateNewSizeByMaxSize()
	{
		$scales = array();

		if ( $this->maxWidth > 0 )
		{
			$scales[] = $this->maxWidth / $this->originalWidth;
		}

		if ( $this->maxHeight > 0 )
		{
			$scales[] = $this->maxHeight / $this->originalHeight;
		}

		if ( count($scales) === 0 )
		{
			return array(
				'height' => $this->originalHeight,
				'width'  => $this->originalWidth,
			);
		}

		$scale = min($scales);

		return array(
			'height' => intval($this->originalHeight * $scale),
			'width'  => intval($this->originalWidth * $scale),
		);
	}
}
