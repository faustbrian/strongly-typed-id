<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Fixtures;

use Cline\Struct\AbstractData;
use Cline\Struct\Attributes\CastWith;
use Cline\StronglyTypedId\Casts\Data\StronglyTypedIdCast;

/**
 * @author Brian Faust <brian@cline.sh>
 */
final readonly class UserData extends AbstractData
{
    public function __construct(
        #[CastWith(StronglyTypedIdCast::class)]
        public UserId $id,
        #[CastWith(StronglyTypedIdCast::class)]
        public ?BusinessUnitId $businessUnitId = null,
        public ?string $name = null,
    ) {}
}
