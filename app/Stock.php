<?php

namespace App;

use App\Support\Database\CacheQueryBuilder;
use App\Traits\StockAnalysis;
use App\Traits\StockIndicators;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class Stock extends Model
{
    use StockIndicators;
    use StockAnalysis;
    use CacheQueryBuilder;

    protected $fillable = ['ticker_id'];
    protected $hidden = ['id', 'created_at', 'updated_at', 'ticker_id', 'data', 'projections', 'ticker'];
    protected $appends = ['symbol', 'value', 'nextDay', 'fiveDay', 'tenDay', 'lastUpdatedAt', 'lastUpdate', 'quickLook', 'averageKellySize', 'inWatchlist', 'recommendedPosition', 'currentPosition'];

    public function ticker()
    {
        return $this->belongsTo(Ticker::class);
    }

    public function projections()
    {
        return $this->hasMany(StockProjection::class)->orderBy('id', 'DESC');
    }

    public function trainedModels()
    {
        return $this->hasMany(TrainedStockModel::class);
    }

    public function accuracy()
    {
        return $this->hasMany(ModelAccuracy::class)->orderBy('id', 'DESC');
    }

    public function portfolios()
    {
        return $this->belongsToMany(Portfolio::class);
    }

    public function watchlists()
    {
        return $this->belongsToMany(Watchlist::class);
    }

    public function automations()
    {
        return $this->hasMany(Automation::class);
    }

    public function trades()
    {
        return $this->hasMany(Trade::class);
    }

    public function modelParameters()
    {
        return $this->hasOne(ModelParameter::class);
    }

    public static function fetch($symbol)
    {
        $ticker = Ticker::fetch($symbol);

        if ($ticker) {
            return $ticker->stock;
        }

        sleep(5);

        return self::fetch($symbol);
    }

    public function getSymbolAttribute()
    {
        return $this->ticker['symbol'];
    }

    public function getAverageKellySizeAttribute()
    {
        $projections = $this->projections()->limit(3)->get();
        $trueAverage = $projections->pluck('kellyPositionSize')->avg();

        return round($trueAverage, 2);
    }

    public function recommendedPositionFor(User $user = null)
    {
        return $this->getRecommendedPositionAttribute($user);
    }

    public function getRecommendedPositionAttribute(User $user = null)
    {
        $user = $user ? $user : auth()->user();
        if ($user && $user->portfolio) {
            $moneyIn = ($this->averageKellySize / 100) * $user->portfolio->value;

            return round($moneyIn / $this->value);
        }

        return 0;
    }

    /**
     * Helper method to get the data of the given stock, limited to 365 results.
     *
     * @param $eod bool - whether ot not to only include end of day daya in the results
     *
     * @return HasManyThrough
     */
    public function data($eod = true)
    {
        $allData = $this->hasManyThrough(TickerData::class, Ticker::class, 'id', 'ticker_id', 'ticker_id');

        if ($eod) {
            $allData = $allData->eod();
        }

        $allData->orderBy('created_at', 'ASC');
        $count = $allData->count();

        return $allData->offset($count - 365);
    }

    public function getLastUpdateAttribute()
    {
        return $this->data(false)->get()->last();
    }

    public function getLastUpdatedAtAttribute()
    {
        $lastUpdate = $this->lastUpdate;

        return $lastUpdate->created_at->format('H:i');
    }

    public function getValueAttribute()
    {
        return $this->lastUpdate->close;
    }

    public function getProjectionsAttribute()
    {
        return $this->projections()->limit(3)->get();
    }

    public function getAccuracyAttribute()
    {
        return $this->accuracy()->limit(3)->get();
    }

    private function getDatasetFor(Collection $dataset, string $key, string $timePeriod)
    {
        return $dataset->filter(function ($projection) use ($key, $timePeriod) {
            return $projection->$key === $timePeriod;
        })->first();
    }

    private function getProjectionAndAccuracyFor(string $timePeriod)
    {
        $projection = $this->getDatasetFor($this->projections, 'projection_for', $timePeriod);
        $accuracy = $this->getDatasetFor($this->accuracy, 'time_period', $timePeriod);

        return ['projection' => $projection, 'accuracy' => $accuracy];
    }

    private function getVerdictFor(string $timePeriod)
    {
        $projectionAndAccuracy = $this->getProjectionAndAccuracyFor($timePeriod);
        $projection = $projectionAndAccuracy['projection'];
        $verdict = $projection['verdict'];
        $accuracy = $projectionAndAccuracy['accuracy']['accuracy_'.str_replace(' ', '_', $verdict)] * 100;

        return [
            'verdict'   => $verdict,
            'accuracy'  => round($accuracy),
            'kellySize' => round($projection['kellyPositionSize'], 2),
        ];
    }

    private function getProbabilityLikelyOutcomeFor(string $timePeriod)
    {
        $projection = $this->getDatasetFor($this->projections, 'projection_for', $timePeriod);
        $probabilityProfit = round($projection->probabilityProfit * 100);
        $probabilityLoss = round($projection->probabilityLoss * 100);

        if ($probabilityProfit > $probabilityLoss) {
            return ['profit' => $probabilityProfit];
        }

        return ['loss' => $probabilityLoss];
    }

    private function getDayString(Carbon $lastUpdate)
    {
        $dayDiff = Carbon::now()->diffInDays($lastUpdate);
        switch ($dayDiff) {
            case 0:
                return 'Today';
            case 1:
                return 'Yesterday';
            default:
                return $lastUpdate->isoFormat('dddd');
        }
    }

    public function getQuickLookAttribute()
    {
        $lastUpdateCreatedAt = $this->lastUpdate->created_at;
        $lastUpdateDay = $this->getDayString($lastUpdateCreatedAt);
        $lastUpdatedOn = $lastUpdateCreatedAt->format('m/d/Y - H:i');
        $lastProjectionUpdate = $this->projections->first()->created_at->format('m/d/Y - H:i');
        $nextDayBroad = $this->getProbabilityLikelyOutcomeFor('next day');
        $fiveDayBroad = $this->getProbabilityLikelyOutcomeFor('five day');
        $tenDayBroad = $this->getProbabilityLikelyOutcomeFor('ten day');

        return collect([
            'price'                 => $this->value,
            'change'                => $this->lastUpdate->change,
            'changePercent'         => $this->lastUpdate->change_percent,
            'lastProjectionUpdate'  => $lastProjectionUpdate,
            'lastUpdateDay'         => $lastUpdateDay,
            'lastUpdate'            => $lastUpdatedOn,
            'nextDay'               => $nextDayBroad,
            'fiveDay'               => $fiveDayBroad,
            'tenDay'                => $tenDayBroad,
        ]);
    }

    public function getNextDayAttribute()
    {
        return $this->getVerdictFor('next day');
    }

    public function getFiveDayAttribute()
    {
        return $this->getVerdictFor('five day');
    }

    public function getTenDayAttribute()
    {
        return $this->getVerdictFor('ten day');
    }

    public function getLastTrainedModel($profitWindow = 1)
    {
        $model = $this->trainedModels()->where('profit_window', $profitWindow)->get()->last();
        if ($model !== null && $model instanceof TrainedStockModel) {
            $modelTrainedAt = $model->created_at;
            $lastDataPointTakenAt = $this->data->last()->created_at;
            if ($modelTrainedAt->diffInHours($lastDataPointTakenAt) >= 23) {
                // Used a cached model only if you are analyzing the stock more
                // than once a day. Otherwise train a new model on up to date
                // data.
                return;
            }

            return $model->retrieve();
        }
    }

    public function getInWatchlistAttribute()
    {
        if (Auth::check()) {
            $watchlist = Auth::user()->watchlist;

            return $watchlist && $watchlist->stocks->contains($this) ? 'true' : 'false';
        }

        return 'false';
    }

    public function currentPositionFor(User $user = null)
    {
        return $this->getCurrentPositionAttribute($user);
    }

    public function getCurrentPositionAttribute(User $user = null)
    {
        $user = $user ? $user : Auth::user();
        if ($user && $user->portfolio) {
            $stocks = $user->portfolio->stocks;
            if ($stocks->contains($this)) {
                return $stocks->find($this)->pivot->shares;
            }
        }

        return 0;
    }
}
