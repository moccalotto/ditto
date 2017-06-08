<?php

use Moccalotto\Ditto\Ditto;

require 'vendor/autoload.php';

$ditto = Ditto::createFor('exception', 'message', 'status', 'success', 'code')
    ->withStatus('ok')
    ->withMessage('some kind of message')
    ->withCode('g-31')
    ->withException(new LogicException('Spoung'))
    ->withContent([
        'some' => 'data',
        'foo' => [
            'bar' => 'baz',
        ],
    ], '.');

if ($ditto->hasStatus()) {
    print 'Success!!' . PHP_EOL;
}

if ($ditto->code() === 'g-31') {
    print 'Correct code!!' . PHP_EOL;
}

try {
    if ($ditto->hasException()) {
        throw $ditto->exception();
    }
} catch (Exception $l) {
    print 'Correct exception' . PHP_EOL;
}

print $ditto->getOr('foo/bar', 'fallback value') . PHP_EOL;
print $ditto->getOr('foo/bing', 'fallback value') . PHP_EOL;
