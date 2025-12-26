<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\StronglyTypedId\Facades;

use Cline\StronglyTypedId\Contracts\IdGeneratorInterface;
use Illuminate\Support\Facades\Facade;

/**
 * Facade for accessing the ID generator service.
 *
 * Provides a convenient static interface to the configured ID generator implementation.
 * The actual generator can be bound in the service container and may be any implementation
 * of IdGeneratorInterface (UuidV4Generator, UlidGenerator, etc.).
 *
 * @method static string generate() Generate a new unique identifier
 *
 * @author Brian Faust <brian@cline.sh>
 * @see IdGeneratorInterface
 *
 * @example
 * ```php
 * use Cline\StronglyTypedId\Facades\IdGenerator;
 *
 * // Generate a new ID using the configured generator
 * $id = IdGenerator::generate();
 * // Example output: "550e8400-e29b-41d4-a716-446655440000" (if using UuidV4Generator)
 *
 * // The facade delegates to whichever generator is bound in the container
 * app()->bind(IdGeneratorInterface::class, UuidV4Generator::class);
 * $uuid = IdGenerator::generate(); // Generates UUID v4
 *
 * app()->bind(IdGeneratorInterface::class, UlidGenerator::class);
 * $ulid = IdGenerator::generate(); // Generates ULID
 * ```
 */
final class IdGenerator extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * Returns the service container binding key for the ID generator interface.
     *
     * @return string The facade accessor key
     */
    protected static function getFacadeAccessor(): string
    {
        return IdGeneratorInterface::class;
    }
}
