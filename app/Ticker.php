<?php

namespace App;

use App\Exceptions\AlphaVantageException;
use App\Jobs\Stocks\AnalyzeStock;
use App\Jobs\Stocks\CheckAccuracy;
use App\Jobs\Stocks\DownloadTickerHistory;
use App\Support\Database\CacheQueryBuilder;
use App\Traits\StockIndicators;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Ticker extends Model
{
    use StockIndicators;
    use CacheQueryBuilder;

    protected $fillable = ['symbol'];
    protected $hidden = ['data'];

    public function stock()
    {
        return $this->hasOne(Stock::class);
    }

    public function data()
    {
        return $this->hasMany(TickerData::class)->orderBy('created_at', 'DESC');
    }

    public function dataSource()
    {
        return $this->belongsTo(AlphaVantageApi::class, 'alpha_vantage_api_id', 'id');
    }

    private function getLastUpdatedTimestamp()
    {
        $updatedAtColumn = $this->getUpdatedAtColumn();

        return $this->$updatedAtColumn;
    }

    public function downloadInitialHistory()
    {
        $rawData = $this->dataSource->dailyHistory($this['symbol']);
        foreach ($rawData as $index => $dataPoint) {
            // Get either the previous data point or the current one if its the
            // first entry in the set.
            $dataPoint = (object) $dataPoint;
            $previous = $index > 0 ? (object) $rawData[$index - 1] : $dataPoint;
            $previousClose = (float) $previous->close;
            $change = $dataPoint->close - $previousClose;
            // Prevent Division By Zero error
            $previousClose = $previousClose != 0 ? $previousClose : 1;
            $changePercent = ($change / $previousClose) * 100;
            $data = [
                'ticker_id'      => $this->id,
                'open'           => $dataPoint->open,
                'high'           => $dataPoint->high,
                'low'            => $dataPoint->low,
                'close'          => $dataPoint->close,
                'volume'         => $dataPoint->volume,
                'previous_close' => $previous->close,
                'change'         => round($change, 2),
                'change_percent' => round($changePercent, 3),
                'created_at'     => Carbon::createFromDate($dataPoint->date),
                'updated_at'     => $this->freshTimestamp(),
            ];
            DB::table('ticker_data')->insert($data);
        }
        $this->setUpdatedAt($this->freshTimestamp());
        $this->save();
    }

    public static function symbolExists($symbol)
    {
        return self::where('symbol', strtoupper($symbol))->exists();
    }

    private static function requireAlphaVantageAPI()
    {
        $user = Auth::user();
        if ($user instanceof User) {
            $dataSource = $user->dataSource;
            if ($dataSource && $dataSource instanceof AlphaVantageApi) {
                return true;
            }
        }

        throw new AlphaVantageException();
    }

    public static function fetch($symbol, $recurse = true)
    {
        $symbol = strtoupper($symbol);
        $ticker = self::where('symbol', $symbol)->first();
        if ($ticker == null && $recurse) {
            self::requireAlphaVantageAPI();

            $tickerId = DB::table('tickers')->insertGetId([
                'symbol'     => $symbol,
                'created_at' => Carbon::now(),
            ]);

            Stock::create(['ticker_id' => $tickerId]);
            DownloadTickerHistory::withChain([
                new AnalyzeStock($symbol),
                new CheckAccuracy($symbol),
            ])->dispatch($symbol);

            return self::fetch($symbol, false);
        }

        return $ticker;
    }

    public function updateData()
    {
        $updatedAt = $this->getLastUpdatedTimestamp();
        $currentTime = $this->freshTimestamp();
        if ($updatedAt == null || $currentTime->diffInMinutes($updatedAt) > 15) {
            $rawData = $this->dataSource->quote($this['symbol']);
            $staticData = [
                'ticker_id'     => $this->id,
                'is_intraday'   => true,
            ];
            TickerData::create(array_merge($staticData, $rawData));
            $this->setUpdatedAt($this->freshTimestamp());
            $this->save();
        }
    }
}
