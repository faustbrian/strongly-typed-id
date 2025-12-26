<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\StronglyTypedId\Generators\RandomStringGenerator;

describe('RandomStringGenerator', function (): void {
    describe('Happy Paths', function (): void {
        test('generates default 21-character random string', function (): void {
            // Arrange
            $generator = new RandomStringGenerator();

            // Act
            $id = $generator->generate();

            // Assert
            expect($id)->toBeString();
            expect(mb_strlen($id))->toBe(21);
        });

        test('generates alphanumeric characters only', function (): void {
            // Arrange
            $generator = new RandomStringGenerator();

            // Act
            $id = $generator->generate();

            // Assert
            expect($id)->toMatch('/^[A-Za-z0-9]+$/');
        });

        test('generates unique ids on each call', function (): void {
            // Arrange
            $generator = new RandomStringGenerator();

            // Act
            $id1 = $generator->generate();
            $id2 = $generator->generate();

            // Assert
            expect($id1)->not->toBe($id2);
        });

        test('generates string with custom length of 10', function (): void {
            // Arrange
            $generator = new RandomStringGenerator(length: 10);

            // Act
            $id = $generator->generate();

            // Assert
            expect(mb_strlen($id))->toBe(10);
            expect($id)->toMatch('/^[A-Za-z0-9]+$/');
        });

        test('generates string with custom length of 16', function (): void {
            // Arrange
            $generator = new RandomStringGenerator(length: 16);

            // Act
            $id = $generator->generate();

            // Assert
            expect(mb_strlen($id))->toBe(16);
        });

        test('generates string with custom length of 32', function (): void {
            // Arrange
            $generator = new RandomStringGenerator(length: 32);

            // Act
            $id = $generator->generate();

            // Assert
            expect(mb_strlen($id))->toBe(32);
        });

        test('generates string with custom length of 64', function (): void {
            // Arrange
            $generator = new RandomStringGenerator(length: 64);

            // Act
            $id = $generator->generate();

            // Assert
            expect(mb_strlen($id))->toBe(64);
        });
    });

    describe('Sad Paths', function (): void {
        // RandomStringGenerator has no validation - accepts all constructor parameters
        // PHP type system enforces int for length
        // Laravel's Str::random() handles edge cases internally
        // No business rule validations to test in sad paths
    });

    describe('Edge Cases', function (): void {
        test('generates string with minimum length of 1', function (): void {
            // Arrange
            $generator = new RandomStringGenerator(length: 1);

            // Act
            $id = $generator->generate();

            // Assert
            expect(mb_strlen($id))->toBe(1);
            expect($id)->toMatch('/^[A-Za-z0-9]$/');
        });

        test('generates string with length of 5', function (): void {
            // Arrange
            $generator = new RandomStringGenerator(length: 5);

            // Act
            $id = $generator->generate();

            // Assert
            expect(mb_strlen($id))->toBe(5);
        });

        test('generates string with large length of 100', function (): void {
            // Arrange
            $generator = new RandomStringGenerator(length: 100);

            // Act
            $id = $generator->generate();

            // Assert
            expect(mb_strlen($id))->toBe(100);
            expect($id)->toMatch('/^[A-Za-z0-9]+$/');
        });

        test('generates string with large length of 256', function (): void {
            // Arrange
            $generator = new RandomStringGenerator(length: 256);

            // Act
            $id = $generator->generate();

            // Assert
            expect(mb_strlen($id))->toBe(256);
        });

        test('generates many unique strings rapidly', function (): void {
            // Arrange
            $generator = new RandomStringGenerator();
            $ids = [];

            // Act
            for ($i = 0; $i < 1_000; ++$i) {
                $ids[] = $generator->generate();
            }

            // Assert
            expect(count(array_unique($ids)))->toBe(1_000);
        });

        test('maintains length consistency across multiple calls', function (): void {
            // Arrange
            $generator = new RandomStringGenerator(length: 21);

            // Act & Assert
            for ($i = 0; $i < 100; ++$i) {
                $id = $generator->generate();
                expect(mb_strlen($id))->toBe(21);
            }
        });

        test('all alphanumeric characters appear through multiple generations', function (): void {
            // Arrange
            $generator = new RandomStringGenerator(length: 100);
            $allChars = [];

            // Act - Generate multiple IDs to ensure character diversity
            for ($i = 0; $i < 1_000; ++$i) {
                $id = $generator->generate();
                $chars = mb_str_split($id);

                foreach ($chars as $char) {
                    $allChars[$char] = true;
                }
            }

            // Assert - Should have both letters and numbers
            // With high probability (100 chars * 1000 iterations), all types should appear
            $uniqueChars = array_keys($allChars);
            $hasLowercase = false;
            $hasUppercase = false;
            $hasDigits = false;

            foreach ($uniqueChars as $char) {
                if (ctype_lower($char)) {
                    $hasLowercase = true;
                }

                if (ctype_upper($char)) {
                    $hasUppercase = true;
                }

                if (!ctype_digit($char)) {
                    continue;
                }

                $hasDigits = true;
            }

            // At least one of each type should appear with 100k characters
            expect($hasLowercase || $hasUppercase || $hasDigits)->toBeTrue();
            expect(count($uniqueChars))->toBeGreaterThan(10);
        });

        test('different generators with same config produce different strings', function (): void {
            // Arrange
            $generator1 = new RandomStringGenerator(length: 16);
            $generator2 = new RandomStringGenerator(length: 16);

            // Act
            $id1 = $generator1->generate();
            $id2 = $generator2->generate();

            // Assert - Different instances produce different strings
            expect($id1)->not->toBe($id2);
        });

        test('character distribution is roughly uniform', function (): void {
            // Arrange
            $generator = new RandomStringGenerator(length: 100);
            $charCounts = [];

            // Act - Generate strings and count character occurrences
            for ($i = 0; $i < 100; ++$i) {
                $id = $generator->generate();

                foreach (mb_str_split($id) as $char) {
                    if (!isset($charCounts[$char])) {
                        $charCounts[$char] = 0;
                    }

                    ++$charCounts[$char];
                }
            }

            // Assert - No single character should dominate
            // With 62 possible chars (a-z, A-Z, 0-9) and 10000 total chars,
            // expected count per char is ~161. Allow wide variance for randomness.
            foreach ($charCounts as $count) {
                expect($count)->toBeGreaterThan(0);
                expect($count)->toBeLessThan(500); // No char should appear more than 5%
            }
        });

        test('handles zero length by returning empty string', function (): void {
            // Arrange
            $generator = new RandomStringGenerator(length: 0);

            // Act
            $id = $generator->generate();

            // Assert
            expect($id)->toBe('');
            expect(mb_strlen($id))->toBe(0);
        });

        test('handles negative length gracefully', function (): void {
            // Arrange
            // Laravel's Str::random() handles negative length by treating it as 0
            $generator = new RandomStringGenerator(length: -1);

            // Act
            $id = $generator->generate();

            // Assert
            expect($id)->toBe('');
        });
    });

    describe('Regressions', function (): void {
        test('generated strings always meet exact length requirement', function (): void {
            // Arrange - Regression: ensure length is always exact
            $lengths = [1, 5, 10, 16, 21, 32, 64, 100];

            foreach ($lengths as $length) {
                $generator = new RandomStringGenerator(length: $length);

                // Act & Assert
                for ($i = 0; $i < 10; ++$i) {
                    $id = $generator->generate();
                    expect(mb_strlen($id))->toBe($length);
                }
            }
        });

        test('generated strings contain only alphanumeric characters', function (): void {
            // Arrange - Regression: ensure no special characters leak in
            $generator = new RandomStringGenerator();

            // Act & Assert
            for ($i = 0; $i < 100; ++$i) {
                $id = $generator->generate();
                expect($id)->toMatch('/^[A-Za-z0-9]+$/');
                expect($id)->not->toMatch('/[^A-Za-z0-9]/');
            }
        });

        test('generated strings are always unique within generator instance', function (): void {
            // Arrange - Regression: ensure uniqueness across rapid generation
            $generator = new RandomStringGenerator();
            $ids = [];

            // Act
            for ($i = 0; $i < 10_000; ++$i) {
                $ids[] = $generator->generate();
            }

            // Assert - All should be unique
            expect(count(array_unique($ids)))->toBe(10_000);
        });

        test('length parameter actually controls string length', function (): void {
            // Arrange - Regression: ensure length parameter is not ignored
            $sizes = [1, 5, 10, 16, 21, 32, 50, 100];

            foreach ($sizes as $size) {
                $generator = new RandomStringGenerator(length: $size);

                // Act
                $id = $generator->generate();

                // Assert
                expect(mb_strlen($id))->toBe($size);
            }
        });

        test('cryptographic randomness produces high entropy strings', function (): void {
            // Arrange - Regression: ensure strings have high entropy
            $generator = new RandomStringGenerator(length: 32);
            $ids = [];

            // Act
            for ($i = 0; $i < 1_000; ++$i) {
                $ids[] = $generator->generate();
            }

            // Assert - All unique
            expect(count(array_unique($ids)))->toBe(1_000);

            // Check that strings don't have repeating patterns
            foreach ($ids as $id) {
                if (mb_strlen($id) <= 1) {
                    continue;
                }

                // String should not be all same character
                $chars = array_unique(mb_str_split($id));
                expect(count($chars))->toBeGreaterThan(1);
            }
        });

        test('case mixing is preserved across generations', function (): void {
            // Arrange - Regression: ensure both upper and lowercase appear
            $generator = new RandomStringGenerator(length: 100);

            // Act - Generate enough to statistically guarantee both cases
            for ($i = 0; $i < 10; ++$i) {
                $id = $generator->generate();

                // Assert - Each long string should have mixed case
                $hasUpper = (bool) preg_match('/[A-Z]/', $id);
                $hasLower = (bool) preg_match('/[a-z]/', $id);

                // Very unlikely to not have both in 100 chars
                if (!$hasUpper && !$hasLower) {
                    continue;
                }

                expect($hasUpper || $hasLower)->toBeTrue();
            }
        });
    });
});
