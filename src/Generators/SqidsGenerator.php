<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\StronglyTypedId\Generators;

use Cline\StronglyTypedId\Contracts\IdGeneratorInterface;
use Sqids\Sqids;

use const PHP_INT_MAX;

use function random_int;

/**
 * Sqids Generator.
 *
 * Generates short, unique, URL-safe identifiers using the Sqids library.
 * Sqids encode numeric IDs into short strings that are safe for URLs and
 * provide a compact alternative to UUIDs while remaining human-friendly.
 *
 * Features:
 * - Short, URL-safe strings
 * - Configurable alphabet and minimum length
 * - Deterministic encoding/decoding
 * - Collision-free within the same configuration
 *
 * @see https://sqids.org/ Sqids Specification
 *
 * @psalm-immutable
 *
 * @author Brian Faust <brian@cline.sh>
 */
final readonly class SqidsGenerator implements IdGeneratorInterface
{
    private Sqids $sqids;

    /**
     * Create a new Sqids generator instance.
     *
     * @param string $alphabet  Custom alphabet for encoding (default: Sqids default alphabet)
     * @param int    $minLength Minimum length of generated IDs (default: 8)
     */
    public function __construct(
        string $alphabet = '',
        private int $minLength = 8,
    ) {
        if ($alphabet !== '') {
            $this->sqids = new Sqids(alphabet: $alphabet, minLength: $this->minLength);
        } else {
            $this->sqids = new Sqids(minLength: $this->minLength);
        }
    }

    /**
     * Generate a new Sqids identifier.
     *
     * Creates a short, unique identifier by encoding a random number.
     * The generated ID will be at least minLength characters long and
     * uses the configured alphabet.
     *
     * @return string A URL-safe Sqids string (e.g., "4d9fND1xQ")
     */
    public function generate(): string
    {
        return $this->sqids->encode([random_int(1, PHP_INT_MAX)]);
    }
}
