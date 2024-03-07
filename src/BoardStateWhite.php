<?php

class BoardStateWhite extends BoardState
{
    public function getState(): bool
    {
        return true;
    }

    /**
     * @throws Exception
     */
    public function error($xFrom, $yFrom): void
    {
        throw new Exception("$xFrom$yFrom error - " . 'white must walk.');
    }

    public function switch(): void
    {
        $this->board->transitionTo(new BoardStateBlack());
    }
}