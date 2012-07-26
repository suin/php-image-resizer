<?php

namespace Suin\ImageResizer;

interface ImageResizerInterface
{
	/**
	 * Return new ImageResizer object
	 * @param string $filename Image file name to resize
	 */
	public function __construct($filename);

	/**
	 * Set max width
	 * @param int $width Max width(pixel)
	 * @return $this Must return self instance
	 */
	public function maxWidth($width);

	/**
	 * Set max height
	 * @param int $height Max height(pixel)
	 * @return $this Must return self instance
	 */
	public function maxHeight($height);

	/**
	 * Update image size
	 * @return bool Returns TRUE on success, otherwise returns FALSE
	 */
	public function resize();
}
