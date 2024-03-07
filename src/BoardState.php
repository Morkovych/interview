<?php

abstract class BoardState
{
    protected Board $board;

    public function setContext(Board $board): void
    {
        $this->board = $board;
    }

    abstract public function getState(): bool;

    abstract public function error($xFrom, $yFrom): void;

    abstract public function switch(): void;
}