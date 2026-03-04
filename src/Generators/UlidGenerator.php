<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\StronglyTypedId\Generators;

use Cline\StronglyTypedId\Contracts\IdGeneratorInterface;
use Illuminate\Support\Str;

use function mb_strtolower;

/**
 * Generator for ULIDs (Universally Unique Lexicographically Sortable Identifiers).
 *
 * ULIDs are 128-bit identifiers that are lexicographically sortable and encode
 * timestamp information. They are case-insensitive, URL-safe, and monotonically
 * increasing within the same millisecond.
 *
 * Format: 26 characters (10 timestamp + 16 randomness)
 * Example: 01h8e8g9zqk3xj4y9m6n7p8r9t
 *
 * @author Brian Faust <brian@cline.sh>
 *
 * @see https://github.com/ulid/spec ULID Specification
 *
 * @example
 * ```php
 * $generator = new UlidGenerator();
 * $ulid = $generator->generate();
 * // Example: "01h8e8g9zqk3xj4y9m6n7p8r9t"
 * ```
 *
 * @psalm-immutable
 */
final readonly class UlidGenerator implements IdGeneratorInterface
{
    /**
     * Generate a new ULID.
     *
     * Creates a ULID using Laravel's Str::ulid() helper, which leverages
     * Symfony's ULID component. Returns the ULID in lowercase format.
     *
     * @return string A 26-character lowercase ULID string
     */
    public function generate(): string
    {
        return mb_strtolower(Str::ulid()->toString());
    }
}
