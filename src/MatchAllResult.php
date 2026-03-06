<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Regex;

use Cline\Regex\Exceptions\RegexMatchFailed;
use Cline\Regex\Helpers\Arr;
use Exception;

use const PREG_UNMATCHED_AS_NULL;

use function preg_match_all;

/**
 * Value object describing the outcome of a `preg_match_all` operation.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class MatchAllResult extends RegexResult
{
    /**
     * @param array<int|string, array<int, null|string>> $matches Match matrix keyed by group.
     */
    public function __construct(
        private readonly string $pattern,
        private readonly string $subject,
        private readonly bool $hasMatch,
        private readonly array $matches,
    ) {}

    /**
     * Run the regex and wrap the native global-match result.
     */
    public static function for(string $pattern, string $subject): self
    {
        $matches = [];

        try {
            $result = preg_match_all($pattern, $subject, $matches, PREG_UNMATCHED_AS_NULL);
        } catch (Exception $exception) {
            throw RegexMatchFailed::forPatternAndSubject($pattern, $subject, $exception->getMessage());
        }

        if ($result === false) {
            throw RegexMatchFailed::forPatternAndSubject($pattern, $subject, self::lastPregError());
        }

        return new self($pattern, $subject, $result > 0, $matches);
    }

    /**
     * Determine whether the pattern matched the subject at least once.
     */
    public function hasMatch(): bool
    {
        return $this->hasMatch;
    }

    /**
     * Convert the native match matrix into per-match result objects.
     *
     * @return array<int, MatchResult>
     */
    public function results(): array
    {
        return Arr::map(
            Arr::transpose($this->matches),
            fn (array $match): MatchResult => new MatchResult($this->pattern, $this->subject, true, $match),
        );
    }
}
