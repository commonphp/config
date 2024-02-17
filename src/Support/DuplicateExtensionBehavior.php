<?php

/**
 * Defines behaviors for handling duplicate file extension registrations.
 *
 * @package CommonPHP\Configuration\Support
 */

namespace CommonPHP\Configuration\Support;

enum DuplicateExtensionBehavior
{
    case SKIP;
    case OVERWRITE;
    case THROW;
    case WARN;
    case NOTICE;
}