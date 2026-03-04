<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\StronglyTypedId\Generators;

use Cline\StronglyTypedId\Contracts\IdGeneratorInterface;

use function bin2hex;
use function random_bytes;

/**
 * Random Bytes Generator.
 *
 * Generates cryptographically secure random identifiers using PHP's random_bytes()
 * and hexadecimal encoding. Produces identifiers suitable for security tokens,
 * session IDs, and other contexts requiring strong randomness.
 *
 * Features:
 * - Cryptographically secure (uses random_bytes)
 * - Hexadecimal encoding (0-9a-f)
 * - Configurable byte length
 * - Predictable output length (length * 2 chars)
 *
 * @psalm-immutable
 *
 * @author Brian Faust <brian@cline.sh>
 * @see https://www.php.net/manual/en/function.random-bytes.php PHP random_bytes()
 */
final readonly class RandomBytesGenerator implements IdGeneratorInterface
{
    private const int DEFAULT_BYTES = 16;

    /**
     * Create a new random bytes generator.
     *
     * @param int<1, max> $bytes The number of random bytes to generate (default: 16)
     *                           Output length will be bytes * 2 characters
     */
    public function __construct(
        private int $bytes = self::DEFAULT_BYTES,
    ) {}

    /**
     * Generate a new random bytes identifier.
     *
     * Creates a cryptographically secure random identifier by generating
     * random bytes and encoding them as hexadecimal. Output length is
     * always bytes * 2 characters.
     *
     * @return string A hexadecimal random string (e.g., "4f3b7c2a8e1d9f6b5a3c8e2d7f1b4a9c")
     */
    public function generate(): string
    {
        return bin2hex(random_bytes($this->bytes));
    }
}
