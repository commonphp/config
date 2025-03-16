<?php

namespace Neuron\Configuration\Parsers;

use Neuron\Configuration\AbstractParser;
use Neuron\Configuration\Exceptions\FormatException;

/**
 * JSON parser implementation for configuration files.
 */
final class JsonParser extends AbstractParser
{
    /**
     * @inheritDoc
     * @throws FormatException
     */
    public function deserialize(string $data): array
    {
        if (!json_validate($data)) {
            throw new FormatException('json');
        }
        return json_decode($data, true);
    }

    /**
     * @inheritDoc
     */
    public function serialize(array $data): string
    {
        return json_encode($data);
    }
}