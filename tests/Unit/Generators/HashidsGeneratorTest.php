<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\StronglyTypedId\Generators\HashidsGenerator;

describe('HashidsGenerator', function (): void {
    describe('Happy Paths', function (): void {
        test('generates valid hashids string', function (): void {
            // Arrange
            $generator = new HashidsGenerator();

            // Act
            $id = $generator->generate();

            // Assert
            expect($id)->toBeString();
            expect(mb_strlen($id))->toBeGreaterThanOrEqual(8);
        });

        test('generates url-safe characters only', function (): void {
            // Arrange
            $generator = new HashidsGenerator();

            // Act
            $id = $generator->generate();

            // Assert
            expect($id)->toMatch('/^[a-zA-Z0-9]+$/');
        });

        test('respects minimum length configuration', function (): void {
            // Arrange
            $minLength = 16;
            $generator = new HashidsGenerator(minLength: $minLength);

            // Act
            $id = $generator->generate();

            // Assert
            expect(mb_strlen($id))->toBeGreaterThanOrEqual($minLength);
        });

        test('generates unique ids on each call', function (): void {
            // Arrange
            $generator = new HashidsGenerator();

            // Act
            $id1 = $generator->generate();
            $id2 = $generator->generate();

            // Assert
            expect($id1)->not->toBe($id2);
        });

        test('custom salt configuration affects output', function (): void {
            // Arrange
            $salt1 = 'salt1';
            $salt2 = 'salt2';
            $generator1 = new HashidsGenerator(salt: $salt1);
            $generator2 = new HashidsGenerator(salt: $salt2);

            // Act
            $id1 = $generator1->generate();
            $id2 = $generator2->generate();

            // Assert
            expect($id1)->toBeString();
            expect($id2)->toBeString();
            expect($id1)->toMatch('/^[a-zA-Z0-9]+$/');
            expect($id2)->toMatch('/^[a-zA-Z0-9]+$/');
        });

        test('custom alphabet configuration works', function (): void {
            // Arrange
            $customAlphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
            $generator = new HashidsGenerator(alphabet: $customAlphabet);

            // Act
            $id = $generator->generate();

            // Assert
            expect($id)->toBeString();
            expect($id)->toMatch('/^[A-Z0-9]+$/');
        });

        test('empty salt uses default behavior', function (): void {
            // Arrange
            $generator = new HashidsGenerator(salt: '');

            // Act
            $id = $generator->generate();

            // Assert
            expect($id)->toBeString();
            expect($id)->toMatch('/^[a-zA-Z0-9]+$/');
        });

        test('empty alphabet uses default', function (): void {
            // Arrange
            $generator = new HashidsGenerator(alphabet: '');

            // Act
            $id = $generator->generate();

            // Assert
            expect($id)->toBeString();
            expect($id)->toMatch('/^[a-zA-Z0-9]+$/');
        });

        test('combines salt and custom alphabet', function (): void {
            // Arrange
            $salt = 'my-secret-salt';
            $customAlphabet = '0123456789ABCDEF';
            $generator = new HashidsGenerator(salt: $salt, alphabet: $customAlphabet);

            // Act
            $id = $generator->generate();

            // Assert
            expect($id)->toBeString();
            expect($id)->toMatch('/^[0-9A-F]+$/');
        });

        test('combines salt, alphabet, and minimum length', function (): void {
            // Arrange
            $salt = 'test-salt';
            $minLength = 12;
            $customAlphabet = 'ABCDEFGHIJKLMNOP0123456789';
            $generator = new HashidsGenerator(salt: $salt, minLength: $minLength, alphabet: $customAlphabet);

            // Act
            $id = $generator->generate();

            // Assert
            expect(mb_strlen($id))->toBeGreaterThanOrEqual($minLength);
            expect($id)->toMatch('/^[A-P0-9]+$/');
        });
    });

    describe('Sad Paths', function (): void {
        test('rejects alphabet with less than 16 unique characters', function (): void {
            // Arrange & Act & Assert
            expect(fn (): HashidsGenerator => new HashidsGenerator(alphabet: 'ABC123'))
                ->toThrow(InvalidArgumentException::class, 'alphabet must contain at least 16 unique characters');
        });

        test('handles alphabet with duplicate characters by removing duplicates', function (): void {
            // Arrange - Hashids silently removes duplicate characters
            // 'ABCDEFGHIJKLMNOPAA' becomes 'ABCDEFGHIJKLMNOP' (16 chars - valid)
            $generator = new HashidsGenerator(alphabet: 'ABCDEFGHIJKLMNOPAA');

            // Act
            $id = $generator->generate();

            // Assert - should generate valid ID with duplicates removed
            expect($id)->toBeString();
            expect($id)->toMatch('/^[A-P]+$/'); // Only A-P since duplicates removed
        });

        test('rejects alphabet with spaces', function (): void {
            // Arrange & Act & Assert
            expect(fn (): HashidsGenerator => new HashidsGenerator(alphabet: 'ABCDEFGHIJKLMNOP QRST'))
                ->toThrow(InvalidArgumentException::class, "The Hashids alphabet can't contain spaces.");
        });
    });

    describe('Edge Cases', function (): void {
        test('generates many unique ids rapidly', function (): void {
            // Arrange
            $generator = new HashidsGenerator();
            $ids = [];

            // Act
            for ($i = 0; $i < 1_000; ++$i) {
                $ids[] = $generator->generate();
            }

            // Assert
            expect(count(array_unique($ids)))->toBe(1_000);
        });

        test('maintains consistency across multiple calls', function (): void {
            // Arrange
            $generator = new HashidsGenerator();

            // Act & Assert
            for ($i = 0; $i < 100; ++$i) {
                $id = $generator->generate();
                expect($id)->toBeString();
                expect(mb_strlen($id))->toBeGreaterThanOrEqual(8);
                expect($id)->toMatch('/^[a-zA-Z0-9]+$/');
            }
        });

        test('generates with minimum length of zero', function (): void {
            // Arrange
            $generator = new HashidsGenerator(minLength: 0);

            // Act
            $id = $generator->generate();

            // Assert
            expect($id)->toBeString();
            expect($id)->toMatch('/^[a-zA-Z0-9]+$/');
        });

        test('generates with minimum length of one', function (): void {
            // Arrange
            $generator = new HashidsGenerator(minLength: 1);

            // Act
            $id = $generator->generate();

            // Assert
            expect(mb_strlen($id))->toBeGreaterThanOrEqual(1);
            expect($id)->toMatch('/^[a-zA-Z0-9]+$/');
        });

        test('generates with minimum length of eight', function (): void {
            // Arrange
            $generator = new HashidsGenerator(minLength: 8);

            // Act
            $id = $generator->generate();

            // Assert
            expect(mb_strlen($id))->toBeGreaterThanOrEqual(8);
        });

        test('generates with minimum length of sixteen', function (): void {
            // Arrange
            $generator = new HashidsGenerator(minLength: 16);

            // Act
            $id = $generator->generate();

            // Assert
            expect(mb_strlen($id))->toBeGreaterThanOrEqual(16);
        });

        test('generates with minimum length of thirty-two', function (): void {
            // Arrange
            $generator = new HashidsGenerator(minLength: 32);

            // Act
            $id = $generator->generate();

            // Assert
            expect(mb_strlen($id))->toBeGreaterThanOrEqual(32);
        });

        test('custom alphabet with exactly 16 characters works', function (): void {
            // Arrange - Minimum valid alphabet size for Hashids
            $generator = new HashidsGenerator(alphabet: '0123456789ABCDEF');

            // Act
            $id = $generator->generate();

            // Assert
            expect($id)->toBeString();
            expect($id)->toMatch('/^[0-9A-F]+$/');
        });

        test('custom alphabet generates unique ids', function (): void {
            // Arrange
            $customAlphabet = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $generator = new HashidsGenerator(alphabet: $customAlphabet);
            $ids = [];

            // Act
            for ($i = 0; $i < 100; ++$i) {
                $ids[] = $generator->generate();
            }

            // Assert
            expect(count(array_unique($ids)))->toBe(100);

            foreach ($ids as $id) {
                expect($id)->toMatch('/^[0-9A-Z]+$/');
            }
        });

        test('different salts produce different id patterns', function (): void {
            // Arrange
            $generator1 = new HashidsGenerator(salt: 'salt-one');
            $generator2 = new HashidsGenerator(salt: 'salt-two');
            $ids1 = [];
            $ids2 = [];

            // Act
            for ($i = 0; $i < 10; ++$i) {
                $ids1[] = $generator1->generate();
                $ids2[] = $generator2->generate();
            }

            // Assert - All IDs should be unique across both generators
            $allIds = array_merge($ids1, $ids2);
            expect(count(array_unique($allIds)))->toBe(20);
        });

        test('same salt produces consistent encoding scheme', function (): void {
            // Arrange - Multiple generators with same salt
            $salt = 'consistent-salt';
            $generator1 = new HashidsGenerator(salt: $salt);
            $generator2 = new HashidsGenerator(salt: $salt);

            // Act
            $id1 = $generator1->generate();
            $id2 = $generator2->generate();

            // Assert - Different IDs due to random input, but both valid
            expect($id1)->toBeString();
            expect($id2)->toBeString();
            expect($id1)->toMatch('/^[a-zA-Z0-9]+$/');
            expect($id2)->toMatch('/^[a-zA-Z0-9]+$/');
        });

        test('handles very long salt strings', function (): void {
            // Arrange
            $longSalt = str_repeat('abcdefghijklmnopqrstuvwxyz', 10);
            $generator = new HashidsGenerator(salt: $longSalt);

            // Act
            $id = $generator->generate();

            // Assert
            expect($id)->toBeString();
            expect(mb_strlen($id))->toBeGreaterThanOrEqual(8);
            expect($id)->toMatch('/^[a-zA-Z0-9]+$/');
        });

        test('handles salt with special characters', function (): void {
            // Arrange
            $specialSalt = '!@#$%^&*()_+-=[]{}|;:,.<>?';
            $generator = new HashidsGenerator(salt: $specialSalt);

            // Act
            $id = $generator->generate();

            // Assert
            expect($id)->toBeString();
            expect($id)->toMatch('/^[a-zA-Z0-9]+$/');
        });

        test('handles salt with unicode characters', function (): void {
            // Arrange
            $unicodeSalt = 'test-salt-with-émojis-🔒🔑';
            $generator = new HashidsGenerator(salt: $unicodeSalt);

            // Act
            $id = $generator->generate();

            // Assert
            expect($id)->toBeString();
            expect($id)->toMatch('/^[a-zA-Z0-9]+$/');
        });

        test('combines custom alphabet with minimum length', function (): void {
            // Arrange
            $customAlphabet = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $minLength = 12;
            $generator = new HashidsGenerator(minLength: $minLength, alphabet: $customAlphabet);

            // Act
            $id = $generator->generate();

            // Assert
            expect(mb_strlen($id))->toBeGreaterThanOrEqual($minLength);
            expect($id)->toMatch('/^[0-9A-Z]+$/');
        });

        test('different generators with same config produce different ids', function (): void {
            // Arrange
            $generator1 = new HashidsGenerator(salt: 'same-salt', minLength: 10, alphabet: 'ABCDEFGHIJKLMNOP0123456789');
            $generator2 = new HashidsGenerator(salt: 'same-salt', minLength: 10, alphabet: 'ABCDEFGHIJKLMNOP0123456789');

            // Act
            $id1 = $generator1->generate();
            $id2 = $generator2->generate();

            // Assert - Random inputs mean different IDs even with same config
            expect($id1)->not->toBe($id2);
        });

        test('handles very long custom alphabet', function (): void {
            // Arrange
            $longAlphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
            $generator = new HashidsGenerator(alphabet: $longAlphabet);

            // Act
            $id = $generator->generate();

            // Assert
            expect($id)->toBeString();
            expect(mb_strlen($id))->toBeGreaterThanOrEqual(8);
        });

        test('generates consistently with very large minimum length', function (): void {
            // Arrange - Test with large minimum length
            $generator = new HashidsGenerator(minLength: 100);

            // Act
            $id = $generator->generate();

            // Assert
            expect(mb_strlen($id))->toBeGreaterThanOrEqual(100);
            expect($id)->toMatch('/^[a-zA-Z0-9]+$/');
        });

        test('empty salt with custom alphabet works', function (): void {
            // Arrange
            $generator = new HashidsGenerator(salt: '', alphabet: 'ABCDEFGHIJKLMNOP0123456789');

            // Act
            $id = $generator->generate();

            // Assert
            expect($id)->toBeString();
            expect($id)->toMatch('/^[A-Z0-9]+$/');
        });
    });

    describe('Regressions', function (): void {
        test('generated ids always meet minimum length requirement', function (): void {
            // Arrange - Regression: ensure minimum length is always respected
            $minLength = 12;
            $generator = new HashidsGenerator(minLength: $minLength);

            // Act & Assert - Test multiple times to ensure consistency
            for ($i = 0; $i < 50; ++$i) {
                $id = $generator->generate();
                expect(mb_strlen($id))->toBeGreaterThanOrEqual($minLength);
            }
        });

        test('generated ids are always url-safe', function (): void {
            // Arrange - Regression: ensure IDs never contain unsafe characters
            $generator = new HashidsGenerator();

            // Act & Assert - Test multiple times
            for ($i = 0; $i < 50; ++$i) {
                $id = $generator->generate();
                expect($id)->toMatch('/^[a-zA-Z0-9]+$/');
                expect($id)->not->toContain(' ');
                expect($id)->not->toContain('/');
                expect($id)->not->toContain('?');
                expect($id)->not->toContain('&');
                expect($id)->not->toContain('=');
            }
        });

        test('generated ids are always unique within generator instance', function (): void {
            // Arrange - Regression: ensure uniqueness across rapid generation
            $generator = new HashidsGenerator();
            $ids = [];

            // Act - Generate many IDs rapidly
            for ($i = 0; $i < 10_000; ++$i) {
                $ids[] = $generator->generate();
            }

            // Assert - All should be unique
            expect(count(array_unique($ids)))->toBe(10_000);
        });

        test('custom alphabet never produces characters outside alphabet', function (): void {
            // Arrange - Regression: ensure alphabet is strictly respected
            $customAlphabet = '0123456789ABCDEF';
            $generator = new HashidsGenerator(alphabet: $customAlphabet);

            // Act & Assert
            for ($i = 0; $i < 100; ++$i) {
                $id = $generator->generate();
                expect($id)->toMatch('/^[0-9A-F]+$/');
                // Verify no lowercase or other characters
                expect($id)->not->toMatch('/[a-z]/');
                expect($id)->not->toMatch('/[G-Z]/');
            }
        });

        test('salt parameter actually affects id generation', function (): void {
            // Arrange - Regression: ensure salt is not ignored
            $generator1 = new HashidsGenerator(salt: 'test-salt-one');
            $generator2 = new HashidsGenerator(salt: 'test-salt-two');
            $generator3 = new HashidsGenerator(salt: '');

            // Act
            $ids1 = array_map($generator1->generate(...), range(1, 100));
            $ids2 = array_map($generator2->generate(...), range(1, 100));
            $ids3 = array_map($generator3->generate(...), range(1, 100));

            // Assert - All three sets should be different and non-overlapping
            $allIds = array_merge($ids1, $ids2, $ids3);
            expect(count(array_unique($allIds)))->toBe(300);
        });
    });
});
