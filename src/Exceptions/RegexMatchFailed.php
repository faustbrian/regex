<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Regex\Exceptions;

use Throwable;

/**
 * @author Brian Faust <brian@cline.sh>
 */
final class RegexMatchFailed extends RegexFailed
{
    public static function fromPregError(
        string $pattern,
        string $subject,
        string $operation,
        int $pregErrorCode,
        string $pregMessage,
    ): self {
        return new self($pattern, $subject, $operation, $pregMessage, $pregErrorCode);
    }

    public static function fromThrowable(
        string $pattern,
        string $subject,
        string $operation,
        Throwable $throwable,
    ): self {
        return new self(
            $pattern,
            $subject,
            $operation,
            $throwable->getMessage(),
            previous: $throwable,
        );
    }
}
