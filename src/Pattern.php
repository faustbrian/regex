<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Regex;

use Cline\Regex\Exceptions\RegexMatchFailed;
use Cline\Regex\Exceptions\RegexReplaceFailed;
use Throwable;

use const PREG_OFFSET_CAPTURE;
use const PREG_UNMATCHED_AS_NULL;

use function assert;
use function max;
use function mb_strlen;
use function preg_last_error;
use function preg_last_error_msg;
use function preg_match;
use function preg_replace;
use function preg_replace_callback;

/**
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
final readonly class Pattern
{
    public function __construct(
        private string $pattern,
    ) {}

    public static function of(string $pattern): self
    {
        return new self($pattern);
    }

    public function pattern(): string
    {
        return $this->pattern;
    }

    public function match(string $subject, int $offset = 0): MatchResult
    {
        $captures = [];

        try {
            $result = preg_match(
                $this->pattern,
                $subject,
                $captures,
                PREG_OFFSET_CAPTURE | PREG_UNMATCHED_AS_NULL,
                $offset,
            );
        } catch (Throwable $throwable) {
            throw RegexMatchFailed::fromThrowable(
                pattern: $this->pattern,
                subject: $subject,
                operation: 'match',
                throwable: $throwable,
            );
        }

        if ($result === false) {
            throw RegexMatchFailed::fromPregError(
                pattern: $this->pattern,
                subject: $subject,
                operation: 'match',
                pregErrorCode: preg_last_error(),
                pregMessage: preg_last_error_msg(),
            );
        }

        return MatchResult::fromPregMatch($this->pattern, $subject, $result === 1, $captures);
    }

    public function matchAll(string $subject, int $offset = 0): MatchResults
    {
        return MatchResults::lazy(function () use ($subject, $offset): iterable {
            $nextOffset = $offset;
            $subjectLength = mb_strlen($subject, '8bit');

            while (true) {
                $match = $this->match($subject, $nextOffset);

                if (!$match->matched()) {
                    return;
                }

                yield $match;

                $value = $match->value();
                $matchOffset = $match->offset();
                assert($value !== null && $matchOffset !== null);

                $nextOffset = $matchOffset + max(mb_strlen($value, '8bit'), 1);

                if ($nextOffset > $subjectLength) {
                    return;
                }
            }
        });
    }

    public function replaceWith(string $replacement, string $subject, int $limit = -1): ReplaceResult
    {
        $count = 0;

        try {
            $result = preg_replace($this->pattern, $replacement, $subject, $limit, $count);
        } catch (Throwable $throwable) {
            throw RegexReplaceFailed::fromThrowable(
                pattern: $this->pattern,
                subject: $subject,
                operation: 'replace',
                throwable: $throwable,
            );
        }

        if ($result === null) {
            throw RegexReplaceFailed::fromPregError(
                pattern: $this->pattern,
                subject: $subject,
                operation: 'replace',
                pregErrorCode: preg_last_error(),
                pregMessage: preg_last_error_msg(),
            );
        }

        return new ReplaceResult($this->pattern, $subject, $result, $count);
    }

    /**
     * @param callable(MatchResult): string $replacement
     */
    public function replaceUsing(callable $replacement, string $subject, int $limit = -1): ReplaceResult
    {
        $count = 0;

        try {
            $result = preg_replace_callback(
                $this->pattern,
                fn (array $captures): string => $replacement(
                    $this->matchResultFromCallback($subject, $captures),
                ),
                $subject,
                $limit,
                $count,
                PREG_OFFSET_CAPTURE | PREG_UNMATCHED_AS_NULL,
            );
        } catch (Throwable $throwable) {
            throw RegexReplaceFailed::fromThrowable(
                pattern: $this->pattern,
                subject: $subject,
                operation: 'replace',
                throwable: $throwable,
            );
        }

        if ($result === null) {
            throw RegexReplaceFailed::fromPregError(
                pattern: $this->pattern,
                subject: $subject,
                operation: 'replace',
                pregErrorCode: preg_last_error(),
                pregMessage: preg_last_error_msg(),
            );
        }

        return new ReplaceResult($this->pattern, $subject, $result, $count);
    }

    /**
     * @param array<int|string, mixed> $captures
     */
    private function matchResultFromCallback(string $subject, array $captures): MatchResult
    {
        /** @var array<int|string, array{0: null, 1: -1}|array{0: string, 1: int}> $captures */
        return MatchResult::fromPregMatch($this->pattern, $subject, true, $captures);
    }
}
