<?php

namespace FigureValidators;
use Exception;

class ValidatorPawnMove implements ValidatorInterface
{
    private array $figures;

    /**
     * @throws Exception
     */
    public function validate($xFrom, $yFrom, $xTo, $yTo, $figures): void
    {
        $this->figures = $figures;

        if (!$this->availabilityShapesCheck($xFrom, $yFrom))
            throw new Exception('Figure not found!');

        // Проверяем цвет фигуры
        $pawnColor = $this->checkingColorFigure($xFrom, $yFrom); // false = white, true = black

        // Смотрим куда можно ходить
        $whereCanGo = $this->whereCanGo($xFrom, $yFrom, $pawnColor);

        // Если клетка на которую пытаемся сходить не входит в список разрешенных выкидываем ошибку
        if (!in_array($xTo . '-' . $yTo, $whereCanGo))
            throw new Exception("Pawn can't come in here");

        // Проверяем наличие фигур на пути пешки.
        $figuresAlongTheRoad = $this->checkOtherFigures($whereCanGo);

        // Проверяем пытается ли пешка кого-то съесть или перескочить через кого-то
        $iAmHungry = $this->iAmHungry($yTo, $pawnColor, $figuresAlongTheRoad);

        // Выкидываем ошибку если френдли фаер
        if (!empty($iAmHungry)) {
            if ($iAmHungry->isBlack === $pawnColor)
                throw new Exception('Its friendly fire!');
        }
    }

    /**
     * Проверяем наличие пешки на заданной клетке.
     *
     * @param string $xFrom - координаты по оси x
     * @param int $yFrom - координаты по оси y
     * @return bool
     * @throws Exception
     */
    public function availabilityShapesCheck(string $xFrom, int $yFrom): bool
    {
        if (isset($this->figures[$xFrom][$yFrom]) && get_class($this->figures[$xFrom][$yFrom]) === 'Pawn') {
            return true;
        } elseif (isset($this->figures[$xFrom][$yFrom]) && get_class($this->figures[$xFrom][$yFrom]) !== 'Pawn') {
            throw new Exception("$xFrom-$yFrom is not Pawn.");
        } else {
            throw new Exception("$xFrom-$yFrom is empty.");
        }
    }

    /**
     * Проверяем цвет фигуры
     *
     * @param string $xFrom
     * @param int $yFrom
     * @return bool - false = white, true = black
     */
    public function checkingColorFigure(string $xFrom, int $yFrom): bool
    {
        return $this->figures[$xFrom][$yFrom]->isBlack;
    }

    /**
     * Смотрим куда можно ходить
     *
     * @param string $xFrom
     * @param int $yFrom
     * @param bool $color
     * @return array - массив клеток куда потенциально можно сходить
     */
    public function whereCanGo(string $xFrom, int $yFrom, bool $color): array
    {
        if ($color && $yFrom === 7) {
            return [
                $xFrom . '-' . $yFrom - 1,
                $xFrom . '-' . $yFrom - 2,
            ];
        } elseif ($color && $yFrom < 7 && $yFrom > 1) {
            return [
                $xFrom . '-' . $yFrom - 1
            ];
        } elseif (!$color && $yFrom === 2) {
            return [
                $xFrom . '-' . $yFrom + 1,
                $xFrom . '-' . $yFrom + 2,
            ];
        } elseif (!$color && $yFrom > 2 && $yFrom < 8) {
            return [
                $xFrom . '-' . $yFrom + 1
            ];
        } else {
            return [];
        }
    }

    /**
     * Проверяем наличие фигур на пути пешки
     *
     * @param array $whereCanGo
     * @return array - массив фигур встречающихся по дороге пешки
     */
    public function checkOtherFigures(array $whereCanGo): array
    {
        $result = [];
        foreach ($whereCanGo as $where) {
            $coordinates = explode('-', $where);
            if (!empty($this->figures[$coordinates[0]][$coordinates[1]])) {
                $figure = get_class($this->figures[$coordinates[0]][$coordinates[1]]);
                $figureColor = $this->figures[$coordinates[0]][$coordinates[1]]->isBlack;
                $result[] = [
                    'figure' => $figure,
                    'figureColor' => $figureColor,
                    'x' => $coordinates[0],
                    'y' => (int)$coordinates[1]
                ];
            }
        }
        return $result;
    }

    /**
     * Проверяем пытается ли пешка кого-то съесть или перескочить через кого-то.
     * @param int $yTo
     * @param bool $pawnColor
     * @param array $figuresAlongTheRoad
     * @return bool|void|object - false - просто ходит вперед, true - объект поедаемой фигуры.
     * @throws Exception
     */
    public function iAmHungry(int $yTo, bool $pawnColor, array $figuresAlongTheRoad)
    {
        foreach ($figuresAlongTheRoad as $item) {
            if ($pawnColor) {
                if ($yTo === $item['y']) {
                    return $this->figures[$item['x']][$item['y']];
                } elseif ($yTo >= $item['y']) {
                    throw new Exception('You cannot jump over the pieces.');
                } else {
                    return false;
                }
            } else {
                if ($yTo === $item['y']) {
                    return $this->figures[$item['x']][$item['y']];
                } elseif ($yTo <= $item['y']) {
                    throw new Exception('You cannot jump over the pieces.');
                } else {
                    return false;
                }
            }
        }
    }
}