<?php

namespace Suin\ImageResizer;

class ImageResizerTest extends \XoopsUnit\TestCase
{
	private $imageResizerClass = '\Suin\ImageResizer\ImageResizer';

	public static function tearDownAfterClass()
	{
		chmod(__DIR__ . '/images/not_readable.jpeg', 0644);
		chmod(__DIR__ . '/images/not_writable.jpeg', 0644);
	}

	/**
	 * @return \PHPUnit_Framework_MockObject_MockBuilder
	 */
	public function getMockBuilderForImageResizer()
	{
		return $this
			->getMockBuilder($this->imageResizerClass)
			->setMethods(null)
			->disableOriginalConstructor();
	}

	/**
	 * @return \PHPUnit_Framework_MockObject_MockObject
	 */
	public function newImageResizerStub()
	{
		return $this
			->getMockBuilderForImageResizer()
			->getMock();
	}

	public function test__construct()
	{
		$filename = __DIR__ . '/images/plant.jpeg';
		$resizer = new $this->imageResizerClass($filename);
		$this->assertAttributeSame($filename, 'filename', $resizer);
	}

	/**
	 * @expectedException RuntimeException
	 * @expectedExceptionMessage No such file: such_file_does_not_exists
	 */
	public function test__construct_with_not_existing_file()
	{
		$filename = 'such_file_does_not_exists';
		$resizer = new $this->imageResizerClass($filename);
	}

	public function test__construct_with_not_file()
	{
		$filename = __DIR__ . '/images/this_is_directory';
		$this->setExpectedException('RuntimeException', sprintf('No such file: %s', $filename));
		$resizer = new $this->imageResizerClass($filename);
	}

	public function test__construct_with_not_readable_file()
	{
		$filename = __DIR__ . '/images/not_readable.jpeg';
		chmod($filename, 0222);
		$this->assertFalse(is_readable($filename));
		$this->setExpectedException('RuntimeException', sprintf('Not readable: %s', $filename));
		$resizer = new $this->imageResizerClass($filename);
	}

	public function test__construct_with_not_writable_file()
	{
		$filename = __DIR__ . '/images/not_writable.jpeg';
		chmod($filename, 0444);
		$this->assertFalse(is_writable($filename));
		$this->setExpectedException('RuntimeException', sprintf('Not writable: %s', $filename));
		$resizer = new $this->imageResizerClass($filename);
	}

	public function test__construct_with_not_image()
	{
		$filename = __DIR__ . '/images/plant.jp2';
		$this->setExpectedException('RuntimeException', sprintf('Not supported type of image: %s', $filename));
		$resizer = new $this->imageResizerClass($filename);
	}

	public function test__construct_with_not_supported_image()
	{
		$filename = __DIR__ . '/images/this_is_not_image.html';
		$this->setExpectedException('RuntimeException', sprintf('Not supported type of image: %s', $filename));
		$resizer = new $this->imageResizerClass($filename);
	}

	/**
	 * @param array $condition
	 * @dataProvider dataForTest__construct_setup_image_info
	 */
	public function test__construct_setup_image_info(array $condition)
	{
		$resizer = new $this->imageResizerClass($condition['filename']);
		$this->assertAttributeSame($condition['originalHeight'], 'originalHeight', $resizer);
		$this->assertAttributeSame($condition['originalWidth'], 'originalWidth', $resizer);
		$this->assertAttributeSame($condition['type'], 'type', $resizer);
		$this->assertAttributeSame($condition['mime'], 'mime', $resizer);
	}

	public static function dataForTest__construct_setup_image_info()
	{
		return array(
			array(array(
				'filename'       => __DIR__ . '/images/plant.jpeg',
				'originalHeight' => 480,
				'originalWidth'  => 640,
				'type'           => IMAGETYPE_JPEG,
				'mime'           => 'image/jpeg',
			)),
			array(array(
				'filename'       => __DIR__ . '/images/plant.png',
				'originalHeight' => 480,
				'originalWidth'  => 640,
				'type'           => IMAGETYPE_PNG,
				'mime'           => 'image/png',
			)),
			array(array(
				'filename'       => __DIR__ . '/images/plant.gif',
				'originalHeight' => 480,
				'originalWidth'  => 640,
				'type'           => IMAGETYPE_GIF,
				'mime'           => 'image/gif',
			)),
		);
	}

	public function testMaxHeight()
	{
		$maxHeight = 1234;
		$resizer = $this->newImageResizerStub();
		$return = $resizer->maxHeight($maxHeight);
		$this->assertAttributeSame($maxHeight, 'maxHeight', $resizer);
		$this->assertSame($resizer, $return);
	}

	public function testMaxWidth()
	{
		$maxWidth = 1234;
		$resizer = $this->newImageResizerStub();
		$return = $resizer->maxWidth($maxWidth);
		$this->assertAttributeSame($maxWidth, 'maxWidth', $resizer);
		$this->assertSame($resizer, $return);
	}

	/**
	 * @param array $condition
	 * @dataProvider dataForTestResize
	 */
	public function testResize(array $condition)
	{
		// prepare temporary image
		$filename = sys_get_temp_dir().'/'. uniqid();
		copy($condition['filename'], $filename);

		// check precondition
		list($width, $height, $type) = getimagesize($filename);
		$this->assertSame($condition['originalWidth'], $width);
		$this->assertSame($condition['originalHeight'], $height);
		$this->assertSame($condition['type'], $type);

		// test resize
		$resizer = $this->newImageResizerStub();
		$this->reveal($resizer)->attr('filename', $filename);
		$this->reveal($resizer)->attr('type', $condition['type']);
		$this->reveal($resizer)->attr('originalWidth', $condition['originalWidth']);
		$this->reveal($resizer)->attr('originalHeight', $condition['originalHeight']);
		$this->reveal($resizer)->attr('maxWidth', $condition['maxWidth']);
		$this->reveal($resizer)->attr('maxHeight', $condition['maxHeight']);

		$this->assertTrue($resizer->resize());

		// check effect
		list($width, $height) = getimagesize($filename);
		$this->assertSame($condition['expectedWidth'], $width);
		$this->assertSame($condition['expectedHeight'], $height);

		// delete image
		unlink($filename);
	}

	public static function dataForTestResize()
	{
		return array(
			array(array(
				'filename'       => __DIR__.'/images/plant.jpeg',
				'type'           => IMAGETYPE_JPEG,
				'expectedWidth'  => 320,
				'expectedHeight' => 240,
				'originalWidth'  => 640,
				'originalHeight' => 480,
				'maxWidth'       => 320,
				'maxHeight'      => 240,
			)),
			array(array(
				'filename'       => __DIR__.'/images/plant.gif',
				'type'           => IMAGETYPE_GIF,
				'expectedWidth'  => 320,
				'expectedHeight' => 240,
				'originalWidth'  => 640,
				'originalHeight' => 480,
				'maxWidth'       => 320,
				'maxHeight'      => 240,
			)),
			array(array(
				'filename'       => __DIR__.'/images/plant.png',
				'type'           => IMAGETYPE_PNG,
				'expectedWidth'  => 320,
				'expectedHeight' => 240,
				'originalWidth'  => 640,
				'originalHeight' => 480,
				'maxWidth'       => 320,
				'maxHeight'      => 240,
			)),
			// long image
			array(array(
				'filename'       => __DIR__.'/images/building@480x640.jpg',
				'type'           => IMAGETYPE_JPEG,
				'expectedWidth'  => 360,
				'expectedHeight' => 480,
				'originalWidth'  => 480,
				'originalHeight' => 640,
				'maxWidth'       => 640,
				'maxHeight'      => 480,
			)),
		);
	}

	public function testResize_when_not_needs_to_resize_just_returns_true()
	{
		$resizer = $this
			->getMockBuilderForImageResizer()
			->setMethods(array('_needsResize'))
			->getMock();
		$resizer
			->expects($this->once())
			->method('_needsResize')
			->will($this->returnValue(false));
		$this->assertTrue($resizer->resize());
	}

	/**
	 * @param $expect
	 * @param array $condition
	 * @dataProvider dataForTest_needsResize
	 */
	public function test_needsResize($expect, array $condition)
	{
		$resizer = $this->newImageResizerStub();
		$this->reveal($resizer)->attr('originalHeight', $condition['originalHeight']);
		$this->reveal($resizer)->attr('originalWidth', $condition['originalWidth']);
		$this->reveal($resizer)->attr('maxHeight', $condition['maxHeight']);
		$this->reveal($resizer)->attr('maxWidth', $condition['maxWidth']);
		$this->assertSame($expect, $this->reveal($resizer)->call('_needsResize'));
	}

	public static function dataForTest_needsResize()
	{
		return array(
			// max size equals to original size
			array(false, array(
				'originalHeight' => 1,
				'originalWidth'  => 1,
				'maxHeight'      => 1,
				'maxWidth'       => 1,
			)),
			// original height is bigger than max height
			array(true, array(
				'originalHeight' => 2,
				'originalWidth'  => 1,
				'maxHeight'      => 1,
				'maxWidth'       => 1,
			)),
			// original width is bigger than max width
			array(true, array(
				'originalHeight' => 1,
				'originalWidth'  => 2,
				'maxHeight'      => 1,
				'maxWidth'       => 1,
			)),
			// original both sizes are bigger than max sizes
			array(true, array(
				'originalHeight' => 2,
				'originalWidth'  => 2,
				'maxHeight'      => 1,
				'maxWidth'       => 1,
			)),
			// original both sizes are smaller than max sizes
			array(false, array(
				'originalHeight' => 1,
				'originalWidth'  => 1,
				'maxHeight'      => 2,
				'maxWidth'       => 2,
			)),
			// no limit for max height
			array(true, array(
				'originalHeight' => 2,
				'originalWidth'  => 2,
				'maxHeight'      => null,
				'maxWidth'       => 1,
			)),
			// no limit for max width
			array(true, array(
				'originalHeight' => 2,
				'originalWidth'  => 2,
				'maxHeight'      => 1,
				'maxWidth'       => null,
			)),
			// no limit for max height
			array(false, array(
				'originalHeight' => 1,
				'originalWidth'  => 1,
				'maxHeight'      => null,
				'maxWidth'       => 2,
			)),
			// no limit for max width
			array(false, array(
				'originalHeight' => 1,
				'originalWidth'  => 1,
				'maxHeight'      => 2,
				'maxWidth'       => null,
			)),
			// no limit for both height and max
			array(false, array(
				'originalHeight' => 1,
				'originalWidth'  => 1,
				'maxHeight'      => null,
				'maxWidth'       => null,
			)),
		);
	}

	/**
	 * @param $expect
	 * @param $condition
	 * @dataProvider dataForTest_calculateNewSizeByMaxSize
	 */
	public function test_calculateNewSizeByMaxSize($expect, $condition)
	{
		$resizer = $this->newImageResizerStub();
		$this->reveal($resizer)->attr('originalHeight', $condition['originalHeight']);
		$this->reveal($resizer)->attr('originalWidth', $condition['originalWidth']);
		$this->reveal($resizer)->attr('maxHeight', $condition['maxHeight']);
		$this->reveal($resizer)->attr('maxWidth', $condition['maxWidth']);
		$this->assertSame($expect, $this->reveal($resizer)->call('_calculateNewSizeByMaxSize'));
	}

	public static function dataForTest_calculateNewSizeByMaxSize()
	{
		return array(
			// max size equals to original size
			array(
				array('width' => 1, 'height' => 1, ),
				array(
					'originalHeight' => 1,
					'originalWidth'  => 1,
					'maxHeight'      => 1,
					'maxWidth'       => 1,
				)),
			// 1/2 scale
			array(
				array('width' => 480, 'height' => 640,),
				array(
					'originalHeight' => 1280,
					'originalWidth'  => 960,
					'maxHeight'      => 640,
					'maxWidth'       => 480,
				)),
			// 75 percent scale
			array(
				array('width' => 360, 'height' => 480,),
				array(
					'originalHeight' => 640,
					'originalWidth'  => 480,
					'maxHeight'      => 480,
					'maxWidth'       => 640,
				)),
			// no limit for height
			array(
				array('width' => 480, 'height' => 640, ),
				array(
					'originalHeight' => 1280,
					'originalWidth'  => 960,
					'maxHeight'      => null,
					'maxWidth'       => 480,
				)),
			// no limit for width
			array(
				array('width' => 480, 'height' => 640, ),
				array(
					'originalHeight' => 1280,
					'originalWidth'  => 960,
					'maxHeight'      => 640,
					'maxWidth'       => null,
				)),
			// no limit for both height and width, returns original size
			array(
				array('width' => 960, 'height' => 1280, ),
				array(
					'originalHeight' => 1280,
					'originalWidth'  => 960,
					'maxHeight'      => null,
					'maxWidth'       => null,
				)),
		);
	}
}
