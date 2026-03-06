<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Regex;

use Closure;
use Countable;
use IteratorAggregate;
use Traversable;

use function count;
use function iterator_to_array;

/**
 * @author Brian Faust <brian@cline.sh>
 * @implements IteratorAggregate<int, MatchResult>
 */
final class MatchResults implements Countable, IteratorAggregate
{
    /**
     * @param Closure(): iterable<int, MatchResult> $resolver
     * @param array<int, MatchResult>               $resolvedMatches
     */
    private function __construct(
        private readonly Closure $resolver,
        private array $resolvedMatches = [],
        private bool $resolved = false,
    ) {}

    /**
     * @param Closure(): iterable<int, MatchResult> $resolver
     */
    public static function lazy(Closure $resolver): self
    {
        return new self($resolver);
    }

    public function resolved(): bool
    {
        return $this->resolved;
    }

    public function matched(): bool
    {
        return $this->first() instanceof MatchResult;
    }

    public function count(): int
    {
        return count($this->all());
    }

    public function first(): ?MatchResult
    {
        return $this->all()[0] ?? null;
    }

    /**
     * @return array<int, MatchResult>
     */
    public function all(): array
    {
        if (!$this->resolved) {
            $this->resolvedMatches = iterator_to_array(($this->resolver)());
            $this->resolved = true;
        }

        return $this->resolvedMatches;
    }

    /**
     * @return Traversable<int, MatchResult>
     */
    public function getIterator(): Traversable
    {
        yield from $this->all();
    }
}
