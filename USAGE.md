<!--
Copyright (C) Brian Faust

For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.
-->

# Usage

## Basic Matching

```php
use Cline\Regex\Pattern;

$pattern = new Pattern('/(?<name>Brian)/');
$match = $pattern->match('Brian Faust');

$match->matched(); // true
$match->value(); // 'Brian'
$match->offset(); // 0
$match->capture('name'); // 'Brian'
$match->captureOffset('name'); // 0
```

## Fluent Construction

```php
use Cline\Regex\Pattern;

$match = Pattern::of('/(?<name>Brian)/')->match('Brian Faust');
```

## Global Matching

```php
use Cline\Regex\Pattern;

$results = Pattern::of('/Brian/')->matchAll('Brian and Brian');

foreach ($results as $result) {
    $result->value(); // 'Brian'
    $result->offset(); // 0, then 10
}
```

`MatchResults` resolves lazily and caches the results when consumed.

## Replacement

```php
use Cline\Regex\Pattern;

$replacement = Pattern::of('/Brian/')->replaceWith('B.', 'Brian Faust');

$replacement->pattern(); // '/Brian/'
$replacement->originalSubject(); // 'Brian Faust'
$replacement->subject(); // 'B. Faust'
$replacement->replacements(); // 1
```

## Callback Replacement

```php
use Cline\Regex\MatchResult;
use Cline\Regex\Pattern;

$result = Pattern::of('/a(b)/')->replaceUsing(
    fn (MatchResult $match): string => $match->value().':'.$match->offset(),
    'abc',
);

$result->subject(); // 'ab:0c'
```
