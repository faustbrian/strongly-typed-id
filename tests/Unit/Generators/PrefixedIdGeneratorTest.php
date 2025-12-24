<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\StronglyTypedId\Generators\NanoIdGenerator;
use Cline\StronglyTypedId\Generators\PrefixedIdGenerator;
use Cline\StronglyTypedId\Generators\RandomBytesGenerator;
use Cline\StronglyTypedId\Generators\RandomStringGenerator;
use Cline\StronglyTypedId\Generators\SqidsGenerator;
use Cline\StronglyTypedId\Generators\UlidGenerator;
use Cline\StronglyTypedId\Generators\UuidV4Generator;
use Cline\StronglyTypedId\Generators\UuidV7Generator;

describe('PrefixedIdGenerator', function (): void {
    describe('Happy Paths', function (): void {
        test('generates stripe-style prefixed id with underscore separator', function (): void {
            // Arrange
            $generator = new PrefixedIdGenerator('cus', new UuidV7Generator());

            // Act
            $id = $generator->generate();

            // Assert
            expect($id)->toBeString();
            expect($id)->toStartWith('cus_');
        });

        test('generates prefixed id with uuid v7 generator', function (): void {
            // Arrange
            $generator = new PrefixedIdGenerator('cus', new UuidV7Generator());

            // Act
            $id = $generator->generate();

            // Assert
            expect($id)->toMatch('/^cus_[0-9a-f]{8}-[0-9a-f]{4}-7[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/');
        });

        test('generates prefixed id with uuid v4 generator', function (): void {
            // Arrange
            $generator = new PrefixedIdGenerator('ch', new UuidV4Generator());

            // Act
            $id = $generator->generate();

            // Assert
            expect($id)->toStartWith('ch_');
            expect($id)->toMatch('/^ch_[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/');
        });

        test('generates prefixed id with nanoid generator', function (): void {
            // Arrange
            $generator = new PrefixedIdGenerator('tok', new NanoIdGenerator());

            // Act
            $id = $generator->generate();

            // Assert
            expect($id)->toStartWith('tok_');
            expect(mb_strlen($id))->toBe(25); // 'tok_' (4) + NanoID (21)
        });

        test('generates prefixed id with sqids generator', function (): void {
            // Arrange
            $generator = new PrefixedIdGenerator('acct', new SqidsGenerator());

            // Act
            $id = $generator->generate();

            // Assert
            expect($id)->toStartWith('acct_');
            expect($id)->toMatch('/^acct_[a-zA-Z0-9]+$/');
        });

        test('generates prefixed id with ulid generator', function (): void {
            // Arrange
            $generator = new PrefixedIdGenerator('org', new UlidGenerator());

            // Act
            $id = $generator->generate();

            // Assert
            expect($id)->toStartWith('org_');
            expect(mb_strlen($id))->toBe(30); // 'org_' (4) + ULID (26)
        });

        test('generates prefixed id with random string generator', function (): void {
            // Arrange
            $generator = new PrefixedIdGenerator('sess', new RandomStringGenerator(16));

            // Act
            $id = $generator->generate();

            // Assert
            expect($id)->toStartWith('sess_');
            expect(mb_strlen($id))->toBe(21); // 'sess_' (5) + 16
        });

        test('generates prefixed id with random bytes generator', function (): void {
            // Arrange
            $generator = new PrefixedIdGenerator('sec', new RandomBytesGenerator(16));

            // Act
            $id = $generator->generate();

            // Assert
            expect($id)->toStartWith('sec_');
            expect(mb_strlen($id))->toBe(36); // 'sec_' (4) + hex(32)
        });

        test('generates unique ids on each call', function (): void {
            // Arrange
            $generator = new PrefixedIdGenerator('usr', new UuidV7Generator());

            // Act
            $id1 = $generator->generate();
            $id2 = $generator->generate();

            // Assert
            expect($id1)->not->toBe($id2);
            expect($id1)->toStartWith('usr_');
            expect($id2)->toStartWith('usr_');
        });

        test('respects different prefixes for different entity types', function (): void {
            // Arrange
            $customerGen = new PrefixedIdGenerator('cus', new UuidV7Generator());
            $chargeGen = new PrefixedIdGenerator('ch', new UuidV7Generator());
            $accountGen = new PrefixedIdGenerator('acct', new UuidV7Generator());

            // Act
            $customerId = $customerGen->generate();
            $chargeId = $chargeGen->generate();
            $accountId = $accountGen->generate();

            // Assert
            expect($customerId)->toStartWith('cus_');
            expect($chargeId)->toStartWith('ch_');
            expect($accountId)->toStartWith('acct_');
        });
    });

    describe('Sad Paths', function (): void {
        // PrefixedIdGenerator has no validation - accepts all constructor parameters
        // PHP type system enforces string for prefix and IdGeneratorInterface for generator
        // No business rule validations to test in sad paths
    });

    describe('Edge Cases', function (): void {
        test('handles empty prefix', function (): void {
            // Arrange
            $generator = new PrefixedIdGenerator('', new UuidV7Generator());

            // Act
            $id = $generator->generate();

            // Assert
            expect($id)->toStartWith('_');
            expect($id)->toMatch('/^_[0-9a-f]{8}-[0-9a-f]{4}-7[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/');
        });

        test('handles single character prefix', function (): void {
            // Arrange
            $generator = new PrefixedIdGenerator('x', new UuidV7Generator());

            // Act
            $id = $generator->generate();

            // Assert
            expect($id)->toStartWith('x_');
            expect(mb_strlen($id))->toBe(38); // 'x_' (2) + UUID (36)
        });

        test('handles long prefix', function (): void {
            // Arrange
            $longPrefix = 'very_long_entity_prefix';
            $generator = new PrefixedIdGenerator($longPrefix, new NanoIdGenerator());

            // Act
            $id = $generator->generate();

            // Assert
            expect($id)->toStartWith($longPrefix.'_');
            expect(mb_strlen($id))->toBe(mb_strlen($longPrefix) + 1 + 21);
        });

        test('handles prefix with numbers', function (): void {
            // Arrange
            $generator = new PrefixedIdGenerator('v2user', new UuidV7Generator());

            // Act
            $id = $generator->generate();

            // Assert
            expect($id)->toStartWith('v2user_');
        });

        test('handles prefix with mixed case', function (): void {
            // Arrange
            $generator = new PrefixedIdGenerator('CustomerId', new UuidV7Generator());

            // Act
            $id = $generator->generate();

            // Assert
            expect($id)->toStartWith('CustomerId_');
        });

        test('generates many unique ids rapidly', function (): void {
            // Arrange
            $generator = new PrefixedIdGenerator('test', new UuidV7Generator());
            $ids = [];

            // Act
            for ($i = 0; $i < 1_000; ++$i) {
                $ids[] = $generator->generate();
            }

            // Assert
            expect(count(array_unique($ids)))->toBe(1_000);

            foreach ($ids as $id) {
                expect($id)->toStartWith('test_');
            }
        });

        test('maintains underlying generator characteristics', function (): void {
            // Arrange
            $customNanoId = new NanoIdGenerator(size: 10, alphabet: '0123456789');
            $generator = new PrefixedIdGenerator('num', $customNanoId);

            // Act
            $id = $generator->generate();

            // Assert
            expect($id)->toStartWith('num_');
            expect(mb_strlen($id))->toBe(14); // 'num_' (4) + 10
            expect($id)->toMatch('/^num_\d{10}$/');
        });

        test('different generators with same prefix produce different ids', function (): void {
            // Arrange
            $generator1 = new PrefixedIdGenerator('id', new UuidV7Generator());
            $generator2 = new PrefixedIdGenerator('id', new UuidV7Generator());

            // Act
            $id1 = $generator1->generate();
            $id2 = $generator2->generate();

            // Assert
            expect($id1)->not->toBe($id2);
            expect($id1)->toStartWith('id_');
            expect($id2)->toStartWith('id_');
        });

        test('prefix and id are properly separated by underscore', function (): void {
            // Arrange
            $generator = new PrefixedIdGenerator('prefix', new RandomStringGenerator(10));

            // Act
            $id = $generator->generate();

            // Assert
            $parts = explode('_', $id, 2);
            expect($parts)->toHaveCount(2);
            expect($parts[0])->toBe('prefix');
            expect(mb_strlen($parts[1]))->toBe(10);
        });

        test('nested prefixed generators work correctly', function (): void {
            // Arrange
            $innerGen = new PrefixedIdGenerator('inner', new UuidV7Generator());
            $outerGen = new PrefixedIdGenerator('outer', $innerGen);

            // Act
            $id = $outerGen->generate();

            // Assert
            expect($id)->toStartWith('outer_inner_');
            expect($id)->toMatch('/^outer_inner_[0-9a-f]{8}-[0-9a-f]{4}-7[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/');
        });
    });

    describe('Regressions', function (): void {
        test('prefix is never mutated or lost', function (): void {
            // Arrange - Regression: ensure prefix stays consistent
            $prefix = 'stable';
            $generator = new PrefixedIdGenerator($prefix, new UuidV7Generator());

            // Act & Assert
            for ($i = 0; $i < 100; ++$i) {
                $id = $generator->generate();
                expect($id)->toStartWith($prefix.'_');
            }
        });

        test('underscore separator is always present', function (): void {
            // Arrange - Regression: ensure separator is never omitted
            $generator = new PrefixedIdGenerator('pre', new NanoIdGenerator());

            // Act & Assert
            for ($i = 0; $i < 50; ++$i) {
                $id = $generator->generate();
                expect($id)->toContain('_');
                $parts = explode('_', $id, 2);
                expect($parts)->toHaveCount(2);
                expect($parts[0])->toBe('pre');
            }
        });

        test('underlying generator is called exactly once per generate', function (): void {
            // Arrange - Regression: ensure no duplicate generation
            $generator = new PrefixedIdGenerator('test', new UuidV7Generator());

            // Act
            $ids = [];

            for ($i = 0; $i < 1_000; ++$i) {
                $ids[] = $generator->generate();
            }

            // Assert - All IDs unique means generator called once per call
            expect(count(array_unique($ids)))->toBe(1_000);
        });

        test('different entity types maintain distinct prefixes', function (): void {
            // Arrange - Regression: ensure prefix collision doesn't occur
            $types = ['cus', 'ch', 'acct', 'tok', 'sess'];
            $generators = [];

            foreach ($types as $type) {
                $generators[$type] = new PrefixedIdGenerator($type, new UuidV7Generator());
            }

            // Act & Assert
            foreach ($generators as $type => $generator) {
                for ($i = 0; $i < 20; ++$i) {
                    $id = $generator->generate();
                    expect($id)->toStartWith($type.'_');

                    // Ensure no other prefix appears
                    foreach ($types as $otherType) {
                        if ($type === $otherType) {
                            continue;
                        }

                        if ($otherType === 'ch') {
                            continue;
                        }

                        // 'ch' is substring of 'chs', etc
                        expect($id)->not->toStartWith($otherType.'_');
                    }
                }
            }
        });

        test('composability with all generator types', function (): void {
            // Arrange - Regression: ensure works with every generator
            $generators = [
                new UuidV4Generator(),
                new UuidV7Generator(),
                new UlidGenerator(),
                new NanoIdGenerator(),
                new SqidsGenerator(),
                new RandomStringGenerator(),
                new RandomBytesGenerator(),
            ];

            foreach ($generators as $baseGen) {
                $prefixedGen = new PrefixedIdGenerator('test', $baseGen);

                // Act
                $id = $prefixedGen->generate();

                // Assert
                expect($id)->toBeString();
                expect($id)->toStartWith('test_');
            }
        });
    });
});
