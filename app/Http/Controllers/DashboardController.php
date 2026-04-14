<?php
// © Atia Hegazy — atiaeno.com

namespace App\Http\Controllers;

use App\Models\Click;
use App\Models\Link;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Handle dashboard request.
     */
    public function __invoke(Request $request): Response
    {
        $userId = Auth::id();

        $totalLinks = Link::forUser($userId)->count();
        $activeLinks = Link::forUser($userId)->active()->count();
        $totalClicks = Link::forUser($userId)->sum('clicks_count');

        $clicksToday = Click::whereHas('link', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })->whereDate('created_at', today())->count();

        $recentLinks = Link::forUser($userId)
            ->latest()
            ->limit(5)
            ->get(['id', 'short_code', 'destination_url', 'clicks_count', 'is_active', 'created_at']);

        return Inertia::render('Dashboard', [
            'stats' => [
                'total_links'  => $totalLinks,
                'active_links' => $activeLinks,
                'total_clicks' => $totalClicks,
                'clicks_today' => $clicksToday,
            ],
            'recentLinks' => $recentLinks,
        ]);
    }
}
