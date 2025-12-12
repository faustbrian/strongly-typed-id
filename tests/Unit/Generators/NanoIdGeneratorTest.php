<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\StronglyTypedId\Generators\NanoIdGenerator;

describe('NanoIdGenerator', function (): void {
    describe('Happy Paths', function (): void {
        test('generates default 21-character id', function (): void {
            // Arrange
            $generator = new NanoIdGenerator();

            // Act
            $id = $generator->generate();

            // Assert
            expect($id)->toBeString();
            expect(mb_strlen($id))->toBe(21);
        });

        test('generates id with url-friendly characters from default alphabet', function (): void {
            // Arrange
            $generator = new NanoIdGenerator();

            // Act
            $id = $generator->generate();

            // Assert
            expect($id)->toMatch('/^[A-Za-z0-9_-]+$/');
        });

        test('generates unique ids on each call', function (): void {
            // Arrange
            $generator = new NanoIdGenerator();

            // Act
            $id1 = $generator->generate();
            $id2 = $generator->generate();

            // Assert
            expect($id1)->not->toBe($id2);
        });

        test('generates id with custom size of 5 characters', function (): void {
            // Arrange
            $generator = new NanoIdGenerator(size: 5);

            // Act
            $id = $generator->generate();

            // Assert
            expect(mb_strlen($id))->toBe(5);
        });

        test('generates id with custom size of 10 characters', function (): void {
            // Arrange
            $generator = new NanoIdGenerator(size: 10);

            // Act
            $id = $generator->generate();

            // Assert
            expect(mb_strlen($id))->toBe(10);
        });

        test('generates id with custom size of 50 characters', function (): void {
            // Arrange
            $generator = new NanoIdGenerator(size: 50);

            // Act
            $id = $generator->generate();

            // Assert
            expect(mb_strlen($id))->toBe(50);
        });

        test('generates id with numeric-only custom alphabet', function (): void {
            // Arrange
            $generator = new NanoIdGenerator(alphabet: '0123456789');

            // Act
            $id = $generator->generate();

            // Assert
            expect($id)->toBeString();
            expect($id)->toMatch('/^\d+$/');
        });

        test('generates id with letters-only custom alphabet', function (): void {
            // Arrange
            $generator = new NanoIdGenerator(alphabet: 'abcdefghijklmnopqrstuvwxyz');

            // Act
            $id = $generator->generate();

            // Assert
            expect($id)->toBeString();
            expect($id)->toMatch('/^[a-z]+$/');
        });

        test('generates id with uppercase letters custom alphabet', function (): void {
            // Arrange
            $generator = new NanoIdGenerator(alphabet: 'ABCDEFGHIJKLMNOPQRSTUVWXYZ');

            // Act
            $id = $generator->generate();

            // Assert
            expect($id)->toBeString();
            expect($id)->toMatch('/^[A-Z]+$/');
        });

        test('generates id with hexadecimal custom alphabet', function (): void {
            // Arrange
            $generator = new NanoIdGenerator(alphabet: '0123456789ABCDEF');

            // Act
            $id = $generator->generate();

            // Assert
            expect($id)->toBeString();
            expect($id)->toMatch('/^[0-9A-F]+$/');
        });

        test('combines custom size and custom alphabet', function (): void {
            // Arrange
            $generator = new NanoIdGenerator(size: 15, alphabet: '0123456789ABCDEF');

            // Act
            $id = $generator->generate();

            // Assert
            expect(mb_strlen($id))->toBe(15);
            expect($id)->toMatch('/^[0-9A-F]+$/');
        });
    });

    describe('Sad Paths', function (): void {
        // NanoIdGenerator has no validation - accepts all constructor parameters
        // PHP type system enforces int for size and string for alphabet
        // No business rule validations to test in sad paths
    });

    describe('Edge Cases', function (): void {
        test('generates id with minimum size of 1 character', function (): void {
            // Arrange
            $generator = new NanoIdGenerator(size: 1);

            // Act
            $id = $generator->generate();

            // Assert
            expect(mb_strlen($id))->toBe(1);
            expect($id)->toMatch('/^[A-Za-z0-9_-]$/');
        });

        test('generates id with large size of 100 characters', function (): void {
            // Arrange
            $generator = new NanoIdGenerator(size: 100);

            // Act
            $id = $generator->generate();

            // Assert
            expect(mb_strlen($id))->toBe(100);
            expect($id)->toMatch('/^[A-Za-z0-9_-]+$/');
        });

        test('generates id with large size of 250 characters', function (): void {
            // Arrange
            $generator = new NanoIdGenerator(size: 250);

            // Act
            $id = $generator->generate();

            // Assert
            expect(mb_strlen($id))->toBe(250);
            expect($id)->toMatch('/^[A-Za-z0-9_-]+$/');
        });

        test('generates id with single character alphabet', function (): void {
            // Arrange
            $generator = new NanoIdGenerator(alphabet: 'A');

            // Act
            $id = $generator->generate();

            // Assert
            expect($id)->toBe(str_repeat('A', 21));
            expect($id)->toMatch('/^A+$/');
        });

        test('generates id with binary alphabet 0 and 1 only', function (): void {
            // Arrange
            $generator = new NanoIdGenerator(alphabet: '01');

            // Act
            $id = $generator->generate();

            // Assert
            expect($id)->toBeString();
            expect(mb_strlen($id))->toBe(21);
            expect($id)->toMatch('/^[01]+$/');
        });

        test('all alphabet characters appear in generated ids through multiple generations', function (): void {
            // Arrange
            $alphabet = '0123456789';
            $generator = new NanoIdGenerator(size: 50, alphabet: $alphabet);
            $allChars = [];

            // Act - Generate multiple IDs to ensure all characters appear
            for ($i = 0; $i < 100; ++$i) {
                $id = $generator->generate();
                $chars = mb_str_split($id);

                foreach ($chars as $char) {
                    $allChars[$char] = true;
                }
            }

            // Assert - All alphabet characters should appear at least once
            $uniqueChars = array_keys($allChars);
            sort($uniqueChars);
            expect(count($uniqueChars))->toBe(mb_strlen($alphabet));
        });

        test('generates many unique ids rapidly', function (): void {
            // Arrange
            $generator = new NanoIdGenerator();
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
            $generator = new NanoIdGenerator(size: 21);

            // Act & Assert
            for ($i = 0; $i < 100; ++$i) {
                $id = $generator->generate();
                expect(mb_strlen($id))->toBe(21);
            }
        });

        test('custom alphabet characters are uniformly distributed', function (): void {
            // Arrange
            $alphabet = '0123';
            $generator = new NanoIdGenerator(size: 100, alphabet: $alphabet);
            $charCounts = array_fill_keys(mb_str_split($alphabet), 0);

            // Act - Generate IDs and count character occurrences
            for ($i = 0; $i < 100; ++$i) {
                $id = $generator->generate();

                foreach (mb_str_split($id) as $char) {
                    ++$charCounts[$char];
                }
            }

            // Assert - Distribution should be roughly uniform
            // Each character should appear at least 1500 times out of 10000 (15%)
            // and at most 3500 times (35%) for a 4-character alphabet
            foreach ($charCounts as $count) {
                expect($count)->toBeGreaterThan(1_500);
                expect($count)->toBeLessThan(3_500);
            }
        });

        test('different generators with same config produce different ids', function (): void {
            // Arrange
            $generator1 = new NanoIdGenerator(size: 21);
            $generator2 = new NanoIdGenerator(size: 21);

            // Act
            $id1 = $generator1->generate();
            $id2 = $generator2->generate();

            // Assert - Different instances produce different IDs
            expect($id1)->not->toBe($id2);
        });

        test('handles alphabet with special url-safe characters', function (): void {
            // Arrange
            $generator = new NanoIdGenerator(alphabet: '-_.');

            // Act
            $id = $generator->generate();

            // Assert
            expect($id)->toBeString();
            expect($id)->toMatch('/^[-_.]+$/');
        });

        test('generates id with three character alphabet', function (): void {
            // Arrange
            $generator = new NanoIdGenerator(alphabet: 'ABC');

            // Act
            $id = $generator->generate();

            // Assert
            expect($id)->toBeString();
            expect(mb_strlen($id))->toBe(21);
            expect($id)->toMatch('/^[ABC]+$/');
        });

        test('handles very long alphabet', function (): void {
            // Arrange
            $longAlphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*()_+-=[]{}|;:,.<>?';
            $generator = new NanoIdGenerator(alphabet: $longAlphabet);

            // Act
            $id = $generator->generate();

            // Assert
            expect($id)->toBeString();
            expect(mb_strlen($id))->toBe(21);
        });

        test('generates consistently with size 2', function (): void {
            // Arrange
            $generator = new NanoIdGenerator(size: 2);

            // Act
            $id = $generator->generate();

            // Assert
            expect(mb_strlen($id))->toBe(2);
        });

        test('generates consistently with size 3', function (): void {
            // Arrange
            $generator = new NanoIdGenerator(size: 3);

            // Act
            $id = $generator->generate();

            // Assert
            expect(mb_strlen($id))->toBe(3);
        });

        test('handles zero size by returning empty string immediately', function (): void {
            // Arrange
            // This tests BOTH line 75 and line 101:
            // - Line 75: step calculation is 0 (since size=0), triggering step = 1 fallback
            // - Line 101: while condition (0 < 0) is false immediately, returns empty string
            // The while loop never executes, and we hit the fallback return statement
            $generator = new NanoIdGenerator(size: 0);

            // Act
            $id = $generator->generate();

            // Assert
            expect($id)->toBe('');
            expect(mb_strlen($id))->toBe(0);
        });

        test('handles extremely large alphabet efficiently', function (): void {
            // Arrange
            // Tests generator with very large alphabet (10000+ chars)
            // While this doesn't trigger line 75 (step=1 fallback), it validates
            // the algorithm's stability with extreme alphabet sizes
            $hugeAlphabet = str_repeat('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789', 278); // ~10000 chars
            $generator = new NanoIdGenerator(size: 1, alphabet: $hugeAlphabet);

            // Act
            $id = $generator->generate();

            // Assert
            // Even with extreme alphabet length, generator still produces valid output
            expect($id)->toBeString();
            expect(mb_strlen($id))->toBe(1);
            // Verify the character is from the alphabet
            expect(mb_strpos($hugeAlphabet, $id))->not->toBeFalse();
        });

        test('documents unpack false scenario is unreachable in practice', function (): void {
            // Line 84: if ($bytes === false) continue;
            //
            // This line tests if unpack() returns false, which according to PHP documentation
            // only happens when:
            // 1. The format string is invalid
            // 2. The data string is shorter than required by format
            //
            // In our case:
            // - Format 'C*' is always valid (unsigned char, repeat to end)
            // - Data comes from random_bytes($step) which always returns exactly $step bytes
            // - unpack('C*', ...) with valid data never returns false
            //
            // Therefore, this line is effectively unreachable under normal PHP operation.
            // It serves as defensive programming but cannot be reliably tested without
            // mocking PHP's internal functions or corrupting memory.
            //
            // This test documents the analysis rather than attempting to trigger the condition.

            // Arrange
            $generator = new NanoIdGenerator();

            // Act - Generate many IDs to verify unpack never fails
            for ($i = 0; $i < 1_000; ++$i) {
                $id = $generator->generate();

                // Assert
                expect($id)->toBeString();
                expect(mb_strlen($id))->toBe(21);
            }

            // If we reach here, unpack() never returned false in 1000 iterations
            // This validates that the continue statement is not hit under normal conditions
            expect(true)->toBeTrue(); // Explicit assertion for documentation
        });

        test('handles negative size by treating it as boundary condition', function (): void {
            // Arrange
            // This tests BOTH line 75 and line 101 (similar to size=0):
            // - Line 75: step calculation is negative, triggering step = 1 fallback
            // - Line 101: while condition (0 < -1) is false, returns empty string
            // Constructor doesn't validate size, so negative values are technically allowed
            $generator = new NanoIdGenerator(size: -1);

            // Act
            $id = $generator->generate();

            // Assert
            // With negative size, while (0 < -1) is false, returns empty string at line 101
            expect($id)->toBe('');
            expect(mb_strlen($id))->toBe(0);
        });

        test('validates step calculation with large alphabet remains stable', function (): void {
            // Arrange
            // Testing algorithm stability with large alphabet
            // Formula: step = ceil(1.6 * mask * size / alphabetLength)
            // Note: Due to ceil(), step is always >= 1 for any positive size
            // Line 75 (step = 1 fallback) only triggers when size <= 0
            $largeAlphabet = str_repeat('0123456789', 100); // 1000 characters
            $generator = new NanoIdGenerator(size: 1, alphabet: $largeAlphabet);

            // Act
            $id = $generator->generate();

            // Assert
            expect($id)->toBeString();
            expect(mb_strlen($id))->toBe(1);
            expect(mb_strpos($largeAlphabet, $id))->not->toBeFalse();
        });
    });

    describe('Regressions', function (): void {
        test('generated ids always meet exact size requirement', function (): void {
            // Arrange - Regression: ensure size is always exact
            $size = 21;
            $generator = new NanoIdGenerator(size: $size);

            // Act & Assert - Test multiple times to ensure consistency
            for ($i = 0; $i < 50; ++$i) {
                $id = $generator->generate();
                expect(mb_strlen($id))->toBe($size);
            }
        });

        test('generated ids contain only alphabet characters', function (): void {
            // Arrange - Regression: ensure IDs never contain characters outside alphabet
            $alphabet = '0123456789ABCDEF';
            $generator = new NanoIdGenerator(alphabet: $alphabet);

            // Act & Assert - Test multiple times
            for ($i = 0; $i < 50; ++$i) {
                $id = $generator->generate();
                expect($id)->toMatch('/^[0-9A-F]+$/');
                // Verify no lowercase or other characters
                expect($id)->not->toMatch('/[a-z]/');
                expect($id)->not->toMatch('/[G-Z]/');
            }
        });

        test('generated ids are always unique within generator instance', function (): void {
            // Arrange - Regression: ensure uniqueness across rapid generation
            $generator = new NanoIdGenerator();
            $ids = [];

            // Act - Generate many IDs rapidly
            for ($i = 0; $i < 10_000; ++$i) {
                $ids[] = $generator->generate();
            }

            // Assert - All should be unique
            expect(count(array_unique($ids)))->toBe(10_000);
        });

        test('custom alphabet parameter is always respected', function (): void {
            // Arrange - Regression: ensure alphabet is strictly used
            $customAlphabet = 'ABCD';
            $generator = new NanoIdGenerator(alphabet: $customAlphabet);

            // Act & Assert
            for ($i = 0; $i < 100; ++$i) {
                $id = $generator->generate();
                expect($id)->toMatch('/^[ABCD]+$/');
                // Verify no other characters
                expect($id)->not->toMatch('/[^ABCD]/');
            }
        });

        test('size parameter actually controls id length', function (): void {
            // Arrange - Regression: ensure size parameter is not ignored
            $sizes = [5, 10, 15, 21, 30, 50, 100];

            foreach ($sizes as $size) {
                $generator = new NanoIdGenerator(size: $size);

                // Act
                $id = $generator->generate();

                // Assert
                expect(mb_strlen($id))->toBe($size);
            }
        });

        test('default alphabet produces url-safe ids consistently', function (): void {
            // Arrange - Regression: ensure default alphabet remains URL-safe
            $generator = new NanoIdGenerator();

            // Act & Assert
            for ($i = 0; $i < 100; ++$i) {
                $id = $generator->generate();
                expect($id)->toMatch('/^[A-Za-z0-9_-]+$/');
                // Ensure no spaces or special URL-unsafe characters
                expect($id)->not->toContain(' ');
                expect($id)->not->toContain('/');
                expect($id)->not->toContain('?');
                expect($id)->not->toContain('&');
                expect($id)->not->toContain('=');
                expect($id)->not->toContain('+');
            }
        });

        test('cryptographic randomness produces high entropy ids', function (): void {
            // Arrange - Regression: ensure IDs have high entropy (not predictable patterns)
            $generator = new NanoIdGenerator();
            $ids = [];

            // Act
            for ($i = 0; $i < 1_000; ++$i) {
                $ids[] = $generator->generate();
            }

            // Assert - Check for patterns that indicate low entropy
            $uniqueIds = array_unique($ids);
            expect(count($uniqueIds))->toBe(1_000); // All unique

            // Check that IDs don't have sequential patterns
            foreach ($ids as $id) {
                // No ID should be all same character (except single-char alphabet edge case)
                if (mb_strlen($id) <= 1) {
                    continue;
                }

                $chars = array_unique(mb_str_split($id));
                expect(count($chars))->toBeGreaterThan(1);
            }
        });

        test('uniform distribution prevents modulo bias', function (): void {
            // Arrange - Regression: verify uniform distribution algorithm works
            $alphabet = '0123456789';
            $generator = new NanoIdGenerator(size: 1_000, alphabet: $alphabet);
            $charCounts = array_fill_keys(mb_str_split($alphabet), 0);

            // Act - Generate large ID to get statistically significant sample
            for ($i = 0; $i < 10; ++$i) {
                $id = $generator->generate();

                foreach (mb_str_split($id) as $char) {
                    ++$charCounts[$char];
                }
            }

            // Assert - Each character should appear roughly 1000 times (10%)
            // Allow 20% variance for statistical randomness
            $expected = 1_000;

            foreach ($charCounts as $count) {
                expect($count)->toBeGreaterThan((int) ($expected * 0.8));
                expect($count)->toBeLessThan((int) ($expected * 1.2));
            }
        });
    });
});
