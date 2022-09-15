<?php

namespace App\Traits;

use Illuminate\Support\Collection;
use Laratrade\Trader\Facades\Trader;

trait StockIndicators
{
    use Math;

    private function padArray(array $array)
    {
        return array_pad($array, -365, 0);
    }

    private function paddedDataValues(string $key)
    {
        $dailyData = $this->data(true)->get();

        return $dailyData->pluck($key)->values()->pad(-365, 0)->toArray();
    }

    public function getOpenAttribute()
    {
        return $this->paddedDataValues('open');
    }

    public function getHighAttribute()
    {
        return $this->paddedDataValues('high');
    }

    public function getLowAttribute()
    {
        return $this->paddedDataValues('low');
    }

    public function getCloseAttribute()
    {
        return $this->paddedDataValues('close');
    }

    public function getVolumeAttribute()
    {
        return $this->paddedDataValues('volume');
    }

    public function getRealAttribute()
    {
        return $this->close;
    }

    // ---------======================================================---------
    // ---------=============== Stock analysis methods ===============---------
    // ---------======================================================---------

    public function getSmaAttribute()
    {
        return $this->sma();
    }

    public function sma($timePeriod = 30)
    {
        $sma = Trader::sma($this->real, $timePeriod);

        return $this->padArray($sma);
    }

    public function getEmaAttribute()
    {
        return $this->ema();
    }

    public function ema($timePeriod = 30)
    {
        $ema = Trader::ema($this->real, $timePeriod);

        return  $this->padArray($ema);
    }

    public function getWmaAttribute()
    {
        return $this->wma();
    }

    public function wma($timePeriod = 30)
    {
        $wma = Trader::wma($this->real, $timePeriod);

        return $this->padArray($wma);
    }

    public function getRsiAttribute()
    {
        return $this->rsi();
    }

    public function rsi($timePeriod = 14)
    {
        $rsi = Trader::rsi($this->real, $timePeriod);

        return $this->padArray($rsi);
    }

    public function getUltoscAttribute()
    {
        return $this->ultosc();
    }

    public function ultosc($tpd1 = 7, $tpd2 = 14, $tpd3 = 28)
    {
        $ultosc = Trader::ultosc($this->high, $this->low, $this->close, $tpd1, $tpd2, $tpd3);

        return $this->padArray($ultosc);
    }

    public function getTypPriceAttribute()
    {
        return $this->typPrice();
    }

    public function typPrice()
    {
        return Trader::typprice($this->high, $this->low, $this->close);
    }

    public function getMidPriceAttribute()
    {
        return $this->midPrice();
    }

    public function midPrice($timePeriod = 14)
    {
        return Trader::midprice($this->high, $this->low, $timePeriod);
    }

    public function getDxAttribute()
    {
        return $this->dx();
    }

    public function dx($timePeriod = 14)
    {
        $dx = Trader::dx($this->high, $this->low, $this->close, $timePeriod);

        return $this->padArray($dx);
    }

    public function getSarAttribute()
    {
        return $this->sar();
    }

    public function sar($acceleration = 0.02, $maximum = 0.2)
    {
        return Trader::sar($this->high, $this->low, $acceleration, $maximum);
    }

    public function getObvAttribute()
    {
        return $this->obv();
    }

    public function obv()
    {
        return Trader::obv($this->real, $this->volume);
    }

    // ---------======================================================---------
    // ---------================= Data  manipulation =================---------
    // ---------======================================================---------

    private function computeCloseDelta(Collection $indicatedPriceLevels, array $close)
    {
        return $indicatedPriceLevels->map(function ($indicatedPrice, $index) use ($close) {
            return $indicatedPrice == 0 ? 0 : $close[$index] - $indicatedPrice;
        });
    }

    public function computeObvSlope()
    {
        $obv = collect($this->obv());

        return $obv->map(function ($volume, $index) use ($obv) {
            if ($index > 0) {
                // Get the current and previous OBV reading to determine its
                // current slope for every time period.
                $obvWindow = $obv->slice($index - 1, 2)->values();

                return $this->computeSlope($obvWindow->toArray());
            }

            return 0;
        });
    }

    private function indicators()
    {
        $close = $this->close;
        $sar = collect($this->sar())->values();
        $wma = collect($this->wma())->values();

        return [
            'dx'        => collect($this->dx()),
            'rsi'       => collect($this->rsi()),
            'ultosc'    => collect($this->ultosc()),
            'sard'      => $this->computeCloseDelta($sar, $close),
            'wmad'      => $this->computeCloseDelta($wma, $close),
        ];
    }

    public function trendIndicators()
    {
        $data = [];
        $close = $this->close;
        $indicators = $this->indicators();

        foreach (range(0, count($close) - 1) as $index) {
            foreach ($indicators as $indicator => $values) {
                $data[$index][$indicator] = $values->get($index);
            }
        }

        return collect($data)->filter(function ($row) {
            foreach ($row as $key => $value) {
                if ($value == null || $value == 0) {
                    return false;
                }
            }

            return true;
        });
    }

    public function nDayHistoricalProfitability($nDays)
    {
        $close = $this->close;

        return collect($close)->map(function ($currentClose, $index) use ($close, $nDays) {
            $hasNext = $index < (count($close) - $nDays);
            if ($hasNext == false) {
                return;
            }

            $nextClose = $close[$index + $nDays];
            $delta = $nextClose - $currentClose;
            $average = ($nextClose + $currentClose) / 2;
            $average = $average ? $average : 1;
            $percentageChange = (abs($delta) / $average) * 100;
            if ($percentageChange >= 5) {
                $magnitude = 'large';
            } elseif ($percentageChange >= 1) {
                $magnitude = 'moderate';
            } else {
                $magnitude = 'small';
            }

            return $magnitude.' '.($delta > 0 ? 'profit' : 'loss');
        });
    }

    public function nextDayHistoricalProfitability()
    {
        return $this->nDayHistoricalProfitability(1);
    }

    public function fiveDayHistoricalProfitability()
    {
        return $this->nDayHistoricalProfitability(5);
    }

    public function tenDayHistoricalProfitability()
    {
        return $this->nDayHistoricalProfitability(10);
    }

    public function formatProfitabilityAndIndicators(Collection $profitability)
    {
        // Get the total number of points to go through
        $numberPoints = count($profitability);

        // Snag the indicators for the time period, pad the beginning because
        // some of them have offset starting points (rsi starts at 14)
        $indicators = $this->trendIndicators();
        $paddedIndicators = $indicators->pad(-$numberPoints, null);

        // Loop through all of the points and filter out days that have a null
        // value for either the indicator or the profit projection.
        // This is done to ensure the profit projections and indicators begin
        // and end at the same index.
        foreach (range(0, $numberPoints - 1) as $index) {
            $profitProjection = $profitability->get($index);
            $pointIndicators = $paddedIndicators->get($index);
            if ($profitProjection === null || $pointIndicators === null) {
                $profitability->forget($index);
                $paddedIndicators->forget($index);
            }
        }

        return [
            'profitability' => $profitability->values(),
            'indicators'    => $paddedIndicators->map(function ($item) {
                return array_values($item);
            })->values(),
        ];
    }
}
