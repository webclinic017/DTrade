<?php

namespace App\Charts\Stocks;

use App\Stock;
use Illuminate\Support\Collection;

class StockDataPoints extends StockChart
{
    private $map = [
        'dx'        => 1,
        'rsi'       => 2,
        'ultosc'    => 3,
        'sard'      => 4,
        'wmad'      => 5,
    ];

    private $dataMatrix = [
        ['dx', 'rsi'],
        ['dx', 'ultosc'],
        ['dx', 'sard'],
        ['dx', 'wmad'],
        ['rsi', 'dx'],
        ['rsi', 'ultosc'],
        ['rsi', 'sard'],
        ['rsi', 'wmad'],
        ['sard', 'dx'],
        ['sard', 'rsi'],
        ['sard', 'ultosc'],
        ['sard', 'wmad'],
        ['wmad', 'dx'],
        ['wmad', 'rsi'],
        ['wmad', 'ultosc'],
        ['wmad', 'sard'],
    ];

    private $axes;

    public function __construct(Stock $stock, int $dataPoints = null)
    {
        $this->axes = $dataPoints ? $this->dataMatrix[$dataPoints] : null;
        parent::__construct($stock);
    }

    public function setup()
    {
        $this->axes = $this->axes ? $this->axes : collect($this->dataMatrix)->random();
        $this->type('scatter');
        $this->setOptions();
        $data = $this->formatData();
        $this->addDatasets($data);
    }

    private function setOptions()
    {
        $this->options([
            'xAxis' => [
                'name'          => $this->axes[0],
                'nameLocation'  => 'middle',
                'nameTextStyle' => [
                    'fontWeight'    => 'bold',
                    'fontSize'      => '20',
                ],
            ],
            'yAxis' => [
                'name'          => $this->axes[1],
                'nameLocation'  => 'middle',
                'nameTextStyle' => [
                    'fontWeight'    => 'bold',
                    'fontSize'      => '20',
                    'padding'       => 20,
                ],
            ],
        ]);
    }

    private function formatData()
    {
        $windows = $this->profitabilityWindows();
        $periods = $this->timePeriodData($windows);
        $categorized = $this->addCategoryToEachDataPoint($periods);
        $allData = $this->mergeData($categorized)->get(0);

        return $this->mapData($allData);
    }

    private function addDatasets(Collection $data)
    {
        $datasets = $this->categories();
        $datasets->each(function (string $dataset) use ($data) {
            $filteredData = $data->filter(function (Collection $row) use ($dataset) {
                return $row->contains($dataset);
            });

            $data = $filteredData->map(function (Collection $row) {
                return [$row->get(1), $row->get(2)];
            });

            $this->dataset($dataset, 'scatter', $data->values()->toArray());
        });
    }

    private function profitabilityWindows()
    {
        return collect([
            //            $this->stock->nDayHistoricalProfitability(1),
            //            $this->stock->nDayHistoricalProfitability(5),
            $this->stock->nDayHistoricalProfitability(10),
        ]);
    }

    private function timePeriodData(Collection $windows)
    {
        return $windows->map(function (Collection $window) {
            return $this->stock->formatProfitabilityAndIndicators($window);
        });
    }

    private function addCategoryToEachDataPoint(Collection $periods)
    {
        return $periods->map(function ($period) {
            return $period['indicators']->map(function ($indicators, $index) use ($period) {
                return collect($indicators)->prepend($period['profitability'][$index]);
            });
        });
    }

    private function mergeData(Collection $data)
    {
        $merged = collect();
        $data->each(function ($datum) use ($merged) {
            if ($datum instanceof Collection || is_array($datum)) {
                $merged->push($this->mergeData($datum));
            } else {
                $merged->push($datum);
            }
        });

        return $merged;
    }

    private function mapData(Collection $data)
    {
        return $data->map(function (Collection $row) {
            $xIndex = $this->map[$this->axes[0]];
            $yIndex = $this->map[$this->axes[1]];
            $x = $row->get($xIndex);
            $y = $row->get($yIndex);
            $category = $row->get(0);

            return collect([$category, $x, $y]);
        });
    }

    public function categories()
    {
        return $this->profitabilityWindows()->flatten()->unique()->filter(function ($category) {
            return $category !== null;
        })->values();
    }
}
