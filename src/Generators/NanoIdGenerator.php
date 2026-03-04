<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\StronglyTypedId\Generators;

use Cline\StronglyTypedId\Contracts\IdGeneratorInterface;

use const M_LN2;

use function ceil;
use function log;
use function mb_strlen;
use function random_bytes;
use function str_repeat;
use function unpack;

/**
 * NanoID Generator.
 *
 * Generates cryptographically secure, URL-friendly unique identifiers.
 * By default, generates 21-character IDs with the same collision probability as UUID v4.
 * Uses uniform random distribution to prevent modulo bias and ensure security.
 *
 * Features:
 * - Cryptographically secure (uses random_bytes)
 * - URL-friendly alphabet (A-Za-z0-9_-)
 * - Uniform distribution (no modulo bias)
 * - Compact (21 chars vs 36 for UUID)
 * - Collision resistant (same as UUID v4 for default length)
 *
 * @psalm-immutable
 *
 * @author Brian Faust <brian@cline.sh>
 * @see https://github.com/ai/nanoid NanoID Specification
 */
final readonly class NanoIdGenerator implements IdGeneratorInterface
{
    private const string DEFAULT_ALPHABET = '_-0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    private const int DEFAULT_SIZE = 21;

    /**
     * Create a new NanoID generator.
     *
     * @param int    $size     The length of the ID to generate (default: 21)
     * @param string $alphabet The characters to use in the ID (default: URL-friendly alphabet)
     */
    public function __construct(
        private int $size = self::DEFAULT_SIZE,
        private string $alphabet = self::DEFAULT_ALPHABET,
    ) {}

    /**
     * Generate a new NanoID identifier.
     *
     * Uses cryptographically secure random bytes and uniform distribution
     * algorithm (masking + rejection sampling) to prevent modulo bias.
     *
     * @return string A URL-friendly NanoID string (e.g., "V1StGXR8_Z5jdHi6B-myT")
     */
    public function generate(): string
    {
        $alphabetLength = mb_strlen($this->alphabet);

        // Handle single character alphabet edge case where log(0) = -INF
        if ($alphabetLength <= 1) {
            return str_repeat($this->alphabet, $this->size);
        }

        $mask = (2 << (int) (log($alphabetLength - 1) / M_LN2)) - 1;
        $step = (int) ceil(1.6 * $mask * $this->size / $alphabetLength);

        if ($step < 1) {
            $step = 1;
        }

        $id = '';

        while (mb_strlen($id) < $this->size) {
            $bytes = unpack('C*', random_bytes($step));

            if ($bytes === false) {
                continue; // @codeCoverageIgnore
            }

            /** @var array<int, int> $bytes */
            foreach ($bytes as $byte) {
                $byte &= $mask;

                if (!isset($this->alphabet[$byte])) {
                    continue;
                }

                $id .= $this->alphabet[$byte];

                if (mb_strlen($id) === $this->size) {
                    return $id;
                }
            }
        }

        return $id;
    }
}
