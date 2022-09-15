<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class ModelParameter extends Model
{
    protected $fillable = ['stock_id', 'cost', 'degree', 'gamma', 'tolerance'];

    public static $durationWeight = 30; // 0 - 100 scale, 100 is equal weight as average accuracy achieved

    public $optimizeParameters = [
        'cost'      => [1, 100, 5],
        'degree'    => [1, 5, 1],
        'gamma'     => [1, 1000, 10],
        'tolerance' => [0.001, 0.1, 0.01],
    ];

    public static function boot()
    {
        parent::boot();

        static::saved(function (self $model) {
            $model->stock->loadModelParameters();
        });
    }

    public function stock()
    {
        return $this->belongsTo(Stock::class);
    }

    public function accuracy()
    {
        return $this->hasManyThrough(ModelAccuracy::class, Stock::class, 'id', 'stock_id');
    }

    private function isClosestToMax($parameter, $parameterConfig)
    {
        $max = $parameterConfig[1];
        $maxModulo = $this->$parameter > 1 ? ($max % $this->$parameter) : fmod($max, $this->$parameter);
        $isClosestToMax = $this->$parameter >= ($max - $maxModulo);

        return $isClosestToMax;
    }

    public function modifyParameter($parameter)
    {
        if (array_key_exists($parameter, $this->optimizeParameters)) {
            $parameterConfig = $this->optimizeParameters[$parameter];

            if ($this->isClosestToMax($parameter, $parameterConfig)) {
                // If the current value of the parameter being modified is at its
                // upper limit, restart the optimization process at its minimum.
                $this->$parameter = $this->optimizeParameters[$parameter][0];
            } else {
                // Otherwise increase the parameter by its specified delta.
                $delta = $parameterConfig[2];
                $this->$parameter += $delta;
            }

            $this->save();
        }
    }

    private function getCurrentConfig()
    {
        return [
            'cost'      => $this->cost,
            'degree'    => $this->degree,
            'gamma'     => $this->gamma,
            'tolerance' => $this->tolerance,
        ];
    }

    private function restoreConfig(array $config)
    {
        foreach ($config as $property => $value) {
            $this->$property = $value;
        }
        $this->save();
    }

    public function getAverageScore()
    {
        return self::scoreResults($this->accuracy->map(function (ModelAccuracy $accuracy) {
            return collect($accuracy->only([
                'accuracy_large_profit',
                'accuracy_medium_profit',
                'accuracy_small_profit',
                'accuracy_large_loss',
                'accuracy_medium_loss',
                'accuracy_small_loss',
                'duration',
            ]));
        }));
    }

    public static function scoreResults(Collection $results)
    {
        $averageAccuracy = $results->map(function (Collection $result) {
            return $result->except('duration');
        })->flatten()->average();
        $averageDuration = $results->map(function (Collection $result) {
            return $result->only('duration');
        })->flatten()->average();

        return ($averageAccuracy * 100) - (($averageDuration * self::$durationWeight) / 100);
    }

    /**
     * @throws \Exception
     */
    public function recursiveOptimization($parameter, $previousConfig = null, $previousScore = 0)
    {
        if ($this->stock instanceof Stock) {
            $this->modifyParameter($parameter);
            $results = ModelAccuracy::test($this->stock);
            $score = $this->scoreResults($results);
            if ($previousConfig && $score < $previousScore) {
                // If the last run was better, stop optimizing
                $this->restoreConfig($previousConfig);

                return $previousScore;
            } else {
                // Either here was no previous config and no previous results
                // (first time through), or the previous results were worse than
                // the ones we just got. Either way, optimize again!
                $config = $this->getCurrentConfig();

                return $this->recursiveOptimization($parameter, $config, $score);
            }
        }

        return 0;
    }

    /**
     * @throws \Exception
     */
    public function optimize()
    {
        $previousScore = $this->getAverageScore();
        $previousConfig = null;
        foreach ($this->optimizeParameters as $parameter => $parameterConfig) {
            $previousScore = $this->recursiveOptimization($parameter, $previousConfig, $previousScore);
            $previousConfig = $this->getCurrentConfig();
        }

        $averageScore = $this->getAverageScore();

        return [
            'score'     => $previousScore,
            'config'    => $previousConfig,
            'avgScore'  => $averageScore,
        ];
    }

    public static function initiate(Stock $stock)
    {
        self::create([
            'stock_id'  => $stock->id,
            'cost'      => 5,
            'degree'    => 3,
            'gamma'     => 3,
            'tolerance' => 0.001,
        ]);
    }
}
