<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\StronglyTypedId\Exceptions;

use InvalidArgumentException;

use function get_debug_type;
use function sprintf;

/**
 * Exception thrown when a value has an invalid type during strongly-typed ID operations.
 *
 * Provides specialized factory methods for creating type validation errors in different
 * contexts (getters, setters, serialization) with detailed error messages that include
 * the attribute name, expected types, and actual type received.
 *
 * @author Brian Faust <brian@cline.sh>
 *
 * @example
 * ```php
 * use Cline\StronglyTypedId\Exceptions\InvalidTypeException;
 *
 * // Getter validation error
 * throw InvalidTypeException::createForGetter('user_id', 123);
 * // "Value for user_id must be string, int given"
 *
 * // Setter validation error
 * throw InvalidTypeException::createForSetter('user_id', [], UserId::class);
 * // "Value for user_id must be null, string, or instance of UserId, array given"
 *
 * // Serialization error
 * throw InvalidTypeException::createForSerializer('user_id', new stdClass());
 * // "Cannot serialize user_id value of type stdClass"
 * ```
 */
final class InvalidTypeException extends InvalidArgumentException
{
    /**
     * Create an exception for invalid getter value type.
     *
     * Used when retrieving a strongly-typed ID from the database or data object
     * and the value is not a string as expected.
     *
     * @param  string $key   The attribute name that has an invalid type
     * @param  mixed  $value The actual value that was provided
     * @return self   The configured exception instance
     */
    public static function createForGetter(string $key, mixed $value): self
    {
        return new self(
            sprintf(
                'Invalid type for attribute "%s": expected string from database, got %s. '.
                'Ensure the database column stores string values.',
                $key,
                get_debug_type($value),
            ),
        );
    }

    /**
     * Create an exception for invalid setter value type.
     *
     * Used when setting a strongly-typed ID attribute and the value is not one of
     * the accepted types (null, string, or the expected StronglyTypedId instance).
     *
     * @param  string $key           The attribute name that has an invalid type
     * @param  mixed  $value         The actual value that was provided
     * @param  string $expectedClass The fully qualified class name of the expected StronglyTypedId type
     * @return self   The configured exception instance
     */
    public static function createForSetter(string $key, mixed $value, string $expectedClass): self
    {
        return new self(
            sprintf(
                'Invalid value for attribute "%s": expected null, string, or %s instance, got %s. '.
                'Pass a valid ID string, the typed ID object, or null to clear the value.',
                $key,
                $expectedClass,
                get_debug_type($value),
            ),
        );
    }

    /**
     * Create an exception for invalid serialization value type.
     *
     * Used when serializing a model or data object to array/JSON and the strongly-typed
     * ID attribute has an unexpected type that cannot be converted to a string.
     *
     * @param  string $key   The attribute name that cannot be serialized
     * @param  mixed  $value The actual value that was provided
     * @return self   The configured exception instance
     */
    public static function createForSerializer(string $key, mixed $value): self
    {
        return new self(
            sprintf(
                'Cannot serialize attribute "%s": unexpected type %s. '.
                'Expected StronglyTypedId instance or string value. Check the attribute cast configuration.',
                $key,
                get_debug_type($value),
            ),
        );
    }
}
