<?php

/**
 * Defines behaviors for handling unsavable configurations.
 *
 * @package CommonPHP\Configuration\Support
 */

namespace CommonPHP\Configuration\Support;

enum ConfigurationCannotSaveBehavior
{
    case IGNORE;
    case THROW;
    case WARN;
    case NOTICE;
}