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
use Random\RandomException;

use function mb_strtolower;
use function random_bytes;

/**
 * UUID Version 8 Generator.
 *
 * Generates custom UUIDs using the UUID version 8 specification, which allows for
 * application-specific UUID formats. This implementation uses cryptographically secure
 * random bytes to create unique identifiers.
 *
 * Features:
 * - Custom UUID format (RFC 4122 compliant structure)
 * - Cryptographically secure random generation
 * - Globally unique
 * - Application-specific design flexibility
 *
 * Note: UUIDv8 is designed for experimental or vendor-specific use cases where
 * standard UUID versions don't meet specific requirements.
 *
 * @see https://datatracker.ietf.org/doc/html/draft-peabody-dispatch-new-uuid-format UUID v8 Specification
 *
 * @psalm-immutable
 */
final readonly class UuidV8Generator implements IdGeneratorInterface
{
    /**
     * Generate a new UUID version 8 identifier.
     *
     * Creates a custom UUID using 16 cryptographically secure random bytes.
     * The resulting UUID follows the v8 format but with application-specific data.
     *
     * @throws RandomException If an appropriate source of randomness cannot be found
     *
     * @return string A lowercase UUID v8 string (e.g., "018a2e65-0c85-8000-8000-000000000000")
     */
    public function generate(): string
    {
        return mb_strtolower(Uuid::uuid8(random_bytes(16))->toString());
    }
}
