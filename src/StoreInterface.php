<?php

declare(strict_types=1);

namespace PHPdot\Cache;

use Psr\SimpleCache\CacheInterface;

/**
 * Extended PSR-16 cache interface with remember pattern.
 *
 * @author Omar Hamdan <omar@phpdot.com>
 * @license MIT
 */
interface StoreInterface extends CacheInterface
{
    /**
     * Get an item from cache, or store the result of a callback.
     *
     * @template T
     *
     * @param callable(): T $callback
     *
     * @return T
     */
    public function remember(string $key, int $ttl, callable $callback): mixed;

    /**
     * Get an item from cache, or store the result of a callback forever.
     *
     * @template T
     *
     * @param callable(): T $callback
     *
     * @return T
     */
    public function rememberForever(string $key, callable $callback): mixed;
}
