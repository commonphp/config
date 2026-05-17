# Loading A File

This example uses a loader and a JSON driver to read and write one config file.

```php
<?php

declare(strict_types=1);

use CommonPHP\Config\ConfigLoader;
use CommonPHP\Drivers\Config\JSON\JsonConfigurationDriver;

$loader = new ConfigLoader();
$loader->registerDriver('json', new JsonConfigurationDriver());

$config = $loader->load(__DIR__ . '/app.json');

$loader->write(__DIR__ . '/cache.json', [
    'enabled' => true,
    'ttl' => 300,
]);
```

To store loaded data in the loader repository:

```php
$loader->loadInto(__DIR__ . '/app.json', 'app');

$name = $loader->repository()->get('app.name');
```

If the file has no extension or uses a non-standard extension, pass the format explicitly:

```php
$config = $loader->load(__DIR__ . '/settings', 'json');
```
