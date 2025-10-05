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
 * Exception thrown when a generator configuration value is invalid.
 *
 * Provides specialized factory methods for creating generator validation errors with
 * detailed error messages that include the invalid value and expected format.
 *
 * @author Brian Faust <brian@cline.sh>
 *
 * @example
 * ```php
 * use Cline\StronglyTypedId\Exceptions\InvalidGeneratorException;
 *
 * // Empty or non-string configuration
 * throw InvalidGeneratorException::invalidConfigValue('');
 * // "Generator configuration must be a non-empty string, string given"
 *
 * // Unsupported generator type
 * throw InvalidGeneratorException::unsupportedType('uuid-v9');
 * // "Unsupported generator type: uuid-v9"
 * ```
 */
final class InvalidGeneratorException extends InvalidArgumentException
{
    /**
     * Create an exception for invalid generator configuration value.
     *
     * Used when the generator configuration is not a string or is an empty string.
     *
     * @param  mixed $value The actual configuration value that was provided
     * @return self  The configured exception instance
     */
    public static function invalidConfigValue(mixed $value): self
    {
        return new self(
            sprintf(
                'Invalid generator configuration: expected a non-empty string, got %s. '.
                'Set a valid generator type in config/strongly-typed-id.php or via StronglyTypedId::$generator.',
                get_debug_type($value),
            ),
        );
    }

    /**
     * Create an exception for unsupported generator type.
     *
     * Used when the generator string value is not one of the supported GeneratorType enum values.
     *
     * @param  string $generatorType The invalid generator type that was provided
     * @return self   The configured exception instance
     */
    public static function unsupportedType(string $generatorType): self
    {
        return new self(
            sprintf(
                'Unsupported generator type "%s". Valid options: uuid_v1, uuid_v3, uuid_v4, uuid_v5, '.
                'uuid_v6, uuid_v7, uuid_v8, ulid. Check the GeneratorType enum for available types.',
                $generatorType,
            ),
        );
    }
}
