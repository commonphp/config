<?php

namespace Neuron\Configuration;

/**
 * Enum defining merge behaviors for configuration data.
 */
enum MergeMode
{
    /**
     * Throw an error if the configuration key already exists.
     */
    case Error;

    /**
     * Merge new configuration data with existing data.
     */
    case Merge;

    /**
     * Replace existing configuration data with new data.
     */
    case Replace;

    /**
     * Ignore the new configuration data if the key already exists.
     */
    case Ignore;
}