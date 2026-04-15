<?php
// © Atia Hegazy — atiaeno.com

namespace App\Http\Controllers\Api;

use App\Models\Click;
use App\Models\Link;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AnalyticsController extends ApiController
{
    /**
     * Get analytics for a specific link.
     * GET /api/v1/links/{id}/analytics
     */
    public function show(Request $request, string $id): \Illuminate\Http\JsonResponse
    {
        $link = Link::forUser(auth()->id())
            ->where(function ($q) use ($id) {
                $q->where('id', $id)
                    ->orWhere('short_code', $id);
            })
            ->first();

        if (!$link) {
            return $this->error('Link not found', 404);
        }

        // Validate period parameter
        $validator = Validator::make($request->query(), [
            'period' => ['nullable', 'in:today,week,month,all'],
        ]);

        if ($validator->fails()) {
            return $this->error('Invalid period parameter', 422, $validator->errors()->toArray());
        }

        $period = $request->query('period', 'all');
        $clicksQuery = Click::forLink($link->id);

        // Apply period filter
        $clicksQuery = match ($period) {
            'today' => $clicksQuery->whereDate('created_at', today()),
            'week' => $clicksQuery->where('created_at', '>=', now()->subDays(7)),
            'month' => $clicksQuery->where('created_at', '>=', now()->subDays(30)),
            default => $clicksQuery,
        };

        // Build analytics data
        $analytics = [
            'link_id' => $link->id,
            'period' => $period,
            'summary' => $this->getSummary($clicksQuery, $link),
            'geography' => $this->getGeography($clicksQuery),
            'referrers' => $this->getReferrers($clicksQuery),
            'devices' => $this->getDevices($clicksQuery),
            'browsers' => $this->getBrowsers($clicksQuery),
            'daily_clicks' => $this->getDailyClicks($clicksQuery, $period),
        ];

        return $this->success($analytics);
    }

    /**
     * Get summary statistics.
     */
    private function getSummary($query, Link $link): array
    {
        $totalClicks = (clone $query)->count();

        // Calculate unique visitors based on ip_hash
        $uniqueVisitors = (clone $query)
            ->distinct('ip_hash')
            ->count('ip_hash');

        return [
            'total_clicks' => $totalClicks,
            'unique_visitors' => $uniqueVisitors,
            'ctr' => $link->clicks_count > 0
                ? round(($totalClicks / max($link->clicks_count, 1)) * 100, 2)
                : 0,
        ];
    }

    /**
     * Get geographic distribution.
     */
    private function getGeography($query): array
    {
        return (clone $query)
            ->selectRaw('country_code, COUNT(*) as count')
            ->whereNotNull('country_code')
            ->groupBy('country_code')
            ->orderByDesc('count')
            ->limit(10)
            ->get()
            ->pluck('count', 'country_code')
            ->toArray();
    }

    /**
     * Get referrer distribution.
     */
    private function getReferrers($query): array
    {
        $referrers = (clone $query)
            ->selectRaw('referrer_domain, COUNT(*) as count')
            ->groupBy('referrer_domain')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        $result = [];
        foreach ($referrers as $row) {
            $key = $row->referrer_domain ?? 'direct';
            $result[$key] = $row->count;
        }

        return $result;
    }

    /**
     * Get device distribution.
     */
    private function getDevices($query): array
    {
        $devices = (clone $query)
            ->selectRaw('device_type, COUNT(*) as count')
            ->groupBy('device_type')
            ->get();

        $result = ['mobile' => 0, 'desktop' => 0, 'tablet' => 0, 'other' => 0];

        foreach ($devices as $row) {
            $type = strtolower($row->device_type ?? 'other');
            if (isset($result[$type])) {
                $result[$type] = $row->count;
            } else {
                $result['other'] += $row->count;
            }
        }

        return $result;
    }

    /**
     * Get browser distribution.
     */
    private function getBrowsers($query): array
    {
        return (clone $query)
            ->selectRaw('browser, COUNT(*) as count')
            ->whereNotNull('browser')
            ->groupBy('browser')
            ->orderByDesc('count')
            ->limit(6)
            ->get()
            ->pluck('count', 'browser')
            ->toArray();
    }

    /**
     * Get daily clicks time series.
     */
    private function getDailyClicks($query, string $period): array
    {
        $days = match ($period) {
            'today' => 1,
            'week' => 7,
            'month' => 30,
            default => 30,
        };

        $results = (clone $query)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as clicks')
            ->groupBy('date')
            ->orderBy('date')
            ->limit($days)
            ->get();

        return $results->map(fn ($row) => [
            'date' => $row->date,
            'clicks' => $row->clicks,
        ])->toArray();
    }
}
