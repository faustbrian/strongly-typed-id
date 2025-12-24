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
 * UUID Version 7 Generator.
 *
 * Generates time-ordered, globally unique identifiers using UUID version 7 specification.
 * UUIDv7 combines a Unix timestamp with random data to create sortable, unique identifiers.
 * This makes them ideal for database primary keys as they maintain insertion order.
 *
 * Features:
 * - Time-ordered (sortable by creation time)
 * - Globally unique
 * - Database-friendly (improves B-tree index performance)
 * - RFC 4122 compliant
 *
 * @psalm-immutable
 *
 * @author Brian Faust <brian@cline.sh>
 * @see https://datatracker.ietf.org/doc/html/draft-peabody-dispatch-new-uuid-format UUID v7 Specification
 */
final readonly class UuidV7Generator implements IdGeneratorInterface
{
    /**
     * Generate a new UUID version 7 identifier.
     *
     * Creates a time-ordered UUID that includes a Unix timestamp in the most
     * significant bits, ensuring lexicographic sorting matches chronological order.
     *
     * @return string A lowercase UUID v7 string (e.g., "018a2e65-0c85-7000-8000-000000000000")
     */
    public function generate(): string
    {
        return mb_strtolower(Uuid::uuid7()->toString());
    }
}
