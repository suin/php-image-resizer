# \Suin\ImageResizer

This is a simple image resizing library for PHP.

## Requirements

* PHP 5.3 or later
* GD 2.0.28 or later
* GD needs to support JPEG, GIF, PNG

## Installation

`git clone` and drop `Source` directory to your project.

Please use PSR-0 compatible class loader to load this library.


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