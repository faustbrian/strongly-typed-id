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
 * Generator for UUID version 5 (name-based using SHA-1 hashing).
 *
 * UUIDv5 generates identifiers by hashing a namespace identifier and a name using SHA-1.
 * Like UUIDv3, it is deterministic - the same namespace and name will always produce
 * the same UUID. This makes it ideal for creating consistent, reproducible IDs based on
 * specific inputs. UUIDv5 is preferred over UUIDv3 due to SHA-1's stronger cryptographic
 * properties compared to MD5.
 *
 * Format: 8-4-4-4-12 hexadecimal digits (36 characters with hyphens)
 * Example: 886313e1-3b8a-5372-9b90-0c9aee199e5d
 *
 * @author Brian Faust <brian@cline.sh>
 *
 * @see https://datatracker.ietf.org/doc/html/rfc4122#section-4.3 RFC 4122 - UUIDv5 Specification
 *
 * @example
 * ```php
 * // Using default namespace (OID) with auto-generated name
 * $generator = new UuidV5Generator();
 * $uuid = $generator->generate();
 * // Each call generates different UUID due to unique name
 *
 * // Using custom namespace and fixed name for deterministic UUIDs
 * $generator = new UuidV5Generator(Uuid::NAMESPACE_DNS, 'example.com');
 * $uuid1 = $generator->generate();
 * $uuid2 = $generator->generate();
 * // $uuid1 === $uuid2 (same namespace + name = same UUID)
 *
 * // Different namespaces produce different UUIDs for the same name
 * $gen1 = new UuidV5Generator(Uuid::NAMESPACE_DNS, 'example.com');
 * $gen2 = new UuidV5Generator(Uuid::NAMESPACE_URL, 'example.com');
 * // $gen1->generate() !== $gen2->generate()
 * ```
 *
 * @psalm-immutable
 */
final readonly class UuidV5Generator implements IdGeneratorInterface
{
    /**
     * Create a new UUIDv5 generator instance.
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
     * Generate a new UUIDv5.
     *
     * Creates a name-based UUID using SHA-1 hashing. If no name was provided in the
     * constructor, a unique name is generated for each call. Returns the UUID in
     * lowercase format.
     *
     * @return string A 36-character lowercase UUIDv5 string with hyphens
     */
    public function generate(): string
    {
        $name = $this->name ?? uniqid('', true);

        return mb_strtolower(Uuid::uuid5($this->namespace, $name)->toString());
    }
}
