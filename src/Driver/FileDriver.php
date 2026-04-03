<?php

declare(strict_types=1);

namespace PHPdot\Cache\Driver;

use PHPdot\Cache\DriverInterface;
use PHPdot\Cache\Serializer;

/**
 * Filesystem cache driver with binary header for expiry tracking.
 *
 * @author Omar Hamdan <omar@phpdot.com>
 * @license MIT
 */
final class FileDriver implements DriverInterface
{
    /**
     * @param string $directory Base directory for cache files.
     * @param Serializer $serializer Value serializer.
     */
    public function __construct(
        private readonly string $directory,
        private readonly Serializer $serializer = new Serializer(),
    ) {}

    /**
     * {@inheritDoc}
     */
    public function get(string $key): mixed
    {
        $path = $this->path($key);

        if (!\is_file($path)) {
            return null;
        }

        $contents = \file_get_contents($path);

        if ($contents === false || \strlen($contents) < 8) {
            return null;
        }

        $header = \unpack('Jexpiry', \substr($contents, 0, 8));

        if ($header === false) {
            return null;
        }

        /** @var int $expiry */
        $expiry = $header['expiry'];

        if ($expiry > 0 && \time() >= $expiry) {
            $this->delete($key);

            return null;
        }

        return $this->serializer->unserialize(\substr($contents, 8));
    }

    /**
     * {@inheritDoc}
     */
    public function set(string $key, mixed $value, int $ttl = 0): bool
    {
        $path = $this->path($key);
        $dir = \dirname($path);

        if (!\is_dir($dir)) {
            \mkdir($dir, 0777, true);
        }

        $expiry = $ttl > 0 ? \time() + $ttl : 0;
        $data = \pack('J', $expiry) . $this->serializer->serialize($value);

        return \file_put_contents($path, $data, \LOCK_EX) !== false;
    }

    /**
     * {@inheritDoc}
     */
    public function delete(string $key): bool
    {
        $path = $this->path($key);

        if (\is_file($path)) {
            return \unlink($path);
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function clear(): bool
    {
        if (!\is_dir($this->directory)) {
            return true;
        }

        $this->removeDirectoryContents($this->directory);

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function has(string $key): bool
    {
        return $this->get($key) !== null;
    }

    /**
     * {@inheritDoc}
     */
    public function getMultiple(array $keys): array
    {
        $results = [];

        foreach ($keys as $key) {
            $value = $this->get($key);

            if ($value !== null) {
                $results[$key] = $value;
            }
        }

        return $results;
    }

    /**
     * {@inheritDoc}
     */
    public function setMultiple(array $values, int $ttl = 0): bool
    {
        $success = true;

        foreach ($values as $key => $value) {
            if (!$this->set($key, $value, $ttl)) {
                $success = false;
            }
        }

        return $success;
    }

    /**
     * {@inheritDoc}
     */
    public function deleteMultiple(array $keys): bool
    {
        $success = true;

        foreach ($keys as $key) {
            if (!$this->delete($key)) {
                $success = false;
            }
        }

        return $success;
    }

    /**
     * Build the file path for a cache key.
     */
    private function path(string $key): string
    {
        $hash = \md5($key);

        return $this->directory . '/' . \substr($hash, 0, 2) . '/' . $hash;
    }

    /**
     * Recursively remove all files and subdirectories within a directory.
     */
    private function removeDirectoryContents(string $directory): void
    {
        $items = \scandir($directory);

        if ($items === false) {
            return;
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $directory . '/' . $item;

            if (\is_dir($path)) {
                $this->removeDirectoryContents($path);
                \rmdir($path);
            } else {
                \unlink($path);
            }
        }
    }
}
