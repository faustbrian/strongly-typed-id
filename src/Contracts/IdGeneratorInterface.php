<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\StronglyTypedId\Contracts;

/**
 * Contract for ID generator implementations.
 *
 * Defines the interface for classes that generate unique identifiers.
 * Implementations should generate IDs in specific formats such as UUIDs (v1-v6),
 * ULIDs, or other identifier schemes.
 *
 * @author Brian Faust <brian@cline.sh>
 *
 * @example
 * ```php
 * class CustomIdGenerator implements IdGeneratorInterface
 * {
 *     public function generate(): string
 *     {
 *         return uniqid('custom_', true);
 *     }
 * }
 *
 * $generator = new CustomIdGenerator();
 * $id = $generator->generate(); // "custom_507f1f77bcf86cd799439011"
 * ```
 */
interface IdGeneratorInterface
{
    /**
     * Generate a unique identifier.
     *
     * Creates and returns a new unique identifier string. The format and algorithm
     * depend on the specific implementation (UUID, ULID, etc.).
     *
     * @return string The generated identifier in lowercase string format
     */
    public function generate(): string;
}
