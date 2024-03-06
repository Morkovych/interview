<?php

require_once __DIR__ . '/vendor/autoload.php';

try {
    $board = new Board();

    $args = $argv;
    array_shift($args);

    $color = [];

    foreach ($args as $key => $move) {
        $color[$key] = $board->move($move);
        if ($key === 0 && $color[$key] !== false) {
            throw new Exception('The sequence of moves is not followed.');
        }
        if ($key > 0 && $color[$key - 1] === $color[$key]) {
            throw new Exception('The sequence of moves is not followed.');
        }
    }

    $board->dump();
} catch (\Exception $e) {
    echo $e->getMessage() . "\n";
    exit(1);
}
