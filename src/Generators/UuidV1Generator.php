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
 * Generator for UUID version 1 (time-based).
 *
 * UUIDv1 generates identifiers based on the current timestamp and MAC address.
 * The timestamp is a 60-bit value representing the number of 100-nanosecond
 * intervals since the UUID epoch (October 15, 1582). The MAC address provides
 * spatial uniqueness.
 *
 * Format: 8-4-4-4-12 hexadecimal digits (36 characters with hyphens)
 * Example: 550e8400-e29b-11d4-a716-446655440000
 *
 * Note: UUIDv1 can leak MAC address information and is not recommended for
 * security-sensitive applications. Consider UUIDv4 or UUIDv6 instead.
 *
 * @author Brian Faust <brian@cline.sh>
 *
 * @see https://datatracker.ietf.org/doc/html/rfc4122#section-4.2 RFC 4122 - UUIDv1 Specification
 *
 * @example
 * ```php
 * $generator = new UuidV1Generator();
 * $uuid = $generator->generate();
 * // Example: "550e8400-e29b-11d4-a716-446655440000"
 * ```
 *
 * @psalm-immutable
 */
final readonly class UuidV1Generator implements IdGeneratorInterface
{
    /**
     * Generate a new UUIDv1.
     *
     * Creates a time-based UUID using Ramsey\Uuid library. The UUID includes
     * timestamp and MAC address information. Returns the UUID in lowercase format.
     *
     * @return string A 36-character lowercase UUIDv1 string with hyphens
     */
    public function generate(): string
    {
        return mb_strtolower(Uuid::uuid1()->toString());
    }
}
