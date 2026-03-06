<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Regex\Exceptions\RegexReplaceFailed;
use Cline\Regex\MatchResult;
use Cline\Regex\Regex;

it('can replace a pattern with a string', function (): void {
    expect(Regex::replace('/a/', 'b', 'aabb')->result())->toBe('bbbb');
});

it('throws an exception on an invalid pattern', function (): void {
    $expected = RegexReplaceFailed::forPatternAndSubject(
        '/a',
        'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',
        "preg_replace(): No ending delimiter '/' found",
    )->getMessage();

    expect(
        fn (): mixed => Regex::replace('/a', 'b', 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa')->result(),
    )->toThrow(
        RegexReplaceFailed::class,
        $expected,
    );
});

it('can replace patterns with a callback', function (): void {
    $result = Regex::replace(
        '/a(b)/',
        fn (MatchResult $match): string => $match->result().$match->result(),
        'abc',
    )->result();

    expect($result)->toBe('ababc');
});

it('can use an existing function name as a replacement string', function (): void {
    expect(Regex::replace('/a/', '_', 'abab')->result())->toBe('_b_b');
});

it('can replace an array of patterns with a replacement', function (): void {
    expect(Regex::replace(['/a/', '/b/'], 'c', 'aabb')->result())->toBe('cccc');
});

it('can replace an array of patterns with an array', function (): void {
    expect(Regex::replace(['/a/', '/b/'], ['c', 'd'], 'aabb')->result())->toBe('ccdd');
});

it('can limit the amount of replacements', function (): void {
    expect(Regex::replace('/a/', 'b', 'aabb', 1)->result())->toBe('babb');
});

it('counts the amount of replacements', function (): void {
    expect(Regex::replace('/a/', 'b', 'aabb')->count())->toBe(2);
});
