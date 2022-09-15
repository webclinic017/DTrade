<?php

namespace App;

use App\Events\StockUpdated;
use App\Support\Database\CacheQueryBuilder;
use App\Traits\SavesTrials;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class ModelAccuracy extends Model
{
    use CacheQueryBuilder;
    use SavesTrials;

    protected $fillable = [
        'stock_id',
        'time_period',
        'duration',
        'accuracy_large_loss',
        'accuracy_moderate_loss',
        'accuracy_small_loss',
        'accuracy_large_profit',
        'accuracy_moderate_profit',
        'accuracy_small_profit',
    ];

    protected $appends = ['averageAccuracy'];

    public function stock()
    {
        return $this->belongsTo(Stock::class);
    }

    public function modelParameters()
    {
        return $this->stock->modelParameters;
    }

    public function getAccuracyAttributes()
    {
        return collect($this->fillable)->filter(function ($attribute) {
            return strpos($attribute, 'accuracy') !== false;
        });
    }

    public function getAllAccuracyValues()
    {
        return $this->getAccuracyAttributes()->map(function ($attribute) {
            return $this->$attribute;
        });
    }

    public function getAverageAccuracyAttribute()
    {
        return $this->getAllAccuracyValues()->average();
    }

    private static function runTrial(Stock $stock, int $numDays)
    {
        $now = Carbon::now();
        $results = collect($stock->testAccuracy($numDays, 50));
        $finish = Carbon::now();
        $duration = $now->diffInSeconds($finish);

        return $results->put('duration', $duration);
    }

    public static function runTrials(Stock $stock)
    {
        return collect([
            'next day'  => self::runTrial($stock, 1),
            'five day'  => self::runTrial($stock, 5),
            'ten day'   => self::runTrial($stock, 10),
        ]);
    }

    public static function test(Stock $stock)
    {
        $tests = self::runAndSaveTrials($stock, 'time_period', 'duration', 'accuracy');

        event(new StockUpdated($stock, 'Stock projection accuracy estimates have been updated successfully.'));

        return $tests;
    }
}
