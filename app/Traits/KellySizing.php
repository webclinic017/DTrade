<?php

namespace App\Traits;

use App\StockProjection;

trait KellySizing
{
    use Math;

    /**
     * This method determines the most likely outcome out of a given broad
     * outcome, and returns the approximated percentage attributed to that
     * result.
     *
     * Note: The percentages attributed to each result are approximations of
     *       the expected return. They are, to an extent, arbitrary (for now).
     *
     * @param string $profitOrLoss - The broad outcome we are looking for.
     *
     * @return float - The mapped profit/loss percent to be expected.
     */
    public function getVerdictFor(string $profitOrLoss)
    {
        if ($this instanceof StockProjection) {
            $probabilities = $this->getBroadOutcome($profitOrLoss)->toArray();
            $mostLikelyMagnitude = array_keys($probabilities, max($probabilities))[0];
            switch ($mostLikelyMagnitude) {
                case "probability_large_$profitOrLoss":
                    return 0.10; // A large P/L is expected to have a delta of +/- 10%
                    break;
                case "probability_moderate_$profitOrLoss":
                    return 0.05; // A moderate P/L is expected to have a delta of +/- 5%
                    break;
                case "probability_small_$profitOrLoss":
                    return 0.02; // A small P/L is expected to have a delta of +/- 2%
                    break;
            }
        }

        return 0.0;
    }

    private function getPotentialProfitAmount()
    {
        return $this->getVerdictFor('profit');
    }

    private function getPotentialLossAmount()
    {
        return $this->getVerdictFor('loss');
    }

    public function getKellyPositionSizeAttribute()
    {
        if ($this instanceof StockProjection) {
            $profitAmount = $this->getPotentialProfitAmount();
            $lossAmount = $this->getPotentialLossAmount();
            $winProbability = $this->probabilityProfit;

            $profitAmount = $this->normalize($profitAmount, 0.01, 0.12);

            // The kelly formula being used is: W/A – (1 – W)/B
            // Where W is the win probability, B is the profit (%) in the event
            // of a win, and A is the potential loss (%).
            // https://blogs.cfainstitute.org/investor/2018/06/14/the-kelly-criterion-you-dont-know-the-half-of-it/
            return ($winProbability / $lossAmount) - ((1 - $winProbability) / $profitAmount);
        }

        return 0;
    }
}
