<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\StronglyTypedId\Generators\RandomBytesGenerator;

describe('RandomBytesGenerator', function (): void {
    describe('Happy Paths', function (): void {
        test('generates default 32-character hexadecimal string', function (): void {
            // Arrange
            $generator = new RandomBytesGenerator();

            // Act
            $id = $generator->generate();

            // Assert
            expect($id)->toBeString();
            expect(mb_strlen($id))->toBe(32); // 16 bytes = 32 hex chars
        });

        test('generates hexadecimal characters only', function (): void {
            // Arrange
            $generator = new RandomBytesGenerator();

            // Act
            $id = $generator->generate();

            // Assert
            expect($id)->toMatch('/^[0-9a-f]+$/');
        });

        test('generates unique ids on each call', function (): void {
            // Arrange
            $generator = new RandomBytesGenerator();

            // Act
            $id1 = $generator->generate();
            $id2 = $generator->generate();

            // Assert
            expect($id1)->not->toBe($id2);
        });

        test('generates with 8 bytes producing 16 hex characters', function (): void {
            // Arrange
            $generator = new RandomBytesGenerator(bytes: 8);

            // Act
            $id = $generator->generate();

            // Assert
            expect(mb_strlen($id))->toBe(16);
            expect($id)->toMatch('/^[0-9a-f]+$/');
        });

        test('generates with 16 bytes producing 32 hex characters', function (): void {
            // Arrange
            $generator = new RandomBytesGenerator(bytes: 16);

            // Act
            $id = $generator->generate();

            // Assert
            expect(mb_strlen($id))->toBe(32);
        });

        test('generates with 32 bytes producing 64 hex characters', function (): void {
            // Arrange
            $generator = new RandomBytesGenerator(bytes: 32);

            // Act
            $id = $generator->generate();

            // Assert
            expect(mb_strlen($id))->toBe(64);
        });

        test('generates with 64 bytes producing 128 hex characters', function (): void {
            // Arrange
            $generator = new RandomBytesGenerator(bytes: 64);

            // Act
            $id = $generator->generate();

            // Assert
            expect(mb_strlen($id))->toBe(128);
        });
    });

    describe('Sad Paths', function (): void {
        // RandomBytesGenerator has no validation - accepts all constructor parameters
        // PHP type system enforces int for bytes
        // PHP's random_bytes() will throw exception for invalid values
        // Exception testing is covered in edge cases
    });

    describe('Edge Cases', function (): void {
        test('generates with 1 byte producing 2 hex characters', function (): void {
            // Arrange
            $generator = new RandomBytesGenerator(bytes: 1);

            // Act
            $id = $generator->generate();

            // Assert
            expect(mb_strlen($id))->toBe(2);
            expect($id)->toMatch('/^[0-9a-f]{2}$/');
        });

        test('generates with 4 bytes producing 8 hex characters', function (): void {
            // Arrange
            $generator = new RandomBytesGenerator(bytes: 4);

            // Act
            $id = $generator->generate();

            // Assert
            expect(mb_strlen($id))->toBe(8);
        });

        test('generates with large byte count of 128', function (): void {
            // Arrange
            $generator = new RandomBytesGenerator(bytes: 128);

            // Act
            $id = $generator->generate();

            // Assert
            expect(mb_strlen($id))->toBe(256);
            expect($id)->toMatch('/^[0-9a-f]+$/');
        });

        test('generates with large byte count of 256', function (): void {
            // Arrange
            $generator = new RandomBytesGenerator(bytes: 256);

            // Act
            $id = $generator->generate();

            // Assert
            expect(mb_strlen($id))->toBe(512);
        });

        test('generates many unique ids rapidly', function (): void {
            // Arrange
            $generator = new RandomBytesGenerator();
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
            $generator = new RandomBytesGenerator(bytes: 16);

            // Act & Assert
            for ($i = 0; $i < 100; ++$i) {
                $id = $generator->generate();
                expect(mb_strlen($id))->toBe(32);
            }
        });

        test('all hexadecimal characters appear through multiple generations', function (): void {
            // Arrange
            $generator = new RandomBytesGenerator(bytes: 32);
            $allChars = [];

            // Act - Generate multiple IDs to ensure all hex chars appear
            for ($i = 0; $i < 1_000; ++$i) {
                $id = $generator->generate();
                $chars = mb_str_split($id);

                foreach ($chars as $char) {
                    $allChars[$char] = true;
                }
            }

            // Assert - All 16 hex characters should appear
            $uniqueChars = array_keys($allChars);
            sort($uniqueChars);

            // Should have 0-9 and a-f (16 characters total)
            expect(count($uniqueChars))->toBe(16);

            // Verify all hex characters present
            foreach (['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'a', 'b', 'c', 'd', 'e', 'f'] as $hexChar) {
                expect($allChars)->toHaveKey($hexChar);
            }
        });

        test('different generators with same config produce different ids', function (): void {
            // Arrange
            $generator1 = new RandomBytesGenerator(bytes: 16);
            $generator2 = new RandomBytesGenerator(bytes: 16);

            // Act
            $id1 = $generator1->generate();
            $id2 = $generator2->generate();

            // Assert - Different instances produce different IDs
            expect($id1)->not->toBe($id2);
        });

        test('character distribution is uniform', function (): void {
            // Arrange
            $generator = new RandomBytesGenerator(bytes: 100);
            $charCounts = array_fill_keys(mb_str_split('0123456789abcdef'), 0);

            // Act - Generate IDs and count character occurrences
            for ($i = 0; $i < 100; ++$i) {
                $id = $generator->generate();

                foreach (mb_str_split($id) as $char) {
                    ++$charCounts[$char];
                }
            }

            // Assert - Distribution should be roughly uniform
            // Each hex char should appear ~1250 times out of 20000 (6.25%)
            // Allow variance for randomness (4% to 9%)
            foreach ($charCounts as $count) {
                expect($count)->toBeGreaterThan(800); // > 4%
                expect($count)->toBeLessThan(1_800); // < 9%
            }
        });

        test('output length is always exactly double the byte count', function (): void {
            // Arrange & Act & Assert
            $byteCounts = [1, 2, 4, 8, 16, 32, 64, 100];

            foreach ($byteCounts as $bytes) {
                $generator = new RandomBytesGenerator(bytes: $bytes);
                $id = $generator->generate();
                expect(mb_strlen($id))->toBe($bytes * 2);
            }
        });

        test('zero bytes throws exception from random_bytes', function (): void {
            // Arrange
            $generator = new RandomBytesGenerator(bytes: 0);

            // Act & Assert - PHP 8.0+ random_bytes() throws ValueError for length <= 0
            expect(fn (): string => $generator->generate())
                ->toThrow(ValueError::class);
        });

        test('negative bytes throws exception from random_bytes', function (): void {
            // Arrange
            $generator = new RandomBytesGenerator(bytes: -1);

            // Act & Assert - PHP's random_bytes() throws ValueError for negative length
            expect(fn (): string => $generator->generate())
                ->toThrow(ValueError::class);
        });
    });

    describe('Regressions', function (): void {
        test('generated ids always meet exact length requirement', function (): void {
            // Arrange - Regression: ensure length is always exact (bytes * 2)
            $byteCounts = [1, 4, 8, 16, 32, 64];

            foreach ($byteCounts as $bytes) {
                $generator = new RandomBytesGenerator(bytes: $bytes);

                // Act & Assert
                for ($i = 0; $i < 10; ++$i) {
                    $id = $generator->generate();
                    expect(mb_strlen($id))->toBe($bytes * 2);
                }
            }
        });

        test('generated ids contain only lowercase hexadecimal', function (): void {
            // Arrange - Regression: bin2hex always produces lowercase
            $generator = new RandomBytesGenerator();

            // Act & Assert
            for ($i = 0; $i < 100; ++$i) {
                $id = $generator->generate();
                expect($id)->toMatch('/^[0-9a-f]+$/');
                expect($id)->not->toMatch('/[A-F]/'); // No uppercase
                expect($id)->not->toMatch('/[^0-9a-f]/'); // No other chars
            }
        });

        test('generated ids are always unique within generator instance', function (): void {
            // Arrange - Regression: ensure uniqueness across rapid generation
            $generator = new RandomBytesGenerator();
            $ids = [];

            // Act
            for ($i = 0; $i < 10_000; ++$i) {
                $ids[] = $generator->generate();
            }

            // Assert - All should be unique
            expect(count(array_unique($ids)))->toBe(10_000);
        });

        test('bytes parameter actually controls output length', function (): void {
            // Arrange - Regression: ensure bytes parameter is not ignored
            $sizes = [1, 2, 4, 8, 16, 32, 64, 100];

            foreach ($sizes as $bytes) {
                $generator = new RandomBytesGenerator(bytes: $bytes);

                // Act
                $id = $generator->generate();

                // Assert
                expect(mb_strlen($id))->toBe($bytes * 2);
            }
        });

        test('cryptographic randomness produces high entropy ids', function (): void {
            // Arrange - Regression: ensure IDs have high entropy
            $generator = new RandomBytesGenerator(bytes: 16);
            $ids = [];

            // Act
            for ($i = 0; $i < 1_000; ++$i) {
                $ids[] = $generator->generate();
            }

            // Assert - All unique
            expect(count(array_unique($ids)))->toBe(1_000);

            // Check that IDs don't have repeating patterns
            foreach ($ids as $id) {
                if (mb_strlen($id) <= 2) {
                    continue;
                }

                // ID should not be all same character
                $chars = array_unique(mb_str_split($id));
                expect(count($chars))->toBeGreaterThan(1);
            }
        });

        test('uniform byte distribution prevents bias', function (): void {
            // Arrange - Regression: verify uniform distribution
            $generator = new RandomBytesGenerator(bytes: 200);
            $charCounts = array_fill_keys(mb_str_split('0123456789abcdef'), 0);

            // Act - Generate large sample
            for ($i = 0; $i < 100; ++$i) {
                $id = $generator->generate();

                foreach (mb_str_split($id) as $char) {
                    ++$charCounts[$char];
                }
            }

            // Assert - Each char should appear ~2500 times (6.25% of 40000)
            // Allow 20% variance for statistical randomness
            $expected = 2_500;

            foreach ($charCounts as $count) {
                expect($count)->toBeGreaterThan((int) ($expected * 0.8));
                expect($count)->toBeLessThan((int) ($expected * 1.2));
            }
        });

        test('hexadecimal encoding is always lowercase', function (): void {
            // Arrange - Regression: bin2hex() produces lowercase
            $generator = new RandomBytesGenerator(bytes: 32);

            // Act & Assert
            for ($i = 0; $i < 50; ++$i) {
                $id = $generator->generate();
                expect($id)->toBe(mb_strtolower($id));
                expect($id)->not->toMatch('/[A-Z]/');
            }
        });

        test('output contains all possible hex digits over many generations', function (): void {
            // Arrange - Regression: ensure no hex digits are missing
            $generator = new RandomBytesGenerator(bytes: 32);
            $allChars = [];

            // Act
            for ($i = 0; $i < 100; ++$i) {
                $id = $generator->generate();

                foreach (mb_str_split($id) as $char) {
                    $allChars[$char] = true;
                }
            }

            // Assert - All 16 hex chars should appear
            $uniqueChars = array_keys($allChars);
            sort($uniqueChars);
            expect(count($uniqueChars))->toBe(16);
        });
    });
});
