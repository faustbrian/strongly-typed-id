<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\StronglyTypedId\Generators;

use Cline\StronglyTypedId\Contracts\IdGeneratorInterface;
use Illuminate\Support\Str;

/**
 * Random String Generator.
 *
 * Generates random alphanumeric identifiers using Laravel's Str::random().
 * Produces cryptographically secure random strings suitable for tokens,
 * passwords, and general-purpose unique identifiers.
 *
 * Features:
 * - Cryptographically secure (uses random_bytes internally)
 * - Alphanumeric characters (a-z, A-Z, 0-9)
 * - Configurable length
 * - Lightweight and fast
 *
 * @psalm-immutable
 *
 * @author Brian Faust <brian@cline.sh>
 * @see https://laravel.com/docs/helpers#method-str-random Laravel Str::random()
 */
final readonly class RandomStringGenerator implements IdGeneratorInterface
{
    private const int DEFAULT_LENGTH = 21;

    /**
     * Create a new random string generator.
     *
     * @param int $length The length of the random string to generate (default: 21)
     */
    public function __construct(
        private int $length = self::DEFAULT_LENGTH,
    ) {}

    /**
     * Generate a new random string identifier.
     *
     * Creates a cryptographically secure random string using Laravel's
     * Str::random() helper. The string contains alphanumeric characters.
     *
     * @return string A random alphanumeric string (e.g., "aB3dEf9Hi2kLmN5pQ7r")
     */
    public function generate(): string
    {
        return Str::random($this->length);
    }
}
