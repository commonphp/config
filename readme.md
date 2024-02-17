# CommonPHP Configuration Manager

The CommonPHP Configuration Manager (`comphp/config`) is a flexible and robust library designed to simplify the management of configuration files in PHP applications. It leverages the power of dynamic driver loading and custom behaviors to provide a highly adaptable solution for application configuration.

## Features

- **Dynamic Driver Support**: Easily extendable to support various configuration file formats through custom drivers.
- **Behavior Customization**: Control how the system handles duplicate file extensions and unsupported save operations with configurable behaviors.
- **Exception Handling**: Comprehensive exception handling for precise error tracking and management.
- **Attribute and Contract-Based Configuration**: Utilize PHP attributes and interfaces for defining configuration drivers, ensuring a clear and structured approach.

## Installation

Use Composer to install the Configuration Manager:

```
composer require comphp/config
```

## Basic Usage

Refer to the `examples/general-usage.php` file for a detailed example. Here's a quick overview:

1. **Define a Configuration Driver**:
   Implement the `ConfigurationDriverContract` in your driver class. Optionally, use the `ConfigurationDriverAttribute` to specify supported file extensions.

2. **Load and Use the Configuration Manager**:
    ```php
    use CommonPHP\Configuration\ConfigurationManager;
    use CommonPHP\Drivers\DriverManager;

    // Initialize the Driver Manager and Configuration Manager
    $driverManager = new DriverManager();
    $configurationManager = new ConfigurationManager($driverManager);

    // Load a custom driver
    $configurationManager->loadDriver(YourCustomDriver::class);

    // Get configuration
    $config = $configurationManager->get('/path/to/your/config.file');

    // Access configuration data
    echo $config->data['your_config_key'];
    ```

3. **Saving Configurations**:
   If your driver supports saving, you can persist modifications:
    ```php
    $config->data['new_key'] = 'new_value';
    $config->save(); // Make sure your driver implements saving logic
    ```

## Customizing Behavior

Customize how the Configuration Manager handles certain scenarios through properties:

- **Duplicate Extension Behavior**: Decide how to handle when multiple drivers claim the same file extension.
- **Configuration Cannot Save Behavior**: Define behavior for when a configuration cannot be saved (e.g., driver doesn't support saving).

## Creating Custom Drivers

Implement the `ConfigurationDriverContract` interface in your driver class and optionally use the `ConfigurationDriverAttribute` to specify which file extensions your driver supports.

## Handling Exceptions

The library defines a range of exceptions for fine-grained error handling, from driver load failures to access denied scenarios. Catch these exceptions to handle different error conditions gracefully.

## Contributing

Contributions to the CommonPHP Configuration Manager are welcome. Please follow the repository's contributing guidelines to submit bug reports, feature requests, or pull requests.

## License

The CommonPHP Configuration Manager is open-sourced software licensed under the MIT license.