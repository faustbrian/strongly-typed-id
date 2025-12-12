<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\StronglyTypedId\Generators;

use Cline\StronglyTypedId\Contracts\IdGeneratorInterface;
use Hashids\Hashids;

use const PHP_INT_MAX;

use function random_int;

/**
 * Hashids Generator.
 *
 * Generates short, unique, URL-safe identifiers using the Hashids library.
 * Hashids encode numeric IDs into short strings that are decodable back to
 * the original numbers, making them ideal for obfuscating database IDs.
 *
 * Features:
 * - Short, URL-safe strings
 * - Configurable salt, alphabet, and minimum length
 * - Bidirectional encoding/decoding
 * - Prevents enumeration attacks on sequential IDs
 *
 * @psalm-immutable
 *
 * @author Brian Faust <brian@cline.sh>
 * @see https://hashids.org/ Hashids Specification
 */
final readonly class HashidsGenerator implements IdGeneratorInterface
{
    private Hashids $hashids;

    /**
     * Create a new Hashids generator instance.
     *
     * @param string $salt      Unique salt to make hashes unpredictable (default: '')
     * @param int    $minLength Minimum length of generated IDs (default: 8)
     * @param string $alphabet  Custom alphabet for encoding (default: Hashids default)
     */
    public function __construct(
        string $salt = '',
        private int $minLength = 8,
        string $alphabet = '',
    ) {
        $this->hashids = new Hashids(
            salt: $salt,
            minHashLength: $this->minLength,
            alphabet: $alphabet !== '' ? $alphabet : 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890',
        );
    }

    /**
     * Generate a new Hashids identifier.
     *
     * Creates a short, unique identifier by encoding a random number.
     * The generated ID will be at least minLength characters long and
     * uses the configured salt and alphabet for obfuscation.
     *
     * @return string A URL-safe Hashids string (e.g., "Xb9kLm2N")
     */
    public function generate(): string
    {
        return $this->hashids->encode(random_int(1, PHP_INT_MAX));
    }
}
