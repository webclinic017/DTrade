<?php

namespace App;

use Digitonic\IexCloudSdk\Facades\Stocks\Quote;
use Illuminate\Support\Collection;

class DataSource
{
    use Traits\DataSource;

    public $tickers;

    public function __construct()
    {
        $this->tickers = $this->tickers();
        $this->lastTradingDayQuoteKey = 'latest_time';
        $this->maxAutomaticRequestsPerDay = 200000;
    }

    public static function api()
    {
        return new self();
    }

    /**
     * Method that gets all of the shared tickers for the platform.
     */
    public function tickers()
    {
        return Ticker::where('alpha_vantage_api_id', null);
    }

    /**
     * Helper method that changes a $string separated by a given $delimiter and
     * converts it to camel case formatting.
     *
     * @param string $string
     * @param string $delimiter
     * @param bool   $capitalizeFirstCharacter
     *
     * @return string
     */
    private function camelCase(string $string, string $delimiter = '_', bool $capitalizeFirstCharacter = false)
    {
        $str = str_replace($delimiter, '', ucwords($string, $delimiter));

        if (!$capitalizeFirstCharacter) {
            $str = lcfirst($str);
        }

        return $str;
    }

    /**
     * Helper method that formats a given quote to a basic standard format which
     * can be inserted later into the TickerData table.
     *
     * Note: This method does not ensure all required fields exist for later use
     * but rather formats the quote so that the keys/values that are present
     * follow a similar standard.
     *
     * @param Collection $quote - a single quote / time period.
     *
     * @return array - the formatted quote.
     */
    private function formatQuote(Collection $quote)
    {
        $properties = ['open', 'high', 'low', 'close', 'previous_close', 'change', 'change_percent', 'volume', $this->lastTradingDayQuoteKey];
        $formattedQuote = [];

        foreach ($properties as $property) {
            $value = $quote->get($property);
            $camelCase = $this->camelCase($property);

            if ($property == 'close') {
                $value = $quote->get('latestPrice');
            } elseif ($quote->get($camelCase)) {
                $value = $quote->get($camelCase);
            }

            $formattedQuote[$property] = $value;
        }

        return $formattedQuote;
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
        $quote = Quote::setSymbol($symbol)->get();

        return $this->formatQuote($quote);
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
        if (AlphaVantageApi::exists()) {
            return AlphaVantageApi::first()->dailyHistory($symbol);
        }

        return null;
    }
}
