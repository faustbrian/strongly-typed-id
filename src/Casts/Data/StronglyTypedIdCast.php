<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\StronglyTypedId\Casts\Data;

use Cline\StronglyTypedId\ValueObjects\StronglyTypedId;
use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Support\Types\NamedType;

use function is_string;
use function is_subclass_of;

/**
 * Cast for strongly typed IDs in Spatie Laravel Data objects.
 *
 * Automatically converts string values to StronglyTypedId value objects when casting
 * properties in Laravel Data classes. This cast intelligently detects the target ID type
 * and creates the appropriate instance if the property type is a subclass of StronglyTypedId.
 *
 * @author Brian Faust <brian@cline.sh>
 *
 * @example
 * ```php
 * use Spatie\LaravelData\Data;
 * use Cline\StronglyTypedId\Casts\Data\StronglyTypedIdCast;
 *
 * class UserData extends Data
 * {
 *     public function __construct(
 *         #[WithCast(StronglyTypedIdCast::class)]
 *         public UserId $id,
 *     ) {}
 * }
 *
 * $userData = UserData::from(['id' => '550e8400-e29b-41d4-a716-446655440000']);
 * // $userData->id is now a UserId instance
 * ```
 */
final class StronglyTypedIdCast implements Cast
{
    /**
     * Cast the given value to a strongly typed ID.
     *
     * This method performs intelligent type detection and conversion:
     * - Returns null if the value is null
     * - Returns the value unchanged if it's already a StronglyTypedId instance
     * - Returns the value unchanged if it's not a string
     * - Returns the value unchanged if the property type is not a class
     * - Converts string values to the appropriate StronglyTypedId subclass
     *
     * @param  DataProperty         $property   The property being cast
     * @param  mixed                $value      The value to cast
     * @param  array<string, mixed> $properties All properties of the data object
     * @param  CreationContext      $context    The creation context
     * @return mixed                The cast value (StronglyTypedId instance or original value)
     */
    public function cast(DataProperty $property, mixed $value, array $properties, CreationContext $context): mixed
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof StronglyTypedId) {
            return $value;
        }

        if (!is_string($value)) {
            return $value;
        }

        $type = $property->type->type;

        if (!$type instanceof NamedType) {
            return $value;
        }

        $className = $type->name;

        if (!is_subclass_of($className, StronglyTypedId::class)) {
            return $value;
        }

        return new $className($value);
    }
}
