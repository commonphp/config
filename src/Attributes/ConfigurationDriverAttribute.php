<?php

/**
 * Marks a class as a configuration driver, specifying supported file extensions.
 *
 * @package CommonPHP\Configuration\Attributes
 */

namespace CommonPHP\Configuration\Attributes;

use Attribute;
use CommonPHP\Drivers\Contracts\DriverAttributeContract;

#[Attribute(Attribute::TARGET_CLASS)]
readonly class ConfigurationDriverAttribute implements DriverAttributeContract
{
    /** @var string[] Supported file extensions. */
    public array $extensions;
    public function __construct(string ... $extensions)
    {
        $this->extensions = $extensions;
    }
}