<?php

namespace App\Traits;

use App\Stock;
use Illuminate\Support\Collection;

trait SavesTrials
{
    abstract public static function runTrials(Stock $stock): Collection;

    public static function runAndSaveTrials(Stock $stock, $periodKey, $saveKey, $dataPrefix)
    {
        $trials = self::runTrials($stock);
        $trials->each(function (Collection $trialData, $trialPeriod) use ($stock, $periodKey, $saveKey, $dataPrefix) {
            $trial = [
                'stock_id'  => $stock->id,
                $periodKey  => $trialPeriod,
            ];
            $trialData->each(function ($item, $key) use (&$trial, $saveKey, $dataPrefix) {
                $key = ($key === $saveKey) ? $key : $dataPrefix.'_'.str_replace(' ', '_', $key);
                $trial[$key] = $item;
            });
            self::create($trial);
        });

        return $trials;
    }
}
