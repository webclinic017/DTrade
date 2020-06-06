<?php

namespace App;

use App\Support\Database\CacheQueryBuilder;
use App\Traits\KellySizing;
use Illuminate\Database\Eloquent\Model;

class StockProjection extends Model
{
    use CacheQueryBuilder;
    use KellySizing;

    protected $fillable = [
        'stock_id',
        'projection_for',
        'verdict',
        'probability_large_loss',
        'probability_moderate_loss',
        'probability_small_loss',
        'probability_large_profit',
        'probability_moderate_profit',
        'probability_small_profit',
    ];

    protected $appends = [
        'probabilityProfit',
        'probabilityLoss',
        'kellyPositionSize',
    ];

    public function stock()
    {
        return $this->belongsTo(Stock::class);
    }

    public function getBroadOutcome(string $outcome)
    {
        $probabilities = collect();
        collect($this->fillable)->filter(function ($column) use ($outcome) {
            return strpos($column, $outcome) > -1;
        })->each(function ($column) use (&$probabilities) {
            $probabilities[$column] = $this->$column;
        });

        return $probabilities;
    }

    public function probabilityBroadOutcome(string $outcome)
    {
        $probabilityOutcome = 0.0;
        $this->getBroadOutcome($outcome)->each(function ($probability) use (&$probabilityOutcome) {
            $probabilityOutcome += $probability;
        });

        return $probabilityOutcome;
    }

    public function getProbabilityLossAttribute()
    {
        return $this->probabilityBroadOutcome('loss');
    }

    public function getProbabilityProfitAttribute()
    {
        return $this->probabilityBroadOutcome('profit');
    }
}
