<?php

namespace Chrysanthos\LaravelNovaFutureTrend;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Laravel\Nova\Metrics\Trend;
use Laravel\Nova\Metrics\TrendDateExpressionFactory;
use Laravel\Nova\Nova;

abstract class FutureTrend extends Trend
{
    protected function aggregate($request, $model, $unit, $function, $column, $dateColumn = null)
    {
        $query = $model instanceof Builder ? $model : (new $model)->newQuery();

        $timezone = Nova::resolveUserTimezone($request) ?? $request->timezone ?? config('app.timezone');

        $expression = (string) TrendDateExpressionFactory::make(
            $query, $dateColumn = $dateColumn ?? $query->getModel()->getQualifiedCreatedAtColumn(),
            $unit, $timezone
        );

        $possibleDateResults = $this->getAllPossibleDateResults(
            $startingDate = $this->getAggregateStartingDate($request, $unit, $timezone),
            $endingDate = $this->getEndingDate($request, $unit, $timezone),
            $unit,
            $request->twelveHourTime === 'true'
        );

        $wrappedColumn = $column instanceof Expression
            ? (string) $column
            : $query->getQuery()->getGrammar()->wrap($column);

        $results = $query
            ->select(DB::raw("{$expression} as date_result, {$function}({$wrappedColumn}) as aggregate"))
            ->tap(function ($query) use ($request) {
                return $this->applyFilterQuery($request, $query);
            })
            ->whereBetween(
                $dateColumn, $this->formatQueryDateBetween([$startingDate, $endingDate])
            )->groupBy(DB::raw($expression))
            ->orderBy('date_result')
            ->get();

        $results = array_merge($possibleDateResults, $results->mapWithKeys(function ($result) use ($request, $unit) {
            return [$this->formatAggregateResultDate(
                $result->date_result, $unit, $request->twelveHourTime === 'true'
            ) => round($result->aggregate, $this->roundingPrecision, $this->roundingMode)];
        })->all());

        if (count($results) > $request->range) {
            array_shift($results);
        }

        return $this->result(Arr::last($results))->trend(
            $results
        );
    }

    protected function getAggregateStartingDate($request, $unit, $timezone)
    {
        $now = CarbonImmutable::now($timezone);

        switch ($unit) {
            case 'month':
                return $now->startOfMonth()->setTime(0, 0);

            case 'week':
                return $now->startOfWeek()->setTime(0, 0);

            case 'day':
                return $now->subDay()->setTime(0, 0);

            case 'hour':
                return with($now->addHours(24), function ($now) {
                    return $now->setTimeFromTimeString($now->hour.':00');
                });

            case 'minute':
                return with($now->addMinutes(60), function ($now) {
                    return $now->setTimeFromTimeString($now->hour.':'.$now->minute.':00');
                });

            default:
                throw new InvalidArgumentException('Invalid trend unit provided.');
        }
    }

    protected function getEndingDate($request, $unit, $timezone)
    {
        $now = CarbonImmutable::now($timezone);

        $range = $request->range;

        switch ($unit) {
            case 'month':
                return $now->addMonthsWithoutOverflow($range / 30)->endOfMonth()->setTime(23, 59);

            case 'week':
                return $now->addWeeks($range / 7)->startOfWeek()->setTime(0, 0);

            case 'day':
                return $now->addDays($range)->setTime(0, 0);

            case 'hour':
                return with($now->addHours(24), function ($now) {
                    return $now->setTimeFromTimeString($now->hour.':00');
                });

            case 'minute':
                return with($now->addMinutes(60), function ($now) {
                    return $now->setTimeFromTimeString($now->hour.':'.$now->minute.':00');
                });

            default:
                throw new InvalidArgumentException('Invalid trend unit provided.');
        }
    }
}
