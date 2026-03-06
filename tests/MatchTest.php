<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Regex\Exceptions\RegexGroupDoesNotExist;
use Cline\Regex\Exceptions\RegexMatchFailed;
use Cline\Regex\Regex;

it('can determine if a match was made', function (): void {
    expect(Regex::match('/abc/', 'abc')->hasMatch())->toBeTrue()
        ->and(Regex::match('/abc/', 'def')->hasMatch())->toBeFalse();
});

it('throws an exception if a match throws an error', function (): void {
    $expected = RegexMatchFailed::forPatternAndSubject(
        '/abc',
        'abc',
        "preg_match(): No ending delimiter '/' found",
    )->getMessage();

    expect(fn (): mixed => Regex::match('/abc', 'abc'))
        ->toThrow(RegexMatchFailed::class, $expected);
});

it('throws an exception if a match throws a preg error', function (): void {
    $expected = RegexMatchFailed::forPatternAndSubject(
        '/(?:\D+|<\d+>)*[!?]/',
        'foobar foobar foobar',
        'Backtrack limit exhausted',
    )->getMessage();

    expect(
        fn (): mixed => Regex::match('/(?:\D+|<\d+>)*[!?]/', 'foobar foobar foobar'),
    )->toThrow(RegexMatchFailed::class, $expected);
});

it('can retrieve the matched result', function (): void {
    expect(Regex::match('/abc/', 'abcdef')->result())->toBe('abc');
});

it('returns null if a result is queried for a subject that did not match', function (): void {
    expect(Regex::match('/abc/', 'def')->result())->toBeNull();
});

it('can retrieve a matched group', function (): void {
    expect(Regex::match('/(a)bc/', 'abcdef')->group(1))->toBe('a');
});

it('throws an exception if a non existing group is queried', function (): void {
    $expected = RegexGroupDoesNotExist::forPatternSubjectAndGroup('/(a)bc/', 'abcdef', 2)->getMessage();

    expect(fn (): mixed => Regex::match('/(a)bc/', 'abcdef')->group(2))
        ->toThrow(RegexGroupDoesNotExist::class, $expected);
});

it('can retrieve a matched named group', function (): void {
    expect(Regex::match('/(?<samename>a)bc/', 'abcdef')->namedGroup('samename'))
        ->toBe('a');
});

it('can retrieve all matched groups', function (): void {
    $results = Regex::match('/(a)bc/', 'abcdef')->groups();

    expect($results)->toHaveCount(2)
        ->and($results[0])->toBe('abc')
        ->and($results[1])->toBe('a');
});

it('can include unmatched subpatterns as null', function (): void {
    $results = Regex::match('/(?<A>a)|(?<B>b)/', 'a')->groups();

    expect($results)->toHaveCount(5)
        ->and($results['B'])->toBeNull()
        ->and($results[2])->toBeNull();
});

it('throws an exception if a non existing named group is queried', function (): void {
    $expected = RegexGroupDoesNotExist::forPatternSubjectAndGroup(
        '/(?<samename>a)bc/',
        'abcdef',
        'invalidname',
    )->getMessage();

    expect(fn (): mixed => Regex::match('/(?<samename>a)bc/', 'abcdef')->namedGroup('invalidname'))
        ->toThrow(RegexGroupDoesNotExist::class, $expected);
});

it('returns matched value even if there is a default', function (): void {
    expect(Regex::match('/blue/', 'blue')->resultOr('black'))->toBe('blue');
});

it('returns default value if there is no match', function (): void {
    expect(Regex::match('/blue/', 'yellow')->resultOr('black'))->toBe('black');
});

it('returns matched group value even if there is a default', function (): void {
    expect(Regex::match('/the sky is (.+)/', 'the sky is orange')->groupOr(1, 'blue'))
        ->toBe('orange');
});

it('returns default value if there is no group', function (): void {
    expect(Regex::match('/the sky is (.+)/', 'abc')->groupOr(1, 'blue'))
        ->toBe('blue');
});
