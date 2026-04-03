<?php

declare(strict_types=1);

namespace PHPdot\Cache;

/**
 * Value serializer with automatic igbinary detection.
 *
 * @author Omar Hamdan <omar@phpdot.com>
 * @license MIT
 */
final class Serializer
{
    private readonly bool $useIgbinary;

    public function __construct()
    {
        $this->useIgbinary = \extension_loaded('igbinary');
    }

    /**
     * Serialize a value to a string.
     */
    public function serialize(mixed $value): string
    {
        if ($this->useIgbinary) {
            /** @var string */
            return \igbinary_serialize($value);
        }

        return \serialize($value);
    }

    /**
     * Unserialize a string back to its original value.
     */
    public function unserialize(string $data): mixed
    {
        if ($this->useIgbinary) {
            return \igbinary_unserialize($data);
        }

        return \unserialize($data);
    }
}
