<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\StronglyTypedId;

use Cline\StronglyTypedId\Contracts\IdGeneratorInterface;
use Cline\StronglyTypedId\Enums\GeneratorType;
use Cline\StronglyTypedId\Exceptions\InvalidGeneratorException;
use Cline\StronglyTypedId\Generators\Base58Generator;
use Cline\StronglyTypedId\Generators\GuidGenerator;
use Cline\StronglyTypedId\Generators\HashidsGenerator;
use Cline\StronglyTypedId\Generators\NanoIdGenerator;
use Cline\StronglyTypedId\Generators\PrefixedIdGenerator;
use Cline\StronglyTypedId\Generators\RandomBytesGenerator;
use Cline\StronglyTypedId\Generators\RandomStringGenerator;
use Cline\StronglyTypedId\Generators\SqidsGenerator;
use Cline\StronglyTypedId\Generators\UlidGenerator;
use Cline\StronglyTypedId\Generators\UuidV1Generator;
use Cline\StronglyTypedId\Generators\UuidV3Generator;
use Cline\StronglyTypedId\Generators\UuidV4Generator;
use Cline\StronglyTypedId\Generators\UuidV5Generator;
use Cline\StronglyTypedId\Generators\UuidV6Generator;
use Cline\StronglyTypedId\Generators\UuidV7Generator;
use Cline\StronglyTypedId\Generators\UuidV8Generator;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

use function config;
use function is_string;
use function throw_if;

/**
 * Laravel Service Provider for the Strongly Typed ID Package.
 *
 * This service provider registers and configures the strongly typed ID system,
 * including configuration publishing and ID generator binding based on user preferences.
 *
 * The provider binds the configured ID generator to the service container, allowing
 * automatic dependency injection of the appropriate generator throughout the application.
 *
 * @author Brian Faust <brian@cline.sh>
 * @see PackageServiceProvider
 */
final class StronglyTypedIdServiceProvider extends PackageServiceProvider
{
    /**
     * Configure the package settings.
     *
     * Registers the package name and makes the configuration file available
     * for publishing via `php artisan vendor:publish`.
     *
     * @param Package $package The package configuration instance
     */
    public function configurePackage(Package $package): void
    {
        $package
            ->name('strongly-typed-id')
            ->hasConfigFile();
    }

    /**
     * Register package services after the package has been registered.
     *
     * Binds the ID generator interface to a concrete implementation based on
     * the 'strongly-typed-id.generator' configuration value. This allows the
     * application to use dependency injection to access the configured generator.
     *
     * Supported generators:
     * - uuid_v1: Time-based UUID
     * - uuid_v3: Name-based UUID (MD5)
     * - uuid_v4: Random UUID
     * - uuid_v5: Name-based UUID (SHA-1)
     * - uuid_v6: Reordered time-based UUID
     * - uuid_v7: Time-ordered UUID (recommended for databases)
     * - uuid_v8: Custom UUID
     * - ulid: Universally Unique Lexicographically Sortable Identifier
     * - sqids: Short URL-safe identifiers
     * - hashids: Hash-based obfuscated identifiers
     * - nanoid: Compact URL-friendly unique identifiers
     * - base58: Human-readable identifiers (excludes 0, O, I, l)
     * - guid: Microsoft GUID (uppercase UUID v4)
     * - random_string: Alphanumeric strings via Laravel Str::random()
     * - random_bytes: Hexadecimal strings via PHP random_bytes()
     * - prefixed: Stripe-style prefixed IDs (configurable prefix and generator)
     *
     * @throws InvalidGeneratorException If the configured generator name is not supported
     */
    public function packageRegistered(): void
    {
        $this->app->bind(function ($app): IdGeneratorInterface {
            $generatorValue = config('strongly-typed-id.generator');

            throw_if(!is_string($generatorValue) || $generatorValue === '', InvalidGeneratorException::invalidConfigValue($generatorValue));

            $generator = GeneratorType::tryFrom($generatorValue);

            throw_if($generator === null, InvalidGeneratorException::unsupportedType($generatorValue));

            return match ($generator) {
                GeneratorType::UuidV1 => new UuidV1Generator(),
                GeneratorType::UuidV3 => new UuidV3Generator(),
                GeneratorType::UuidV4 => new UuidV4Generator(),
                GeneratorType::UuidV5 => new UuidV5Generator(),
                GeneratorType::UuidV6 => new UuidV6Generator(),
                GeneratorType::UuidV7 => new UuidV7Generator(),
                GeneratorType::UuidV8 => new UuidV8Generator(),
                GeneratorType::Ulid => new UlidGenerator(),
                GeneratorType::Sqids => new SqidsGenerator(),
                GeneratorType::Hashids => new HashidsGenerator(),
                GeneratorType::NanoId => new NanoIdGenerator(),
                GeneratorType::Base58 => new Base58Generator(),
                GeneratorType::Guid => new GuidGenerator(),
                GeneratorType::RandomString => new RandomStringGenerator(),
                GeneratorType::RandomBytes => new RandomBytesGenerator(),
                GeneratorType::Prefixed => (function (): PrefixedIdGenerator {
                    $prefix = config('strongly-typed-id.generators.prefixed.prefix', 'id');
                    $generatorType = config('strongly-typed-id.generators.prefixed.generator', 'random_string');

                    return new PrefixedIdGenerator(
                        prefix: is_string($prefix) ? $prefix : 'id',
                        generator: match ($generatorType) {
                            'uuid_v7' => new UuidV7Generator(),
                            'nanoid' => new NanoIdGenerator(),
                            'random_bytes' => new RandomBytesGenerator(16),
                            'random_string' => new RandomStringGenerator(24),
                            default => new RandomStringGenerator(24),
                        },
                    );
                })(),
            };
        });
    }
}
