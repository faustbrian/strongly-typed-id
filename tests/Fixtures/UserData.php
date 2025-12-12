<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Fixtures;

use Cline\StronglyTypedId\Casts\Data\StronglyTypedIdCast;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Data;

/**
 * @author Brian Faust <brian@cline.sh>
 */
final class UserData extends Data
{
    public function __construct(
        #[WithCast(StronglyTypedIdCast::class)]
        public readonly UserId $id,
        #[WithCast(StronglyTypedIdCast::class)]
        public readonly ?BusinessUnitId $businessUnitId = null,
        public readonly ?string $name = null,
    ) {}
}
