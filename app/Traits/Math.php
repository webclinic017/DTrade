<?php

namespace App\Traits;

trait Math
{
    /**
     * This method will return the slope of a given set of values, assumed to be
     * y-values, equally spaced by 1 x-value each.
     *
     * @param array $values - the array of y-values.
     *
     * @return float - the slope of the points.
     */
    public function computeSlope(array $values)
    {
        $n = count($values);
        $x = range(0, $n - 1);
        $y = $values;
        $x_sum = array_sum($x);
        $y_sum = array_sum($y);

        $xx_sum = 0;
        $xy_sum = 0;

        for ($i = 0; $i < $n; $i++) {
            $xy_sum += ($x[$i] * $y[$i]);
            $xx_sum += ($x[$i] * $x[$i]);
        }

        $slope = (($n * $xy_sum) - ($x_sum * $y_sum)) / (($n * $xx_sum) - ($x_sum * $x_sum));

        return $slope;
    }

    public function normalize($value, $min, $max)
    {
        return ($value - $min) / ($max - $min);
    }
}
