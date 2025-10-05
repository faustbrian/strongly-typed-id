<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\StronglyTypedId\ValueObjects;

use Cline\StronglyTypedId\Casts\Eloquent\StronglyTypedIdCast;
use Cline\StronglyTypedId\Facades\IdGenerator;
use Illuminate\Database\Eloquent\Casts\Attribute;
use InvalidArgumentException;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Stringable;

use function is_string;
use function mb_strtolower;
use function sprintf;
use function throw_if;
use function throw_unless;

/**
 * Abstract base class for strongly-typed, UUID-based entity identifiers.
 *
 * This class provides a foundation for creating type-safe entity identifiers in PHP.
 * By extending this class, you can create distinct ID types for different entities
 * (e.g., UserId, OrderId, ProductId), preventing accidental mixing of IDs across
 * entity boundaries at compile time.
 *
 * This class should never be instantiated directly - use specific ID types instead.
 *
 * Key Features:
 * - Type safety: Each ID type is a distinct class, preventing ID confusion
 * - Immutability: All properties are readonly, ensuring IDs cannot be modified
 * - Validation: Automatic UUID format validation on construction
 * - Eloquent integration: Built-in casts and attributes for seamless database usage
 * - Multiple formats: Support for UUIDs and ULIDs via configurable generators
 *
 * @example
 * ```php
 * // Define a specific ID type
 * final readonly class UserId extends StronglyTypedId {}
 *
 * // Create IDs
 * $userId = UserId::generate();
 * $fromString = UserId::fromString('550e8400-e29b-41d4-a716-446655440000');
 *
 * // Use in Eloquent models
 * class User extends Model {
 *     protected $casts = [
 *         'id' => UserId::asEloquentCast(),
 *     ];
 * }
 * ```
 *
 * @author Brian Faust <brian@cline.sh>
 *
 * @psalm-immutable
 */
abstract readonly class StronglyTypedId implements Stringable
{
    /**
     * Create a new strongly-typed ID instance.
     *
     * @param string $value The UUID string value (will be validated)
     *
     * @throws InvalidArgumentException If the value is empty or not a valid UUID format
     */
    public function __construct(
        public string $value,
    ) {
        self::validate($value);
    }

    /**
     * Convert the ID to its string representation.
     *
     * @return string The lowercase UUID string
     */
    public function __toString(): string
    {
        return $this->value;
    }

    /**
     * Create an ID instance from a string value.
     *
     * @param string $id The UUID string to create an ID from
     *
     * @throws InvalidArgumentException when the string is not a valid UUID
     *
     * @return static A validated ID instance
     */
    public static function fromString(string $id): static
    {
        return new static(mb_strtolower($id));
    }

    /**
     * Create an ID instance from a UUID interface.
     *
     * @param  UuidInterface $uuid The UUID object to convert
     * @return static        An ID instance containing the lowercase UUID string
     */
    public static function fromUuid(UuidInterface $uuid): static
    {
        return new static(mb_strtolower($uuid->toString()));
    }

    /**
     * Generate a new random ID using the configured generator.
     *
     * @return static A new ID instance with a generated ID
     */
    public static function generate(): static
    {
        return new static(IdGenerator::generate());
    }

    /**
     * Get the Eloquent cast class string for this ID type.
     *
     * Returns a cast specification that can be used in Eloquent model $casts
     * array to automatically convert database strings to ID instances and vice versa.
     *
     * @example
     * ```php
     * class User extends Model {
     *     protected $casts = [
     *         'id' => UserId::asEloquentCast(),
     *         'organization_id' => OrganizationId::asEloquentCast(),
     *     ];
     * }
     * ```
     *
     * @return string The cast specification in "CastClass:IDClass" format
     */
    public static function asEloquentCast(): string
    {
        return StronglyTypedIdCast::class.':'.static::class;
    }

    /**
     * Create an Eloquent attribute accessor/mutator for this ID type.
     *
     * Returns an Attribute instance that handles conversion between database
     * string values and ID instances. Supports null values and accepts both
     * string and ID instances for setting values.
     *
     * This is an alternative to using casts when you need more control over
     * the attribute behavior or want to use Laravel's attribute API.
     *
     * @example
     * ```php
     * class User extends Model {
     *     protected function id(): Attribute {
     *         return UserId::asEloquentAttribute();
     *     }
     * }
     * ```
     *
     * @return Attribute<null|static, null|self|string> Attribute with get/set logic for ID conversion
     */
    public static function asEloquentAttribute(): Attribute
    {
        return Attribute::make(
            get: static fn (mixed $value): ?static => is_string($value) && $value !== '' && $value !== '0' ? static::fromString($value) : null,
            set: static fn (self|string|null $value): ?string => match (true) {
                $value === null => null,
                $value instanceof self => $value->toString(),
                default => static::fromString($value)->toString(),
            },
        );
    }

    /**
     * Compare this ID with another for equality.
     *
     * Two IDs are equal if they have the same string value and are instances
     * of the same ID class. This prevents accidentally comparing IDs from
     * different entity types, providing type safety at runtime.
     *
     * @example
     * ```php
     * $userId1 = UserId::fromString('550e8400-e29b-41d4-a716-446655440000');
     * $userId2 = UserId::fromString('550e8400-e29b-41d4-a716-446655440000');
     * $orderId = OrderId::fromString('550e8400-e29b-41d4-a716-446655440000');
     *
     * $userId1->equals($userId2); // true (same type, same value)
     * $userId1->equals($orderId);  // false (different types, even with same UUID)
     * ```
     *
     * @param  self $other The ID to compare against
     * @return bool True if both IDs are of the same type and have identical values
     */
    public function equals(self $other): bool
    {
        return $this->value === $other->value
            && $other instanceof static;
    }

    /**
     * Get the ID as a string.
     *
     * @return string The lowercase UUID string
     */
    public function toString(): string
    {
        return $this->value;
    }

    /**
     * Validate the ID value format and content.
     *
     * Ensures the value is non-empty and conforms to valid UUID format
     * according to RFC 4122 specifications.
     *
     * @param string $value The ID value to validate
     *
     * @throws InvalidArgumentException when the value is empty or not a valid UUID
     */
    private static function validate(string $value): void
    {
        throw_if($value === '' || $value === '0', new InvalidArgumentException(
            sprintf('%s cannot be empty', static::class),
        ));

        throw_unless(Uuid::isValid($value), new InvalidArgumentException(
            sprintf('Invalid UUID format for %s: %s', static::class, $value),
        ));
    }
}
