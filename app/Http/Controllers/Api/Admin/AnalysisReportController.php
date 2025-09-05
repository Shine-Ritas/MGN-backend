<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\Subscription\SubscriptionAnalysis;

class AnalysisReportController extends Controller
{
    public function __construct(protected SubscriptionAnalysis $subscriptionAnalysis) {}

    public function subscriptionAnalysis(): \Illuminate\Http\JsonResponse
    {
        return response()->json(
            [
                'subscription_analysis' => $this->subscriptionAnalysis->analysis(),
            ]
        );
    }
}
