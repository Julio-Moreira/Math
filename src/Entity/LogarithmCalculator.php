<?php
namespace App\Entity;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class LogarithmCalculator {
    public static function calculate(string $logarithmically, string $base, ExpressionLanguage $exprLang): int|float
    {
        $calculatedLogarithmically = $exprLang->evaluate($logarithmically);
        $calculatedBase = $exprLang->evaluate($base);

        return self::log($calculatedLogarithmically, $calculatedBase);
    }

    public static function log(int|float $logarithmically, int|float $base = 10): int|float {
        $logarithm = 0;
        while ($logarithmically > 1) {
          $logarithmically /= $base;
          $logarithm++;
        }
        return $logarithm;
    }
}