<?php

namespace Neuron\Configuration\Parsers;

use Neuron\Configuration\AbstractParser;
use Neuron\Configuration\Exceptions\FormatException;
use Psr\Log\LoggerInterface;

/**
 * PHP parser implementation for configuration files.
 */
final class PhpParser extends AbstractParser
{
    private LoggerInterface $logger;
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     * @throws FormatException
     */
    public function read(string $source): array
    {
        $this->validateRead($source);
        $result = (require $source);
        if (!is_array($result)) {
            $this->logger->critical("PHP configuration file did not return an array as expected", ['source' => $source, 'content' => file_get_contents($source)]);
            throw new FormatException('php');
        }
        return $result;
    }

    /**
     * @inheritDoc
     */
    public function deserialize(string $data): array
    {
        return []; // This is not support
    }

    /**
     * @inheritDoc
     */
    public function serialize(array $data): string
    {
        return '<?php return ' . var_export($data, true) . ';';
    }
}