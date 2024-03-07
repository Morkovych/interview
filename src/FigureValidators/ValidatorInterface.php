<?php

namespace FigureValidators;

interface ValidatorInterface
{
    public function validate($xFrom, $yFrom, $xTo, $yTo, $figures): void;

    public function availabilityShapesCheck(string $xFrom, int $yFrom): bool;

    public function checkingColorFigure(string $xFrom, int $yFrom): bool;

    public function whereCanGo(string $xFrom, int $yFrom, bool $color): array;

    public function checkOtherFigures(array $whereCanGo): array;

    public function iAmHungry(int $yTo, bool $pawnColor, array $figuresAlongTheRoad);
}