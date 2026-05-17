# Provider With Schema Validation

This example loads application config through a provider, merges defaults, validates the loaded array, and reads typed values from the repository.

```php
<?php

declare(strict_types=1);

use CommonPHP\Config\ConfigDefinition;
use CommonPHP\Config\ConfigProvider;
use CommonPHP\Config\Contracts\AbstractConfigSchema;
use CommonPHP\Drivers\Config\PHP\PhpConfigurationDriver;
use CommonPHP\Runtime\Support\NativePathResolver;

final class AppConfigSchema extends AbstractConfigSchema
{
}

$provider = new ConfigProvider(new NativePathResolver(dirname(__DIR__)));
$provider->registerDriver('php', PhpConfigurationDriver::class);

$provider->define('app', ConfigDefinition::file(
    path: 'config/app.php',
    defaults: [
        'debug' => false,
        'timezone' => 'UTC',
    ],
    schema: new AppConfigSchema([
        'name' => 'required|string',
        'debug' => 'required|boolean',
        'timezone' => 'required|string',
    ]),
));

$provider->load('app');

$debug = $provider->repository()->value('app.debug')->asBool();
$timezone = $provider->repository()->value('app.timezone')->asString();
```

Schema validation runs after defaults are merged. This means schemas can require defaulted keys.
