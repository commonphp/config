<?php

namespace Neuron\Configuration;

/**
 * Enum for identifying parsers by prefix or suffix.
 */
enum ParserIdentifierType
{
    /**
     * Identify the parser using a prefix (e.g., "db:").
     */
    case Prefix;

    /**
     * Identify the parser using a suffix or file extension (e.g., ".json").
     */
    case Suffix;
}