<?php

use CommonPHP\Configuration\Attributes\ConfigurationDriverAttribute;
use CommonPHP\Configuration\ConfigurationManager;
use CommonPHP\Configuration\Contracts\ConfigurationDriverContract;
use CommonPHP\Drivers\ServiceProviders\DriverManagerServiceProvider;
use CommonPHP\ServiceManagement\ServiceManager;

require '../vendor/autoload.php';

#[ConfigurationDriverAttribute('php')]
class GeneralUsageExampleDriver implements ConfigurationDriverContract
{

    #[\Override] function canSave(): bool
    {
        return true;
    }

    #[\Override] function load(string $filename): array
    {
        echo 'Loading: '.$filename.PHP_EOL;
        return [
            'foo' => 'bar',
            'answerToLifeUniverseEverything' => 42
        ];
    }

    #[\Override] function save(string $filename, array $data): void
    {
        echo 'Saving: '.json_encode($data).' to file: '.$filename.PHP_EOL;
    }
}

// Instantiate the ServiceManager and register the DriverManagerServiceProvider for dependency management.
$serviceManager = new ServiceManager();
$serviceManager->providers->registerProvider(DriverManagerServiceProvider::class);
$serviceManager->register(ConfigurationManager::class);

$config = $serviceManager->get(ConfigurationManager::class);

$config->loadDriver(GeneralUsageExampleDriver::class);

$example = $config->get(__FILE__);
var_dump($example->data);

$example->save();
