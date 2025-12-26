<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\StronglyTypedId\Generators\SqidsGenerator;

describe('SqidsGenerator', function (): void {
    describe('Happy Paths', function (): void {
        test('generates valid sqid string', function (): void {
            // Arrange
            $generator = new SqidsGenerator();

            // Act
            $id = $generator->generate();

            // Assert
            expect($id)->toBeString();
            expect(mb_strlen($id))->toBeGreaterThanOrEqual(8);
        });

        test('generates url-safe characters only', function (): void {
            // Arrange
            $generator = new SqidsGenerator();

            // Act
            $id = $generator->generate();

            // Assert
            expect($id)->toMatch('/^[a-zA-Z0-9]+$/');
        });

        test('respects minimum length configuration', function (): void {
            // Arrange
            $minLength = 16;
            $generator = new SqidsGenerator(minLength: $minLength);

            // Act
            $id = $generator->generate();

            // Assert
            expect(mb_strlen($id))->toBeGreaterThanOrEqual($minLength);
        });

        test('generates unique ids on each call', function (): void {
            // Arrange
            $generator = new SqidsGenerator();

            // Act
            $id1 = $generator->generate();
            $id2 = $generator->generate();

            // Assert
            expect($id1)->not->toBe($id2);
        });

        test('custom alphabet configuration works', function (): void {
            // Arrange
            $customAlphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
            $generator = new SqidsGenerator(alphabet: $customAlphabet);

            // Act
            $id = $generator->generate();

            // Assert
            expect($id)->toBeString();
            expect($id)->toMatch('/^[A-Z0-9]+$/');
        });

        test('empty alphabet uses default', function (): void {
            // Arrange
            $generator = new SqidsGenerator(alphabet: '');

            // Act
            $id = $generator->generate();

            // Assert
            expect($id)->toBeString();
            expect($id)->toMatch('/^[a-zA-Z0-9]+$/');
        });
    });

    describe('Sad Paths', function (): void {
        test('rejects alphabet with less than 3 characters', function (): void {
            // Arrange & Act & Assert
            expect(fn (): SqidsGenerator => new SqidsGenerator(alphabet: 'AB'))
                ->toThrow(InvalidArgumentException::class, 'Alphabet length must be at least 3');
        });

        test('rejects alphabet with duplicate characters', function (): void {
            // Arrange & Act & Assert
            expect(fn (): SqidsGenerator => new SqidsGenerator(alphabet: 'ABCA'))
                ->toThrow(InvalidArgumentException::class, 'Alphabet must contain unique characters');
        });

        test('rejects alphabet with multibyte characters', function (): void {
            // Arrange & Act & Assert
            expect(fn (): SqidsGenerator => new SqidsGenerator(alphabet: 'ABC€DEF'))
                ->toThrow(InvalidArgumentException::class, 'Alphabet cannot contain multibyte characters');
        });

        test('rejects negative minimum length', function (): void {
            // Arrange & Act & Assert
            expect(fn (): SqidsGenerator => new SqidsGenerator(minLength: -1))
                ->toThrow(InvalidArgumentException::class, 'Minimum length has to be between 0 and');
        });

        test('rejects minimum length exceeding limit', function (): void {
            // Arrange & Act & Assert
            expect(fn (): SqidsGenerator => new SqidsGenerator(minLength: 256))
                ->toThrow(InvalidArgumentException::class, 'Minimum length has to be between 0 and');
        });
    });

    describe('Edge Cases', function (): void {
        test('generates many unique ids rapidly', function (): void {
            // Arrange
            $generator = new SqidsGenerator();
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
            $generator = new SqidsGenerator();

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
            $generator = new SqidsGenerator(minLength: 0);

            // Act
            $id = $generator->generate();

            // Assert
            expect($id)->toBeString();
            expect($id)->toMatch('/^[a-zA-Z0-9]+$/');
        });

        test('generates with minimum length of one', function (): void {
            // Arrange
            $generator = new SqidsGenerator(minLength: 1);

            // Act
            $id = $generator->generate();

            // Assert
            expect(mb_strlen($id))->toBeGreaterThanOrEqual(1);
            expect($id)->toMatch('/^[a-zA-Z0-9]+$/');
        });

        test('generates with minimum length of eight', function (): void {
            // Arrange
            $generator = new SqidsGenerator(minLength: 8);

            // Act
            $id = $generator->generate();

            // Assert
            expect(mb_strlen($id))->toBeGreaterThanOrEqual(8);
        });

        test('generates with minimum length of sixteen', function (): void {
            // Arrange
            $generator = new SqidsGenerator(minLength: 16);

            // Act
            $id = $generator->generate();

            // Assert
            expect(mb_strlen($id))->toBeGreaterThanOrEqual(16);
        });

        test('generates with minimum length of thirty-two', function (): void {
            // Arrange
            $generator = new SqidsGenerator(minLength: 32);

            // Act
            $id = $generator->generate();

            // Assert
            expect(mb_strlen($id))->toBeGreaterThanOrEqual(32);
        });

        test('custom alphabet with varying sizes', function (): void {
            // Arrange
            $smallAlphabet = 'ABC123';
            $generator = new SqidsGenerator(alphabet: $smallAlphabet);

            // Act
            $id = $generator->generate();

            // Assert
            expect($id)->toBeString();
            expect($id)->toMatch('/^[ABC123]+$/');
        });

        test('custom alphabet generates unique ids', function (): void {
            // Arrange
            $customAlphabet = '0123456789ABCDEF';
            $generator = new SqidsGenerator(alphabet: $customAlphabet);
            $ids = [];

            // Act
            for ($i = 0; $i < 100; ++$i) {
                $ids[] = $generator->generate();
            }

            // Assert
            expect(count(array_unique($ids)))->toBe(100);

            foreach ($ids as $id) {
                expect($id)->toMatch('/^[0-9A-F]+$/');
            }
        });

        test('combines custom alphabet with minimum length', function (): void {
            // Arrange
            $customAlphabet = 'XYZ123';
            $minLength = 12;
            $generator = new SqidsGenerator(alphabet: $customAlphabet, minLength: $minLength);

            // Act
            $id = $generator->generate();

            // Assert
            expect(mb_strlen($id))->toBeGreaterThanOrEqual($minLength);
            expect($id)->toMatch('/^[XYZ123]+$/');
        });

        test('alphabet with exactly 3 characters works', function (): void {
            // Arrange
            $generator = new SqidsGenerator(alphabet: 'ABC');

            // Act
            $id = $generator->generate();

            // Assert
            expect($id)->toBeString();
            expect($id)->toMatch('/^[ABC]+$/');
        });

        test('handles very long custom alphabet', function (): void {
            // Arrange
            $longAlphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_!@#$%^&*()+=[]{}|;:,.<>?';
            $generator = new SqidsGenerator(alphabet: $longAlphabet);

            // Act
            $id = $generator->generate();

            // Assert
            expect($id)->toBeString();
            expect(mb_strlen($id))->toBeGreaterThanOrEqual(8);
        });

        test('different generators with same config produce different ids', function (): void {
            // Arrange
            $generator1 = new SqidsGenerator(alphabet: 'ABCDEF0123456789', minLength: 10);
            $generator2 = new SqidsGenerator(alphabet: 'ABCDEF0123456789', minLength: 10);

            // Act
            $id1 = $generator1->generate();
            $id2 = $generator2->generate();

            // Assert - Random inputs mean different IDs even with same config
            expect($id1)->not->toBe($id2);
        });

        test('generates consistently with minimum length at boundary', function (): void {
            // Arrange - Test at exact minimum length boundary
            $generator = new SqidsGenerator(minLength: 255); // Just under the limit

            // Act
            $id = $generator->generate();

            // Assert
            expect(mb_strlen($id))->toBeGreaterThanOrEqual(255);
        });
    });
});
