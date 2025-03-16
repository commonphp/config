<?php

namespace Neuron\Configuration;

/**
 * Enum defining actions for handling duplicate parsers.
 */
enum DuplicateParserAction
{
    /**
     * Throw an error if a duplicate parser is detected.
     */
    case Error;

    /**
     * Replace the existing parser with the new one.
     */
    case Replace;

    /**
     * Ignore the new parser and keep the existing one.
     */
    case Ignore;
}