<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Regex\Exceptions;

use Exception;

/**
 * Base exception type for regex failures raised by this package.
 *
 * @author Brian Faust <brian@cline.sh>
 */
abstract class RegexFailed extends Exception implements RegexException {}
