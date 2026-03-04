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
use function uniqid;

/**
 * Generator for UUID version 3 (name-based using MD5 hashing).
 *
 * UUIDv3 generates identifiers by hashing a namespace identifier and a name using MD5.
 * The same namespace and name will always produce the same UUID, making it deterministic
 * and reproducible. This is useful for generating consistent IDs based on specific inputs.
 *
 * Format: 8-4-4-4-12 hexadecimal digits (36 characters with hyphens)
 * Example: a3bb189e-8bf9-3888-9912-ace4e6543002
 *
 * Note: UUIDv3 uses MD5, which is cryptographically broken. For new applications,
 * prefer UUIDv5 (SHA-1) or UUIDv4 (random) instead.
 *
 * @author Brian Faust <brian@cline.sh>
 *
 * @see https://datatracker.ietf.org/doc/html/rfc4122#section-4.3 RFC 4122 - UUIDv3 Specification
 *
 * @example
 * ```php
 * // Using default namespace (OID) with auto-generated name
 * $generator = new UuidV3Generator();
 * $uuid = $generator->generate();
 * // Each call generates different UUID due to unique name
 *
 * // Using custom namespace and fixed name for deterministic UUIDs
 * $generator = new UuidV3Generator(Uuid::NAMESPACE_DNS, 'example.com');
 * $uuid1 = $generator->generate();
 * $uuid2 = $generator->generate();
 * // $uuid1 === $uuid2 (same namespace + name = same UUID)
 * ```
 *
 * @psalm-immutable
 */
final readonly class UuidV3Generator implements IdGeneratorInterface
{
    /**
     * Create a new UUIDv3 generator instance.
     *
     * @param string      $namespace The namespace UUID as a string. Common namespaces:
     *                               - Uuid::NAMESPACE_DNS (for domain names)
     *                               - Uuid::NAMESPACE_URL (for URLs)
     *                               - Uuid::NAMESPACE_OID (for ISO OIDs)
     *                               - Uuid::NAMESPACE_X500 (for X.500 DNs)
     * @param null|string $name      The name to hash. If null, a unique ID is generated for each call,
     *                               making the UUID non-deterministic. Provide a fixed name for
     *                               reproducible UUIDs.
     */
    public function __construct(
        private string $namespace = Uuid::NAMESPACE_OID,
        private ?string $name = null,
    ) {}

    /**
     * Generate a new UUIDv3.
     *
     * Creates a name-based UUID using MD5 hashing. If no name was provided in the
     * constructor, a unique name is generated for each call. Returns the UUID in
     * lowercase format.
     *
     * @return string A 36-character lowercase UUIDv3 string with hyphens
     */
    public function generate(): string
    {
        $name = $this->name ?? uniqid('', true);

        return mb_strtolower(Uuid::uuid3($this->namespace, $name)->toString());
    }
}
