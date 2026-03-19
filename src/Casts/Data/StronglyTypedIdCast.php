<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\StronglyTypedId\Casts\Data;

use Cline\StronglyTypedId\ValueObjects\StronglyTypedId;
use Cline\Struct\Contracts\CastInterface;
use Cline\Struct\Metadata\PropertyMetadata;

use function count;
use function is_string;
use function is_subclass_of;

/**
 * Cast for strongly typed IDs in Struct data objects.
 *
 * Automatically converts string values to StronglyTypedId value objects when casting
 * properties in Struct data classes. This cast intelligently detects the target ID type
 * and creates the appropriate instance if the property type is a subclass of StronglyTypedId.
 *
 * @author Brian Faust <brian@cline.sh>
 *
 * @example
 * ```php
 * use Cline\Struct\AbstractData;
 * use Cline\StronglyTypedId\Casts\Data\StronglyTypedIdCast;
 *
 * final readonly class UserData extends AbstractData
 * {
 *     public function __construct(
 *         #[CastWith(StronglyTypedIdCast::class)]
 *         public UserId $id,
 *     ) {}
 * }
 *
 * $userData = UserData::create(['id' => '550e8400-e29b-41d4-a716-446655440000']);
 * // $userData->id is now a UserId instance
 * ```
 */
final class StronglyTypedIdCast implements CastInterface
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
     */
    public function get(PropertyMetadata $property, mixed $value): mixed
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

        if (count($property->types) !== 1) {
            return $value;
        }

        $className = $property->types[0];

        if (!is_subclass_of($className, StronglyTypedId::class)) {
            return $value;
        }

        return new $className($value);
    }

    public function set(PropertyMetadata $property, mixed $value): mixed
    {
        return $value;
    }
}
