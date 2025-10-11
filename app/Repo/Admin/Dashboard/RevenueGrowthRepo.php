<?php

namespace App\Repo\Admin\Dashboard;

use App\Models\UserSubscription;
use App\Services\ApplicationConfig\CacheApplicationConfigService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RevenueGrowthRepo
{
    public function __construct(protected string $startDate, protected string $endDate) {}

    public function getMonthlySubscriptions(): array
    {
        $applicationConfig = (new CacheApplicationConfigService)->getApplicationConfig();

        return [
            'target' => $applicationConfig->monthly_subscriptions_target,
            'subscriptions' => UserSubscription::whereBetween('created_at', [$this->startDate, $this->endDate])->count(),
        ];
    }

    public function getCountBySubscriptions(): array
    {
        return UserSubscription::join('subscriptions', 'subscriptions.id', '=', 'user_subscriptions.subscription_id')
            ->whereBetween('user_subscriptions.created_at', [$this->startDate, $this->endDate])
            ->select('subscriptions.title', DB::raw('count(*) as total'))
            ->groupBy('subscriptions.title') // Use subscriptions.name instead of subscription_id
            ->get()
            ->toArray();
    }

    public function getRevenueByDaysOfTheMonth(): array
    {
        // Step 1: Generate all dates in the range
        $allDates = [];
        $current = Carbon::parse($this->startDate);
        $end = Carbon::parse($this->endDate);

        while ($current->lte($end)) {
            $allDates[$current->toDateString()] = 0; // Default revenue = 0
            $current->addDay();
        }

        // Step 2: Query actual revenue from subscriptions
        $revenues = UserSubscription::whereBetween('created_at', [$this->startDate, $this->endDate])
            ->join('subscriptions', 'subscriptions.id', '=', 'user_subscriptions.subscription_id')
            ->select(
                DB::raw('DATE(user_subscriptions.created_at) as date'),
                DB::raw('SUM(subscriptions.price) as revenue')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('revenue', 'date') // Fetch as associative array [date => revenue]
            ->toArray();

        // Step 3: Merge actual revenue into the full date range
        foreach ($revenues as $date => $revenue) {
            $allDates[$date] = $revenue; // Replace 0 with actual revenue
        }

        // Step 4: Convert to array of objects for easy usage in charts
        return collect($allDates)->map(fn ($revenue, $date) => [
            'date' => $date,
            'revenue' => $revenue,
            'outcome' => 0, // Add random data for testing
        ])->values()->toArray();
    }

    public function getRevenueByWeeks(): array
    {
        // Step 1: Generate all weeks in the range
        $allWeeks = [];
        $current = Carbon::parse($this->startDate);
        $end = Carbon::parse($this->endDate);
        $weekNumber = 1;

        while ($current->lte($end)) {
            $weekStart = $current->toDateString();
            $weekEnd = $current->copy()->endOfWeek()->lte($end)
                ? $current->copy()->endOfWeek()->toDateString()
                : $end->toDateString();

            $allWeeks[] = [
                'label' => "Week {$weekNumber}",
                'week_start' => $weekStart,
                'week_end' => $weekEnd,
                'revenue' => 0,
            ];

            $current->addWeek()->startOfWeek();
            $weekNumber++;
        }

        // Step 2: Query actual revenue from subscriptions (PostgreSQL compatible)
        $subscriptions = UserSubscription::whereBetween('user_subscriptions.created_at', [$this->startDate, $this->endDate])
            ->join('subscriptions', 'subscriptions.id', '=', 'user_subscriptions.subscription_id')
            ->select('user_subscriptions.created_at', 'subscriptions.price')
            ->get();

        // Step 3: Map revenues to their respective weeks
        /** @var \stdClass $subscription */
        foreach ($subscriptions as $subscription) {
            $createdAt = Carbon::parse($subscription->created_at);

            foreach ($allWeeks as $index => &$week) {
                $weekStart = Carbon::parse($week['week_start']);
                $weekEnd = Carbon::parse($week['week_end']);

                if ($createdAt->between($weekStart, $weekEnd)) {
                    $week['revenue'] += (float) $subscription->price;
                    break;
                }
            }
        }

        // Step 4: Return simplified array with only label and revenue
        return collect($allWeeks)->map(fn ($week) => [
            'label' => $week['label'],
            'revenue' => (float) $week['revenue'],
        ])->toArray();
    }
}
