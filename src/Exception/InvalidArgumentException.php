<?php

declare(strict_types=1);

namespace PHPdot\Cache\Exception;

/**
 * PSR-16 required exception for invalid cache arguments.
 *
 * @author Omar Hamdan <omar@phpdot.com>
 * @license MIT
 */
final class InvalidArgumentException extends \InvalidArgumentException implements \Psr\SimpleCache\InvalidArgumentException {}
