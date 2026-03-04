<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\StronglyTypedId\Generators;

use Cline\StronglyTypedId\Contracts\IdGeneratorInterface;
use Ramsey\Uuid\Uuid;

use function mb_strtolower;

/**
 * Generator for UUID version 4 (random).
 *
 * UUIDv4 generates identifiers using random or pseudo-random numbers. This is the
 * most commonly used UUID version and is recommended for general-purpose unique
 * identifier generation. The UUID has 122 bits of randomness, providing excellent
 * collision resistance.
 *
 * Format: 8-4-4-4-12 hexadecimal digits (36 characters with hyphens)
 * Example: 550e8400-e29b-41d4-a716-446655440000
 *
 * UUIDv4 is ideal for most applications due to its simplicity, security
 * (no information leakage), and strong uniqueness guarantees.
 *
 * @author Brian Faust <brian@cline.sh>
 *
 * @see https://datatracker.ietf.org/doc/html/rfc4122#section-4.4 RFC 4122 - UUIDv4 Specification
 *
 * @example
 * ```php
 * $generator = new UuidV4Generator();
 * $uuid = $generator->generate();
 * // Example: "550e8400-e29b-41d4-a716-446655440000"
 *
 * // Each call generates a unique random UUID
 * $uuid1 = $generator->generate();
 * $uuid2 = $generator->generate();
 * // $uuid1 !== $uuid2 (almost certainly different)
 * ```
 *
 * @psalm-immutable
 */
final readonly class UuidV4Generator implements IdGeneratorInterface
{
    /**
     * Generate a new UUIDv4.
     *
     * Creates a random UUID using Ramsey\Uuid library with cryptographically
     * strong random number generation. Returns the UUID in lowercase format.
     *
     * @return string A 36-character lowercase UUIDv4 string with hyphens
     */
    public function generate(): string
    {
        return mb_strtolower(Uuid::uuid4()->toString());
    }
}
