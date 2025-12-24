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
 * Generator for UUID version 6 (reordered time-based).
 *
 * UUIDv6 is a field-compatible version of UUIDv1, with the timestamp reordered to make
 * the UUID lexicographically sortable. This makes it ideal for database indexing and
 * time-based sorting while maintaining compatibility with UUIDv1's timestamp and node
 * components. Unlike UUIDv1, the timestamp bits are arranged for monotonicity.
 *
 * Format: 8-4-4-4-12 hexadecimal digits (36 characters with hyphens)
 * Example: 1ef7b3c0-e29b-61d4-a716-446655440000
 *
 * UUIDv6 combines the benefits of time-based UUIDs (sortability, timestamp info)
 * with better database performance than UUIDv1 or UUIDv4.
 *
 * @author Brian Faust <brian@cline.sh>
 *
 * @see https://datatracker.ietf.org/doc/html/draft-peabody-dispatch-new-uuid-format UUIDv6 Specification Draft
 *
 * @example
 * ```php
 * $generator = new UuidV6Generator();
 * $uuid = $generator->generate();
 * // Example: "1ef7b3c0-e29b-61d4-a716-446655440000"
 *
 * // Sequential generation produces sortable UUIDs
 * $uuid1 = $generator->generate();
 * sleep(1);
 * $uuid2 = $generator->generate();
 * // $uuid1 < $uuid2 (lexicographically)
 * ```
 *
 * @psalm-immutable
 */
final readonly class UuidV6Generator implements IdGeneratorInterface
{
    /**
     * Generate a new UUIDv6.
     *
     * Creates a reordered time-based UUID using Ramsey\Uuid library. The UUID
     * includes timestamp and node information in a sortable format. Returns the
     * UUID in lowercase format.
     *
     * @return string A 36-character lowercase UUIDv6 string with hyphens
     */
    public function generate(): string
    {
        return mb_strtolower(Uuid::uuid6()->toString());
    }
}
