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

use function mb_strtoupper;

/**
 * Generator for GUID (Globally Unique Identifier).
 *
 * GUIDs are Microsoft's implementation of UUIDs, typically using UUID version 4
 * (random) format but displayed in uppercase with optional braces. While functionally
 * identical to UUIDs, GUIDs follow Microsoft's naming and formatting conventions.
 *
 * Format: 8-4-4-4-12 hexadecimal digits (36 characters with hyphens)
 * Example: 550E8400-E29B-41D4-A716-446655440000
 * With braces: {550E8400-E29B-41D4-A716-446655440000}
 *
 * GUIDs are ideal for Windows-based systems, .NET applications, and environments
 * requiring Microsoft-compatible identifiers.
 *
 * @author Brian Faust <brian@cline.sh>
 *
 * @see https://learn.microsoft.com/en-us/dotnet/api/system.guid Microsoft GUID Specification
 * @see https://datatracker.ietf.org/doc/html/rfc4122 RFC 4122 - UUID Specification
 *
 * @example
 * ```php
 * $generator = new GuidGenerator();
 * $guid = $generator->generate();
 * // Example: "550E8400-E29B-41D4-A716-446655440000"
 *
 * // With braces formatting (if needed manually)
 * $guidWithBraces = '{' . $generator->generate() . '}';
 * // Example: "{550E8400-E29B-41D4-A716-446655440000}"
 *
 * // Each call generates a unique random GUID
 * $guid1 = $generator->generate();
 * $guid2 = $generator->generate();
 * // $guid1 !== $guid2 (almost certainly different)
 * ```
 *
 * @psalm-immutable
 */
final readonly class GuidGenerator implements IdGeneratorInterface
{
    /**
     * Generate a new GUID.
     *
     * Creates a random UUID using Ramsey\Uuid library with cryptographically
     * strong random number generation. Returns the GUID in uppercase format
     * following Microsoft conventions.
     *
     * @return string A 36-character uppercase GUID string with hyphens
     */
    public function generate(): string
    {
        return mb_strtoupper(Uuid::uuid4()->toString());
    }
}
