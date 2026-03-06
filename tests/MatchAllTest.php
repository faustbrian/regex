<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Regex\Exceptions\RegexMatchFailed;
use Cline\Regex\Regex;

it('can determine if a match all operation found matches', function (): void {
    expect(Regex::matchAll('/a/', 'aaa')->hasMatch())->toBeTrue()
        ->and(Regex::matchAll('/b/', 'aaa')->hasMatch())->toBeFalse();
});

it('can retrieve the matched results', function (): void {
    $results = Regex::matchAll('/a/', 'aaa')->results();

    expect($results)->toHaveCount(3)
        ->and($results[0]->result())->toBe('a')
        ->and($results[1]->result())->toBe('a')
        ->and($results[2]->result())->toBe('a');
});

it('returns an empty array if a subject does not match', function (): void {
    expect(Regex::matchAll('/abc/', 'def')->results())->toBe([]);
});

it('throws an exception if a match all throws an error', function (): void {
    $expected = RegexMatchFailed::forPatternAndSubject(
        '/abc',
        'abc',
        "preg_match_all(): No ending delimiter '/' found",
    )->getMessage();

    expect(fn (): mixed => Regex::matchAll('/abc', 'abc'))
        ->toThrow(RegexMatchFailed::class, $expected);
});

it('throws an exception if a match all throws a preg error', function (): void {
    $expected = RegexMatchFailed::forPatternAndSubject(
        '/(?:\D+|<\d+>)*[!?]/',
        'foobar foobar foobar',
        'Backtrack limit exhausted',
    )->getMessage();

    expect(
        fn (): mixed => Regex::matchAll('/(?:\D+|<\d+>)*[!?]/', 'foobar foobar foobar'),
    )->toThrow(RegexMatchFailed::class, $expected);
});

it('can retrieve groups from the matched results', function (): void {
    $results = Regex::matchAll('/a(b)/', 'abab')->results();

    expect($results)->toHaveCount(2)
        ->and($results[0]->result())->toBe('ab')
        ->and($results[0]->group(1))->toBe('b')
        ->and($results[1]->result())->toBe('ab')
        ->and($results[1]->group(1))->toBe('b');
});

it('can include unmatched subpatterns as null across match all results', function (): void {
    $results = Regex::matchAll('/(?<A>a)|(?<B>b)/', 'a')->results()[0]->groups();

    expect($results)->toHaveCount(5)
        ->and($results['B'])->toBeNull()
        ->and($results[2])->toBeNull();
});

it('can match multiple named groups', function (): void {
    $results = Regex::matchAll(
        '/the sky is (?<color>.+)/',
        <<<'TEXT'
the sky is blue
foo bar
the sky is green
the sky is red
bar baz
the sky is white
TEXT,
    )->results();

    expect($results)->toHaveCount(4)
        ->and($results[0]->result())->toBe('the sky is blue')
        ->and($results[0]->group('color'))->toBe('blue')
        ->and($results[0]->group(1))->toBe('blue')
        ->and($results[1]->result())->toBe('the sky is green')
        ->and($results[1]->group('color'))->toBe('green')
        ->and($results[1]->group(1))->toBe('green')
        ->and($results[2]->result())->toBe('the sky is red')
        ->and($results[2]->group('color'))->toBe('red')
        ->and($results[2]->group(1))->toBe('red')
        ->and($results[3]->result())->toBe('the sky is white')
        ->and($results[3]->group('color'))->toBe('white')
        ->and($results[3]->group(1))->toBe('white');
});
