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
use function unpack;

/**
 * Base58 Generator.
 *
 * Generates cryptographically secure identifiers using the base58 alphabet.
 * Base58 excludes visually ambiguous characters (0, O, I, l) for improved readability.
 * Commonly used in Bitcoin addresses and other systems requiring human-readable IDs.
 *
 * Features:
 * - Cryptographically secure (uses random_bytes)
 * - Human-readable (no confusing characters)
 * - Uniform distribution (no modulo bias)
 * - Compact representation
 * - URL-safe
 *
 * Alphabet: 123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz
 * Excludes: 0 (zero), O (capital o), I (capital i), l (lowercase L)
 *
 * @psalm-immutable
 *
 * @author Brian Faust <brian@cline.sh>
 * @see https://en.wikipedia.org/wiki/Binary-to-text_encoding#Base58 Base58 Encoding
 */
final readonly class Base58Generator implements IdGeneratorInterface
{
    private const string BASE58_ALPHABET = '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz';

    private const int DEFAULT_SIZE = 21;

    /**
     * Create a new Base58 generator.
     *
     * @param int $size The length of the ID to generate (default: 21)
     */
    public function __construct(
        private int $size = self::DEFAULT_SIZE,
    ) {}

    /**
     * Generate a new Base58 identifier.
     *
     * Uses cryptographically secure random bytes and uniform distribution
     * algorithm (masking + rejection sampling) to prevent modulo bias.
     *
     * @return string A base58-encoded string (e.g., "V1StGXR8Z5jdHi6BmyT")
     */
    public function generate(): string
    {
        $alphabetLength = mb_strlen(self::BASE58_ALPHABET);
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

                if (!isset(self::BASE58_ALPHABET[$byte])) {
                    continue;
                }

                $id .= self::BASE58_ALPHABET[$byte];

                if (mb_strlen($id) === $this->size) {
                    return $id;
                }
            }
        }

        return $id;
    }
}
