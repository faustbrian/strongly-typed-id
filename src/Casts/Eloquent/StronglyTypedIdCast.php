<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\StronglyTypedId\Casts\Eloquent;

use Cline\StronglyTypedId\Exceptions\InvalidTypeException;
use Cline\StronglyTypedId\ValueObjects\StronglyTypedId;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Contracts\Database\Eloquent\SerializesCastableAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

use function is_string;
use function is_subclass_of;
use function sprintf;
use function throw_unless;

/**
 * Cast for strongly typed entity IDs in Eloquent models.
 *
 * Automatically converts string IDs (UUIDs, ULIDs, etc.) to their respective strongly typed ID
 * value objects when reading from the database, and converts them back to strings when writing.
 * This cast ensures type safety and encapsulation of ID logic at the model level.
 *
 * @author Brian Faust <brian@cline.sh>
 *
 * @template TGet of StronglyTypedId|null
 * @template TSet of StronglyTypedId|string|null
 *
 * @implements CastsAttributes<TGet, TSet>
 *
 * @example
 * ```php
 * use Illuminate\Database\Eloquent\Model;
 * use Cline\StronglyTypedId\Casts\Eloquent\StronglyTypedIdCast;
 *
 * class User extends Model
 * {
 *     protected function casts(): array
 *     {
 *         return [
 *             'id' => StronglyTypedIdCast::class.':'.UserId::class,
 *         ];
 *     }
 * }
 *
 * $user = User::find('550e8400-e29b-41d4-a716-446655440000');
 * // $user->id is now a UserId instance, not a string
 * ```
 *
 * @psalm-immutable
 */
final readonly class StronglyTypedIdCast implements CastsAttributes, SerializesCastableAttributes
{
    /**
     * Create a new cast instance.
     *
     * @param class-string<StronglyTypedId> $idClass The fully qualified class name of the strongly typed ID
     *
     * @throws InvalidArgumentException If the provided class does not extend StronglyTypedId
     */
    public function __construct(
        private string $idClass,
    ) {
        throw_unless(
            is_subclass_of($this->idClass, StronglyTypedId::class),
            InvalidArgumentException::class,
            sprintf('Class %s must extend %s', $this->idClass, StronglyTypedId::class),
        );
    }

    /**
     * Cast the given value to the strongly typed ID.
     *
     * Transforms the database value (string) into a StronglyTypedId instance when
     * retrieving the attribute from the model.
     *
     * @param  Model                $model      The model instance
     * @param  string               $key        The attribute name
     * @param  mixed                $value      The raw value from the database
     * @param  array<string, mixed> $attributes All model attributes
     * @return null|StronglyTypedId The strongly typed ID instance or null
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ?StronglyTypedId
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof $this->idClass) {
            return $value;
        }

        throw_unless(is_string($value), InvalidTypeException::createForGetter($key, $value));

        return $this->idClass::fromString($value);
    }

    /**
     * Prepare the given value for storage in the database.
     *
     * Converts a StronglyTypedId instance or validates a string value before
     * storing it in the database. Ensures only valid ID values are persisted.
     *
     * @param Model                $model      The model instance
     * @param string               $key        The attribute name
     * @param mixed                $value      The value to be stored
     * @param array<string, mixed> $attributes All model attributes
     *
     * @throws InvalidArgumentException If the value is not null, string, or a StronglyTypedId instance
     *
     * @return null|string The string representation for database storage
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof StronglyTypedId) {
            return $value->toString();
        }

        if (is_string($value)) {
            // Validate by creating the ID instance first
            $this->idClass::fromString($value);

            return $value;
        }

        throw InvalidTypeException::createForSetter($key, $value, $this->idClass);
    }

    /**
     * Serialize the attribute value for array/JSON conversion.
     *
     * Converts the strongly typed ID to a string when the model is serialized
     * to an array or JSON (e.g., in API responses).
     *
     * @param Model                $model      The model instance
     * @param string               $key        The attribute name
     * @param mixed                $value      The value to serialize
     * @param array<string, mixed> $attributes All model attributes
     *
     * @throws InvalidArgumentException If the value cannot be serialized
     *
     * @return null|string The serialized string value or null
     */
    public function serialize(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof StronglyTypedId) {
            return $value->toString();
        }

        if (is_string($value)) {
            return $value;
        }

        throw InvalidTypeException::createForSerializer($key, $value);
    }
}
