# \Suin\ImageResizer

This is a simple image resizing library for PHP.

## Requirements

* PHP 5.3 or later
* GD 2.0.28 or later
* GD needs to support JPEG, GIF, PNG

## Installation

The recommended way to install this library is through composer. Just create a `composer.json` file and run the `php composer.phar install` command to install it:

```json
{
	"require": {
		"php":          ">=5.3.0",
		"suin/php-image-resizer": ">=1.0.0"
	}
}
```

## How to Use

```php
<?php
use \Suin\ImageResizer\ImageResizer;

$resizer = new ImageResizer('/path/to/your/image.jpeg');

if ( $resizer->maxWidth(480)->maxHeihgt(640)->resize() === false ) {
	// error
} else {
	// success
}
```

## License

MIT License