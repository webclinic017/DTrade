<?php

namespace App;

use AlphaVantage;
use App\Traits\DataSource;
use Illuminate\Database\Eloquent\Model;

class AlphaVantageApi extends Model
{
    use DataSource;

    public $fillable = ['api_key', 'user_id'];
    private $api;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->lastTradingDayQuoteKey = 'latest_trading_day';
        $this->maxAutomaticRequestsPerDay = 400;
    }

    /**
     * Every AV API key belongs to a user.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Every AV API Key has a set of tickers it updates.
     */
    public function tickers()
    {
        return $this->hasMany(Ticker::class);
    }

    /**
     * Helper method to initialize the Alpha Advantage API.
     */
    private function setupApi()
    {
        if ($this->api_key && $this->api == null) {
            $options = new AlphaVantage\Options();
            $options->setApiKey($this->api_key);
            $this->api = new AlphaVantage\Client($options);
        }
    }

    /**
     * Helper method that sets up the api and returns the timeSeries method.
     */
    private function timeSeries()
    {
        $this->setupApi();
        if ($this->api instanceof AlphaVantage\Client) {
            return $this->api->timeSeries();
        }
    }

    /**
     * Helper method that formats a given quote to a basic standard format which
     * can be inserted later into the TickerData table.
     *
     * Note: This method does not ensure all required fields exist for later use
     * but rather formats the quote so that the keys/values that are present
     * follow a similar standard.
     *
     * @param array $quote - a single quote / time period.
     *
     * @return array - the formatted quote.
     */
    private function quoteFormatter(array $quote)
    {
        $newQuote = [];
        foreach ($quote as $key => $value) {
            // Remove leading whitespace and non-alphanumeric characters from the key
            $key = trim(preg_replace('/[^A-Za-z ]/', '', $key));
            $key = str_replace(' ', '_', $key);
            // Change 'price' to 'close' if necessary
            $key = $key == 'price' ? 'close' : $key;

            // Remove symbols (mainly %) from the value string
            $value = preg_replace('/[^A-Za-z0-9.-]/', '', $value);
            $newQuote[$key] = $value;
        }

        return $newQuote;
    }

    /**
     * Method that gets the most recent quote for a given symbol.
     *
     * @param string $symbol - The symbol to look up.
     *
     * @return array | null
     */
    public function quote(string $symbol)
    {
        $timeSeries = $this->timeSeries();
        if ($timeSeries) {
            $unformattedQuote = $timeSeries->globalQuote($symbol);
            $unformattedQuote = $unformattedQuote['Global Quote'];

            return $this->quoteFormatter($unformattedQuote);
        }
    }

    /**
     * Method that gets all of a given symbols' prior history in daily time
     * periods.
     *
     * @param string $symbol - The symbol to look up.
     *
     * @return null | array
     */
    public function dailyHistory(string $symbol)
    {
        $timeSeries = $this->timeSeries();
        if ($timeSeries) {
            $rawData = $timeSeries->daily($symbol, 'full');
            $rawData = collect($rawData['Time Series (Daily)']);

            return $rawData->map(function (array $quote, $date) {
                $formattedQuote = $this->quoteFormatter($quote);
                $formattedQuote['date'] = $date;

                return $formattedQuote;
            })->reverse()->values();
        }
    }
}
