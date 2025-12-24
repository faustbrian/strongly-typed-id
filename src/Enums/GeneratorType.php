<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\StronglyTypedId\Enums;

/**
 * Supported ID generator types.
 *
 * Defines all available generator types for strongly-typed IDs in the application.
 * Each enum case corresponds to a specific implementation of the IdGeneratorInterface
 * and can be used to configure which ID generation strategy to use at runtime.
 *
 * @author Brian Faust <brian@cline.sh>
 *
 * @example
 * ```php
 * use Cline\StronglyTypedId\Enums\GeneratorType;
 *
 * // Configure the ID generator type
 * $type = GeneratorType::UuidV4;
 * config(['strongly-typed-id.generator' => $type->value]);
 *
 * // Use in conditional logic
 * match($generatorType) {
 *     GeneratorType::UuidV4 => new UuidV4Generator(),
 *     GeneratorType::Ulid => new UlidGenerator(),
 *     default => throw InvalidGeneratorException::unsupportedType($generatorType->value),
 * };
 * ```
 */
enum GeneratorType: string
{
    /**
     * UUID version 1 (time-based with MAC address).
     *
     * Generates time-based UUIDs that include timestamp and MAC address information.
     * Provides temporal ordering but may leak hardware information.
     */
    case UuidV1 = 'uuid_v1';

    /**
     * UUID version 3 (name-based with MD5 hashing).
     *
     * Generates deterministic UUIDs based on namespace and name using MD5.
     * Same inputs always produce the same UUID. Not recommended for new applications.
     */
    case UuidV3 = 'uuid_v3';

    /**
     * UUID version 4 (random).
     *
     * Generates random UUIDs with 122 bits of randomness. Most commonly used
     * version and recommended for general-purpose unique identifier generation.
     */
    case UuidV4 = 'uuid_v4';

    /**
     * UUID version 5 (name-based with SHA-1 hashing).
     *
     * Generates deterministic UUIDs based on namespace and name using SHA-1.
     * Preferred over UUIDv3 for name-based UUID generation.
     */
    case UuidV5 = 'uuid_v5';

    /**
     * UUID version 6 (time-ordered with improved timestamp precision).
     *
     * Reordered time-based UUID that improves database indexing performance
     * by placing timestamp bits in sortable order. Compatible with UUIDv1.
     */
    case UuidV6 = 'uuid_v6';

    /**
     * UUID version 7 (Unix timestamp-based).
     *
     * Uses Unix timestamp for time ordering with improved sortability and
     * database indexing performance. Recommended for new applications requiring
     * time-ordered identifiers.
     */
    case UuidV7 = 'uuid_v7';

    /**
     * UUID version 8 (custom format).
     *
     * Allows custom UUID formats while maintaining RFC 4122 compatibility.
     * Reserved for experimental or application-specific UUID implementations.
     */
    case UuidV8 = 'uuid_v8';

    /**
     * ULID (Universally Unique Lexicographically Sortable Identifier).
     *
     * Generates 26-character case-insensitive identifiers that are lexicographically
     * sortable and encode timestamp information. URL-safe and more compact than UUIDs.
     */
    case Ulid = 'ulid';

    /**
     * Sqids (Short Unique Identifiers).
     *
     * Generates short, URL-safe identifiers by encoding numeric values into
     * compact strings. Provides configurable alphabet and minimum length while
     * maintaining uniqueness and human-friendly readability.
     */
    case Sqids = 'sqids';

    /**
     * Hashids (Hash-based Identifier).
     *
     * Generates short, unique, URL-safe identifiers using configurable salt
     * and alphabet. Encodes numeric IDs into decodable strings, making them
     * ideal for obfuscating database IDs while preventing enumeration attacks.
     */
    case Hashids = 'hashids';

    /**
     * NanoID (Nano Identifier).
     *
     * Generates cryptographically secure, URL-friendly unique identifiers.
     * By default, creates 21-character IDs with same collision probability
     * as UUID v4. Uses uniform distribution to prevent modulo bias and ensure
     * security. More compact than UUIDs while maintaining equivalent security.
     */
    case NanoId = 'nanoid';

    /**
     * GUID (Globally Unique Identifier).
     *
     * Microsoft's implementation of UUID version 4, formatted in uppercase
     * following .NET conventions. Functionally identical to UUIDv4 but uses
     * uppercase letters for compatibility with Windows and Microsoft ecosystems.
     */
    case Guid = 'guid';

    /**
     * Random String (Laravel Str::random()).
     *
     * Generates cryptographically secure alphanumeric identifiers using Laravel's
     * Str::random() helper. Produces identifiers containing only a-z, A-Z, and 0-9
     * with configurable length. Ideal for API tokens, session IDs, and general-purpose
     * unique strings where alphanumeric output is required.
     */
    case RandomString = 'random_string';

    /**
     * Random Bytes (PHP random_bytes()).
     *
     * Generates cryptographically secure hexadecimal identifiers using PHP's
     * random_bytes() function with hex encoding. Produces identifiers containing
     * only 0-9 and a-f characters with predictable length (bytes * 2). Ideal for
     * security tokens, encryption keys, and contexts requiring maximum randomness
     * with deterministic output length.
     */
    case RandomBytes = 'random_bytes';

    /**
     * Prefixed ID (Stripe-style).
     *
     * Generates Stripe-style prefixed identifiers by combining a configurable prefix
     * with an underlying ID generator. Creates IDs in the format "prefix_id" where the
     * prefix identifies the entity type and the ID is generated by the configured generator.
     * Defaults to using RandomString(24) to match Stripe's typical format. The prefix and
     * underlying generator can be customized via configuration.
     */
    case Prefixed = 'prefixed';
}
