<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Default ID Generator
    |--------------------------------------------------------------------------
    |
    | This option controls the default ID generator that will be used when
    | creating new strongly-typed identifiers throughout your application.
    | The generator determines the format and characteristics of generated
    | IDs, such as sortability, collision resistance, and readability.
    |
    | Supported: GeneratorType enum values (uuid_v1, uuid_v3, uuid_v4,
    |            uuid_v5, uuid_v6, uuid_v7, uuid_v8, ulid, sqids,
    |            hashids, nanoid, base58, guid, random_string, random_bytes, prefixed)
    |
    | You may also specify a fully-qualified class name that implements the
    | Cline\StronglyTypedId\Contracts\IdGeneratorInterface interface if you wish to
    | use a custom ID generation strategy for your application's needs.
    |
    */

    'generator' => env('STRONGLY_TYPED_ID_GENERATOR', 'uuid_v7'),
    /*
    |--------------------------------------------------------------------------
    | Generator Configurations
    |--------------------------------------------------------------------------
    |
    | Here you may configure specific options for each supported generator.
    | These settings allow you to fine-tune the behaviour of each generator
    | to match your application's requirements. Not all generators require
    | additional configuration, but the options are available if needed.
    |
    */

    'generators' => [
        'uuid_v1' => [
            /*
            | UUID v1 generates time-based UUIDs using MAC address and timestamp.
            | Contains hardware info which may raise privacy concerns. Consider
            | v6 or v7 for time-ordered IDs without hardware identifiers.
            */
        ],
        'uuid_v3' => [
            /*
            | UUID v3 generates name-based UUIDs using MD5 hashing. Deterministic
            | - same namespace and name always produce the same UUID. Use v5 for
            | better security (SHA-1 instead of MD5).
            */
        ],
        'uuid_v4' => [
            /*
            | UUID v4 generates completely random UUIDs. While these provide
            | excellent collision resistance, they lack natural ordering,
            | which may impact database index performance at scale.
            */
        ],
        'uuid_v5' => [
            /*
            | UUID v5 generates name-based UUIDs using SHA-1 hashing. Deterministic
            | - same namespace and name always produce the same UUID. Preferred
            | over v3 due to stronger hashing algorithm.
            */
        ],
        'uuid_v6' => [
            /*
            | UUID v6 generates time-ordered UUIDs (reordered v1 for better DB
            | indexing). Similar to v7 but maintains v1 compatibility whilst
            | improving database performance through timestamp ordering.
            */
        ],
        'uuid_v7' => [
            /*
            | UUID v7 generates time-ordered UUIDs based on a Unix timestamp.
            | These are ideal for database primary keys as they maintain
            | chronological ordering whilst providing global uniqueness.
            */
        ],
        'uuid_v8' => [
            /*
            | UUID v8 provides custom/experimental UUID format. Uses random bytes
            | by default. For production use, consider v4 (random) or v7 (time-ordered)
            | unless you have specific custom UUID requirements.
            */
        ],
        'ulid' => [
            /*
            | ULID (Universally Unique Lexicographically Sortable Identifier)
            | combines timestamp-based ordering with randomness. ULIDs are
            | case-insensitive and use a larger alphabet than UUIDs.
            */
        ],
        'sqids' => [
            /*
            | Sqids generate short, URL-safe identifiers by encoding numeric
            | values. Provides configurable alphabet and minimum length while
            | maintaining uniqueness and human-friendly readability.
            */
        ],
        'hashids' => [
            /*
            | Hashids generate short, unique, URL-safe IDs using configurable
            | salt and alphabet. Encodes numeric IDs into decodable strings,
            | ideal for obfuscating database IDs.
            */
        ],
        'nanoid' => [
            /*
            | NanoID generates cryptographically secure, URL-friendly unique
            | identifiers. By default creates 21-char IDs with same collision
            | probability as UUID v4. More compact than UUIDs.
            */
        ],
        'base58' => [
            /*
            | Base58 generates human-readable identifiers using an alphabet that
            | excludes visually ambiguous characters (0, O, I, l). Commonly used
            | in Bitcoin and systems requiring readable IDs. Cryptographically secure.
            */
        ],
        'guid' => [
            /*
            | GUID (Globally Unique Identifier) is Microsoft's implementation
            | of UUID v4, formatted in uppercase. Functionally identical to
            | UUIDv4 but uses uppercase for Windows/.NET compatibility.
            */
        ],
        'random_string' => [
            /*
            | Random String generates cryptographically secure alphanumeric
            | identifiers using Laravel's Str::random(). Produces a-z, A-Z,
            | 0-9 characters. Ideal for API tokens and session IDs.
            */
        ],
        'random_bytes' => [
            /*
            | Random Bytes generates cryptographically secure hexadecimal
            | identifiers using PHP's random_bytes(). Produces 0-9, a-f chars.
            | Ideal for security tokens and encryption keys.
            */
        ],
        'prefixed' => [
            /*
            | Prefixed IDs generate Stripe-style identifiers with a prefix and
            | underlying generator. Configure the default prefix and which generator
            | to use for the ID portion after the underscore.
            |
            | Default prefix: 'id' (customize per entity type in your models)
            | Default generator: 'random_string' with length 24 (Stripe-style)
            |
            | Supported generators: uuid_v7, nanoid, random_string, random_bytes
            |
            | Example output with defaults: "id_aB3dEf9Hi2kLmN5pQ7rStUv1"
            */
            'prefix' => env('STRONGLY_TYPED_ID_PREFIX', 'id'),
            'generator' => env('STRONGLY_TYPED_ID_PREFIXED_GENERATOR', 'random_string'),
        ],
    ],
    /*
    |--------------------------------------------------------------------------
    | Custom Generators
    |--------------------------------------------------------------------------
    |
    | If you have registered custom ID generators in your application, you
    | may specify their configuration options here. Each custom generator
    | should be keyed by its alias, with any required options as values.
    |
    */

    'custom' => [
        // 'my-generator' => [
        //     'option' => 'value',
        // ],
    ],
];

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ //
// Here endeth thy configuration, noble developer!                            //
// Beyond: code so wretched, even wyrms learned the scribing arts.            //
// Forsooth, they but penned "// TODO: remedy ere long"                       //
// Three realms have fallen since...                                          //
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ //
//                                                  .~))>>                    //
//                                                 .~)>>                      //
//                                               .~))))>>>                    //
//                                             .~))>>             ___         //
//                                           .~))>>)))>>      .-~))>>         //
//                                         .~)))))>>       .-~))>>)>          //
//                                       .~)))>>))))>>  .-~)>>)>              //
//                   )                 .~))>>))))>>  .-~)))))>>)>             //
//                ( )@@*)             //)>))))))  .-~))))>>)>                 //
//              ).@(@@               //))>>))) .-~))>>)))))>>)>               //
//            (( @.@).              //))))) .-~)>>)))))>>)>                   //
//          ))  )@@*.@@ )          //)>))) //))))))>>))))>>)>                 //
//       ((  ((@@@.@@             |/))))) //)))))>>)))>>)>                    //
//      )) @@*. )@@ )   (\_(\-\b  |))>)) //)))>>)))))))>>)>                   //
//    (( @@@(.@(@ .    _/`-`  ~|b |>))) //)>>)))))))>>)>                      //
//     )* @@@ )@*     (@)  (@) /\b|))) //))))))>>))))>>                       //
//   (( @. )@( @ .   _/  /    /  \b)) //))>>)))))>>>_._                       //
//    )@@ (@@*)@@.  (6///6)- / ^  \b)//))))))>>)))>>   ~~-.                   //
// ( @jgs@@. @@@.*@_ VvvvvV//  ^  \b/)>>))))>>      _.     `bb                //
//  ((@@ @@@*.(@@ . - | o |' \ (  ^   \b)))>>        .'       b`,             //
//   ((@@).*@@ )@ )   \^^^/  ((   ^  ~)_        \  /           b `,           //
//     (@@. (@@ ).     `-'   (((   ^    `\ \ \ \ \|             b  `.         //
//       (*.@*              / ((((        \| | |  \       .       b `.        //
//                         / / (((((  \    \ /  _.-~\     Y,      b  ;        //
//                        / / / (((((( \    \.-~   _.`" _.-~`,    b  ;        //
//                       /   /   `(((((()    )    (((((~      `,  b  ;        //
//                     _/  _/      `"""/   /'                  ; b   ;        //
//                 _.-~_.-~           /  /'                _.'~bb _.'         //
//               ((((~~              / /'              _.'~bb.--~             //
//                                  ((((          __.-~bb.-~                  //
//                                              .'  b .~~                     //
//                                              :bb ,'                        //
//                                              ~~~~                          //
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ //
