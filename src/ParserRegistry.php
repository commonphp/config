<?php /** @noinspection DuplicatedCode */

namespace Neuron\Configuration;

use Neuron\Configuration\Exceptions\DuplicateParserException;
use Neuron\Configuration\Exceptions\InvalidParserException;
use Neuron\Configuration\Exceptions\UnsupportedSourceException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Handles parser registration and resolution based on file prefixes or suffixes.
 */
final class ParserRegistry
{
    private ContainerInterface $container;

    /** @var array<string, class-string<ParserInterface>|ParserInterface>  */
    private array $parsers = [
        ParserIdentifierType::Prefix->name => [],
        ParserIdentifierType::Suffix->name => [],
    ];

    /**
     * Initializes the parser registry with a dependency injection container.
     *
     * @param ContainerInterface $container The DI container.
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Resolves a parser based on a prefix.
     *
     * @param string $prefix The parser prefix.
     * @param string &$source The source string (modified by reference).
     * @return ParserInterface|false The resolved parser or false if not found.
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function resolvePrefix(string $prefix, string &$source): ParserInterface|false
    {
        $source = substr($source, strpos($source, ':') + 1);
        if (isset($this->parsers[ParserIdentifierType::Prefix->name][$prefix])) {
            $parser = $this->parsers[ParserIdentifierType::Prefix->name][$prefix];
            if (is_string($parser)) {
                $parser = $this->container->get($parser);
                $this->parsers[ParserIdentifierType::Prefix->name][$prefix] = $parser;
            }
            return $parser;
        }
        return false;
    }

    /**
     * Resolves a parser based on a suffix.
     *
     * @param string $suffix The file extension.
     * @return ParserInterface|false The resolved parser or false if not found.
     * @throws ContainerExceptionInterface If an exception is thrown by the container
     * @throws NotFoundExceptionInterface If there is no parser found
     */
    private function resolveSuffix(string $suffix): ParserInterface|false
    {
        if (isset($this->parsers[ParserIdentifierType::Suffix->name][$suffix])) {
            $parser = $this->parsers[ParserIdentifierType::Suffix->name][$suffix];
            if (is_string($parser)) {
                $parser = $this->container->get($parser);
                $this->parsers[ParserIdentifierType::Suffix->name][$suffix] = $parser;
            }
            return $parser;
        }
        return false;
    }

    /**
     * Resolves a parser for the given source.
     *
     * @param string &$source The source string (modified by reference).
     * @return ParserInterface The resolved parser.
     * @throws ContainerExceptionInterface If an exception is thrown by the container
     * @throws NotFoundExceptionInterface If there is no parser found
     * @throws UnsupportedSourceException If no suitable parser is found.
     */
    public function resolve(string &$source): ParserInterface
    {
        $lowerSource = strtolower($source);
        $parser = false;
        if (str_contains($lowerSource, ':')) {
            $prefix = substr($lowerSource, 0, strpos($lowerSource, ':'));
            $parser = $this->resolvePrefix($prefix, $source);
        }
        if ($parser === false) $parser = $this->resolveSuffix(pathinfo($lowerSource, PATHINFO_EXTENSION));
        if ($parser === false)
        {
            throw new UnsupportedSourceException($source);
        }
        return $parser;
    }

    /**
     * Registers a new parser for a given identifier.
     *
     * @param class-string<ParserInterface> $parserClass The parser class name.
     * @param string $identifier The identifier (prefix or suffix).
     * @param ParserIdentifierType $identifierType Type of identifier.
     * @param DuplicateParserAction $duplicateParserAction Action if a duplicate is found.
     * @return bool True if registered, false if ignored.
     * @throws InvalidParserException If the parser class is invalid.
     * @throws DuplicateParserException If a duplicate parser is detected and not allowed.
     */
    public function register(string $parserClass, string $identifier, ParserIdentifierType $identifierType = ParserIdentifierType::Suffix, DuplicateParserAction $duplicateParserAction = DuplicateParserAction::Replace): bool
    {
        if (!class_exists($parserClass) || !in_array(ParserInterface::class, class_implements($parserClass))) {
            throw new InvalidParserException($parserClass);
        }
        $identifier = strtolower($identifier);
        $exists = array_key_exists($identifier, $this->parsers[$identifierType->name]);
        if ($exists && $duplicateParserAction === DuplicateParserAction::Error) {
            throw new DuplicateParserException($parserClass, $identifierType);
        }
        if ($exists && $duplicateParserAction === DuplicateParserAction::Ignore) return false;
        $this->parsers[$identifierType->name][$identifier] = $parserClass;
        return true;
    }
}