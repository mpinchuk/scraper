# scraper
Provides an ability to scrap articles from GRBJ homepage

## Installation

This package is available for easy installation through [Packagist](http://packagist.com)

```bash
composer require mpinchuk/scraper
```

## Example Controller Usage
```php
<?php

namespace App\Http\Controllers;

use Mpinchuk\Scraper\GRBJ;

class SiteController extends Controller
{
    public function scrap()
    {
        $scraper = new GRBJ();
        $data = $scraper->scrap();
        var_dump($data);
        exit;
    }
}
```
