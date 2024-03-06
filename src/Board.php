<?php

class Board
{
    private array $figures = [];

    public function __construct()
    {
        $this->figures['a'][1] = new Rook(false);
        $this->figures['b'][1] = new Knight(false);
        $this->figures['c'][1] = new Bishop(false);
        $this->figures['d'][1] = new Queen(false);
        $this->figures['e'][1] = new King(false);
        $this->figures['f'][1] = new Bishop(false);
        $this->figures['g'][1] = new Knight(false);
        $this->figures['h'][1] = new Rook(false);

        $this->figures['a'][2] = new Pawn(false);
        $this->figures['b'][2] = new Pawn(false);
        $this->figures['c'][2] = new Pawn(false);
        $this->figures['d'][2] = new Pawn(false);
        $this->figures['e'][2] = new Pawn(false);
        $this->figures['f'][2] = new Pawn(false);
        $this->figures['g'][2] = new Pawn(false);
        $this->figures['h'][2] = new Pawn(false);

        $this->figures['a'][7] = new Pawn(true);
        $this->figures['b'][7] = new Pawn(true);
        $this->figures['c'][7] = new Pawn(true);
        $this->figures['d'][7] = new Pawn(true);
        $this->figures['e'][7] = new Pawn(true);
        $this->figures['f'][7] = new Pawn(true);
        $this->figures['g'][7] = new Pawn(true);
        $this->figures['h'][7] = new Pawn(true);

        $this->figures['a'][8] = new Rook(true);
        $this->figures['b'][8] = new Knight(true);
        $this->figures['c'][8] = new Bishop(true);
        $this->figures['d'][8] = new Queen(true);
        $this->figures['e'][8] = new King(true);
        $this->figures['f'][8] = new Bishop(true);
        $this->figures['g'][8] = new Knight(true);
        $this->figures['h'][8] = new Rook(true);
    }

    /**
     * @throws Exception
     */
    public function move($move)
    {
        if (!preg_match('/^([a-h])(\d)-([a-h])(\d)$/', $move, $match)) {
            throw new \Exception("Incorrect move");
        }

        $xFrom = $match[1];
        $yFrom = $match[2];
        $xTo = $match[3];
        $yTo = $match[4];

        $color = $this->figures[$xFrom][$yFrom]->isBlack;

        // Проверяем наличие пешки на заданной клетке
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

        if (isset($this->figures[$xFrom][$yFrom])) {
            $this->figures[$xTo][$yTo] = $this->figures[$xFrom][$yFrom];
        }
        unset($this->figures[$xFrom][$yFrom]);

        return $color;
    }

    /**
     * Проверяем наличие пешки на заданной клетке.
     *
     * @param string $xFrom - координаты по оси x
     * @param int $yFrom - координаты по оси y
     * @return bool
     * @throws Exception
     */
    private function availabilityShapesCheck(string $xFrom, int $yFrom): bool
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
    private function checkingColorFigure(string $xFrom, int $yFrom): bool
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
    private function whereCanGo(string $xFrom, int $yFrom, bool $color): array
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
    private function checkOtherFigures(array $whereCanGo): array
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
    private function iAmHungry(int $yTo, bool $pawnColor, array $figuresAlongTheRoad)
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

    public function dump(): void
    {
        for ($y = 8; $y >= 1; $y--) {
            echo "$y ";
            for ($x = 'a'; $x <= 'h'; $x++) {
                if (isset($this->figures[$x][$y])) {
                    echo $this->figures[$x][$y];
                } else {
                    echo '-';
                }
            }
            echo "\n";
        }
        echo "  abcdefgh\n";
    }
}
