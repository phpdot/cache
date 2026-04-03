<?php

declare(strict_types=1);

namespace PHPdot\Cache;

/**
 * Raw backend contract for cache drivers.
 *
 * @author Omar Hamdan <omar@phpdot.com>
 * @license MIT
 */
interface DriverInterface
{
    /**
     * Retrieve a value by key.
     *
     * @return mixed The cached value or null if not found.
     */
    public function get(string $key): mixed;

    /**
     * Store a value by key with optional TTL in seconds.
     *
     * @param int $ttl Time-to-live in seconds (0 = no expiry).
     */
    public function set(string $key, mixed $value, int $ttl = 0): bool;

    /**
     * Remove a value by key.
     */
    public function delete(string $key): bool;

    /**
     * Wipe all cached values.
     */
    public function clear(): bool;

    /**
     * Check whether a key exists and is not expired.
     */
    public function has(string $key): bool;

    /**
     * Retrieve multiple values by their keys.
     *
     * @param list<string> $keys
     *
     * @return array<string, mixed> Key => value (missing keys not included).
     */
    public function getMultiple(array $keys): array;

    /**
     * Store multiple key => value pairs with optional TTL.
     *
     * @param array<string, mixed> $values Key => value.
     * @param int $ttl Time-to-live in seconds (0 = no expiry).
     */
    public function setMultiple(array $values, int $ttl = 0): bool;

    /**
     * Remove multiple values by their keys.
     *
     * @param list<string> $keys
     */
    public function deleteMultiple(array $keys): bool;
}
