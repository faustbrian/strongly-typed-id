<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Fixtures;

use Cline\StronglyTypedId\ValueObjects\StronglyTypedId;

/**
 * @psalm-immutable
 *
 * @author Brian Faust <brian@cline.sh>
 */
final readonly class BusinessUnitId extends StronglyTypedId {}
