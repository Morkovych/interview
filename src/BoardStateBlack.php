<?php

class BoardStateBlack extends BoardState
{
    public function getState(): bool
    {
        return false;
    }

    /**
     * @throws Exception
     */
    public function error($xFrom, $yFrom): void
    {
        throw new Exception("$xFrom$yFrom error - " . 'black must walk.');
    }

    /**
     * @throws Exception
     */
    public function switch(): void
    {
        $this->board->transitionTo(new BoardStateWhite());
    }
}