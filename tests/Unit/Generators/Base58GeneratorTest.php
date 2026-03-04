<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\StronglyTypedId\Generators\Base58Generator;

describe('Base58Generator', function (): void {
    describe('Happy Paths', function (): void {
        test('generates default 21-character id', function (): void {
            // Arrange
            $generator = new Base58Generator();

            // Act
            $id = $generator->generate();

            // Assert
            expect($id)->toBeString();
            expect(mb_strlen($id))->toBe(21);
        });

        test('generates id with base58 characters only', function (): void {
            // Arrange
            $generator = new Base58Generator();

            // Act
            $id = $generator->generate();

            // Assert
            // Base58: 123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz
            // Excludes: 0 (zero), O (capital o), I (capital i), l (lowercase L)
            expect($id)->toMatch('/^[123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz]+$/');
        });

        test('generates id without ambiguous characters', function (): void {
            // Arrange
            $generator = new Base58Generator();

            // Act
            for ($i = 0; $i < 100; ++$i) {
                $id = $generator->generate();

                // Assert - Should never contain 0, O, I, or l
                expect($id)->not->toContain('0');
                expect($id)->not->toContain('O');
                expect($id)->not->toContain('I');
                expect($id)->not->toContain('l');
            }
        });

        test('generates unique ids on each call', function (): void {
            // Arrange
            $generator = new Base58Generator();

            // Act
            $id1 = $generator->generate();
            $id2 = $generator->generate();

            // Assert
            expect($id1)->not->toBe($id2);
        });

        test('generates id with custom size of 5 characters', function (): void {
            // Arrange
            $generator = new Base58Generator(size: 5);

            // Act
            $id = $generator->generate();

            // Assert
            expect(mb_strlen($id))->toBe(5);
        });

        test('generates id with custom size of 10 characters', function (): void {
            // Arrange
            $generator = new Base58Generator(size: 10);

            // Act
            $id = $generator->generate();

            // Assert
            expect(mb_strlen($id))->toBe(10);
        });

        test('generates id with custom size of 50 characters', function (): void {
            // Arrange
            $generator = new Base58Generator(size: 50);

            // Act
            $id = $generator->generate();

            // Assert
            expect(mb_strlen($id))->toBe(50);
        });

        test('generated ids are url-safe', function (): void {
            // Arrange
            $generator = new Base58Generator();

            // Act
            $id = $generator->generate();

            // Assert
            expect($id)->not->toContain(' ');
            expect($id)->not->toContain('/');
            expect($id)->not->toContain('?');
            expect($id)->not->toContain('&');
            expect($id)->not->toContain('=');
            expect($id)->not->toContain('+');
        });
    });

    describe('Sad Paths', function (): void {
        // Base58Generator has no validation - accepts all constructor parameters
        // PHP type system enforces int for size
        // No business rule validations to test in sad paths
    });

    describe('Edge Cases', function (): void {
        test('generates id with minimum size of 1 character', function (): void {
            // Arrange
            $generator = new Base58Generator(size: 1);

            // Act
            $id = $generator->generate();

            // Assert
            expect(mb_strlen($id))->toBe(1);
            expect($id)->toMatch('/^[123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz]$/');
        });

        test('generates id with large size of 100 characters', function (): void {
            // Arrange
            $generator = new Base58Generator(size: 100);

            // Act
            $id = $generator->generate();

            // Assert
            expect(mb_strlen($id))->toBe(100);
            expect($id)->toMatch('/^[123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz]+$/');
        });

        test('generates id with large size of 250 characters', function (): void {
            // Arrange
            $generator = new Base58Generator(size: 250);

            // Act
            $id = $generator->generate();

            // Assert
            expect(mb_strlen($id))->toBe(250);
            expect($id)->toMatch('/^[123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz]+$/');
        });

        test('all base58 alphabet characters appear in generated ids through multiple generations', function (): void {
            // Arrange
            $alphabet = '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz';
            $generator = new Base58Generator(size: 100);
            $allChars = [];

            // Act - Generate multiple IDs to ensure all characters appear
            for ($i = 0; $i < 200; ++$i) {
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
            $generator = new Base58Generator();
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
            $generator = new Base58Generator(size: 21);

            // Act & Assert
            for ($i = 0; $i < 100; ++$i) {
                $id = $generator->generate();
                expect(mb_strlen($id))->toBe(21);
            }
        });

        test('base58 characters are uniformly distributed', function (): void {
            // Arrange
            $generator = new Base58Generator(size: 100);
            $charCounts = [];

            // Act - Generate IDs and count character occurrences
            for ($i = 0; $i < 100; ++$i) {
                $id = $generator->generate();

                foreach (mb_str_split($id) as $char) {
                    if (!isset($charCounts[$char])) {
                        $charCounts[$char] = 0;
                    }

                    ++$charCounts[$char];
                }
            }

            // Assert - Each character should appear roughly equally
            // With 100 IDs of length 100 (10000 total chars) and 58-char alphabet
            // Expected per char: ~172 (10000/58)
            // Allow generous variance for statistical randomness
            foreach ($charCounts as $count) {
                expect($count)->toBeGreaterThan(50);
                expect($count)->toBeLessThan(350);
            }
        });

        test('different generators with same config produce different ids', function (): void {
            // Arrange
            $generator1 = new Base58Generator(size: 21);
            $generator2 = new Base58Generator(size: 21);

            // Act
            $id1 = $generator1->generate();
            $id2 = $generator2->generate();

            // Assert - Different instances produce different IDs
            expect($id1)->not->toBe($id2);
        });

        test('generates consistently with size 2', function (): void {
            // Arrange
            $generator = new Base58Generator(size: 2);

            // Act
            $id = $generator->generate();

            // Assert
            expect(mb_strlen($id))->toBe(2);
        });

        test('generates consistently with size 3', function (): void {
            // Arrange
            $generator = new Base58Generator(size: 3);

            // Act
            $id = $generator->generate();

            // Assert
            expect(mb_strlen($id))->toBe(3);
        });

        test('handles zero size by returning empty string immediately', function (): void {
            // Arrange
            $generator = new Base58Generator(size: 0);

            // Act
            $id = $generator->generate();

            // Assert
            expect($id)->toBe('');
            expect(mb_strlen($id))->toBe(0);
        });

        test('handles negative size by treating it as boundary condition', function (): void {
            // Arrange
            $generator = new Base58Generator(size: -1);

            // Act
            $id = $generator->generate();

            // Assert
            expect($id)->toBe('');
            expect(mb_strlen($id))->toBe(0);
        });
    });

    describe('Regressions', function (): void {
        test('generated ids always meet exact size requirement', function (): void {
            // Arrange
            $size = 21;
            $generator = new Base58Generator(size: $size);

            // Act & Assert
            for ($i = 0; $i < 50; ++$i) {
                $id = $generator->generate();
                expect(mb_strlen($id))->toBe($size);
            }
        });

        test('generated ids contain only base58 characters', function (): void {
            // Arrange
            $generator = new Base58Generator();

            // Act & Assert
            for ($i = 0; $i < 50; ++$i) {
                $id = $generator->generate();
                expect($id)->toMatch('/^[123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz]+$/');
                // Verify ambiguous characters are excluded
                expect($id)->not->toContain('0');
                expect($id)->not->toContain('O');
                expect($id)->not->toContain('I');
                expect($id)->not->toContain('l');
            }
        });

        test('generated ids are always unique within generator instance', function (): void {
            // Arrange
            $generator = new Base58Generator();
            $ids = [];

            // Act
            for ($i = 0; $i < 10_000; ++$i) {
                $ids[] = $generator->generate();
            }

            // Assert
            expect(count(array_unique($ids)))->toBe(10_000);
        });

        test('size parameter actually controls id length', function (): void {
            // Arrange
            $sizes = [5, 10, 15, 21, 30, 50, 100];

            foreach ($sizes as $size) {
                $generator = new Base58Generator(size: $size);

                // Act
                $id = $generator->generate();

                // Assert
                expect(mb_strlen($id))->toBe($size);
            }
        });

        test('base58 alphabet produces human-readable ids consistently', function (): void {
            // Arrange
            $generator = new Base58Generator();

            // Act & Assert
            for ($i = 0; $i < 100; ++$i) {
                $id = $generator->generate();
                // Ensure no ambiguous characters
                expect($id)->not->toContain('0');
                expect($id)->not->toContain('O');
                expect($id)->not->toContain('I');
                expect($id)->not->toContain('l');
                // Ensure URL-safe
                expect($id)->not->toContain(' ');
                expect($id)->not->toContain('/');
                expect($id)->not->toContain('?');
            }
        });

        test('cryptographic randomness produces high entropy ids', function (): void {
            // Arrange
            $generator = new Base58Generator();
            $ids = [];

            // Act
            for ($i = 0; $i < 1_000; ++$i) {
                $ids[] = $generator->generate();
            }

            // Assert
            $uniqueIds = array_unique($ids);
            expect(count($uniqueIds))->toBe(1_000);

            // Check that IDs don't have sequential patterns
            foreach ($ids as $id) {
                if (mb_strlen($id) <= 1) {
                    continue;
                }

                $chars = array_unique(mb_str_split($id));
                expect(count($chars))->toBeGreaterThan(1);
            }
        });

        test('uniform distribution prevents modulo bias', function (): void {
            // Arrange
            $generator = new Base58Generator(size: 1_000);
            $charCounts = [];

            // Act - Generate large IDs to get statistically significant sample
            for ($i = 0; $i < 10; ++$i) {
                $id = $generator->generate();

                foreach (mb_str_split($id) as $char) {
                    if (!isset($charCounts[$char])) {
                        $charCounts[$char] = 0;
                    }

                    ++$charCounts[$char];
                }
            }

            // Assert - Each character should appear roughly 172 times (10000/58)
            // Allow 30% variance for statistical randomness
            $expected = 172;

            foreach ($charCounts as $count) {
                expect($count)->toBeGreaterThan((int) ($expected * 0.7));
                expect($count)->toBeLessThan((int) ($expected * 1.3));
            }
        });
    });
});
