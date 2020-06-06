<?php

namespace App\Traits;

use App\Stock;
use App\TrainedStockModel;
use Phpml\Classification\SVC;
use Phpml\CrossValidation\StratifiedRandomSplit;
use Phpml\Dataset\ArrayDataset;
use Phpml\Estimator;
use Phpml\Metric\ClassificationReport;
use Phpml\SupportVectorMachine\Kernel;

trait StockAnalysis
{
    public function makeInformedProjection(Estimator $classifier)
    {
        $projection = collect($classifier->predictProbability(array_values($this->trendIndicators()->last())));
        $projection['verdict'] = $projection->search($projection->max());

        return $projection->toArray();
    }

    public function trainClassifierOnData(array $samples, array $labels, $save = true, $numberDays = 1)
    {
        $classifier = new SVC(
            Kernel::RBF,
            5,
            3,
            3,
            1.1,
            0.001,
            100,
            true,
            true
        );
        $classifier->train($samples, $labels);

        if ($this instanceof Stock && $save) {
            TrainedStockModel::store($classifier, $this, $numberDays);
        }

        return $classifier;
    }

    public function makeProjectionFor($profitWindow, $numberDays)
    {
        if ($this instanceof Stock) {
            $classifier = $this->getLastTrainedModel($numberDays);
            if ($classifier instanceof Estimator) {
                return $this->makeInformedProjection($classifier);
            }
        }

        $formattedData = $this->formatProfitabilityAndIndicators($profitWindow);
        $classifier = $this->trainClassifierOnData($formattedData['indicators']->toArray(), $formattedData['profitability']->toArray(), true, $numberDays);

        return $this->makeInformedProjection($classifier);
    }

    public function nDayProjection(int $numberDays)
    {
        $nDayProfit = $this->nDayHistoricalProfitability($numberDays);

        return $this->makeProjectionFor($nDayProfit, $numberDays);
    }

    public function nextDayProjection()
    {
        return $this->nDayProjection(1);
    }

    public function fiveDayProjection()
    {
        return $this->nDayProjection(5);
    }

    public function tenDayProjection()
    {
        return $this->nDayProjection(10);
    }

    public function runTest(ArrayDataset $dataset)
    {
        $dataset = new StratifiedRandomSplit($dataset, 0.02);

        // train group
        $samples = $dataset->getTrainSamples();
        $labels = $dataset->getTrainLabels();
        $classifier = $this->trainClassifierOnData($samples, $labels, false);

        // test group
        $testSamples = $dataset->getTestSamples();
        $testLabels = $dataset->getTestLabels();
        $predictedLabels = collect($testSamples)->map(function ($sample) use ($classifier) {
            $probabilities = collect($classifier->predictProbability($sample));

            return $probabilities->search($probabilities->max());
        })->toArray();

        $report = new ClassificationReport($testLabels, $predictedLabels, 1);

        return $report->getF1score();
    }

    public function testAccuracy($numberDays = 1, $numberTests = 500)
    {
        $profitWindow = $this->nDayHistoricalProfitability($numberDays);
        $formattedData = $this->formatProfitabilityAndIndicators($profitWindow);

        $dataset = new ArrayDataset(
            $samples = $formattedData['indicators']->toArray(),
            $targets = $formattedData['profitability']->toArray()
        );

        $results = collect()->pad($numberTests, null)->map(function () use ($dataset) {
            return $this->runTest($dataset);
        });

        $labels = collect(array_keys($results->first()));

        $averages = [];
        $labels->each(function ($label) use ($results, &$averages) {
            $averages[$label] = round($results->avg($label), 2);
        });

        return $averages;
    }
}
