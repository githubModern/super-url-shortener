<?php
// © Atia Hegazy — atiaeno.com

use App\Http\Controllers\Admin\AdController;
use App\Http\Controllers\Admin\AffiliateTierController;
use App\Http\Controllers\Admin\ModerationController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\PayoutController;
use App\Http\Controllers\AffiliateController;
use App\Http\Controllers\BulkLinkController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GuestLinkController;
use App\Http\Controllers\LinkController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\QrCodeController;
use App\Http\Controllers\RedirectController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\SocialAuthController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// ── Homepage ─────────────────────────────────────────────────────────────────
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin'        => Route::has('login'),
        'canRegister'     => Route::has('register'),
        'donationEnabled' => (bool) config('app.donation_enabled', false),
        'donationButtonId' => config('app.donation_button_id', ''),
    ]);
})->name('home');

// ── Story 1.4: Guest link shortening ─────────────────────────────────────────
Route::post('/guest/shorten', [GuestLinkController::class, 'store'])
    ->middleware('throttle:30,1')
    ->name('guest.shorten');

// ── Story 1.8: Sitemap & robots ───────────────────────────────────────────────
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/robots.txt', [SitemapController::class, 'robots'])->name('robots');

// ── Story 2.3: OAuth ──────────────────────────────────────────────────────────
Route::get('/auth/{provider}', [SocialAuthController::class, 'redirect'])->name('social.redirect');
Route::get('/auth/{provider}/callback', [SocialAuthController::class, 'callback'])->name('social.callback');

// ── Auth Routes (login, register, etc) ───────────────────────────────────────
require __DIR__.'/auth.php';

// ── Dashboard (must be before catch-all) ──────────────────────────────────────
Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Public redirect endpoint (short URLs) — MUST BE LAST
Route::get('/{shortCode}', RedirectController::class)
    ->where('shortCode', '[a-zA-Z0-9]+')
    ->middleware(\App\Http\Middleware\RateLimitRedirects::class)
    ->name('redirect');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/profile/avatar', [ProfileController::class, 'uploadAvatar'])->name('profile.avatar');

    // Story 2.8: OAuth disconnect
    Route::delete('/auth/{provider}/disconnect', [SocialAuthController::class, 'disconnect'])->name('social.disconnect');

    // Links management
    Route::resource('links', LinkController::class);

    // Story 3.4: QR code download
    Route::get('/links/{link}/qr/{format}', [QrCodeController::class, 'generate'])
        ->where('format', 'svg|png')
        ->name('links.qr');

    // Stories 3.7 & 3.8: Bulk shortening
    Route::get('/links/bulk', [BulkLinkController::class, 'index'])->name('links.bulk');
    Route::post('/links/bulk', [BulkLinkController::class, 'store'])->name('links.bulk.store');
    Route::post('/links/bulk/export', [BulkLinkController::class, 'export'])->name('links.bulk.export');

    // Affiliate program (user-facing)
    Route::prefix('affiliate')->name('affiliate.')->group(function () {
        Route::get('/', [AffiliateController::class, 'index'])->name('index');
        Route::post('/enroll', [AffiliateController::class, 'enroll'])->name('enroll');
        Route::post('/payout', [AffiliateController::class, 'requestPayout'])->name('payout.request');
        Route::get('/payouts', [AffiliateController::class, 'payouts'])->name('payouts');
    });

    // Admin routes
    Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function () {
        // Affiliate tiers (Stories 4.1 & 4.2)
        Route::get('affiliate-tiers', [AffiliateTierController::class, 'index'])->name('affiliate-tiers.index');
        Route::post('affiliate-tiers', [AffiliateTierController::class, 'store'])->name('affiliate-tiers.store');
        Route::patch('affiliate-tiers/{affiliateTier}', [AffiliateTierController::class, 'update'])->name('affiliate-tiers.update');
        Route::post('affiliate-tiers/{affiliateTier}/country-rates', [AffiliateTierController::class, 'syncCountryRates'])->name('affiliate-tiers.country-rates');

        // Payouts (Stories 4.7 & 4.8)
        Route::get('payouts', [PayoutController::class, 'index'])->name('payouts.index');
        Route::post('payouts/{payout}/approve', [PayoutController::class, 'approve'])->name('payouts.approve');
        Route::post('payouts/{payout}/reject', [PayoutController::class, 'reject'])->name('payouts.reject');
        Route::post('payouts/{payout}/mark-paid', [PayoutController::class, 'markPaid'])->name('payouts.mark-paid');
        Route::get('payouts/{payout}/audit-log', [PayoutController::class, 'auditLog'])->name('payouts.audit-log');

        // Ads (Stories 5.1 - 5.4)
        Route::get('ads', [\App\Http\Controllers\Admin\AdController::class, 'index'])->name('ads.index');
        Route::post('ads', [\App\Http\Controllers\Admin\AdController::class, 'store'])->name('ads.store');
        Route::patch('ads/{ad}', [\App\Http\Controllers\Admin\AdController::class, 'update'])->name('ads.update');
        Route::delete('ads/{ad}', [\App\Http\Controllers\Admin\AdController::class, 'destroy'])->name('ads.destroy');

        // Moderation (Stories 6.1 - 6.4)
        Route::get('moderation', [ModerationController::class, 'index'])->name('moderation.index');
        Route::post('moderation/reports/{report}/review', [ModerationController::class, 'review'])->name('moderation.review');
        Route::post('moderation/batch', [ModerationController::class, 'batchReview'])->name('moderation.batch');
        Route::get('moderation/activity-log', [ModerationController::class, 'activityLog'])->name('moderation.activity-log');

        // Settings (Stories 7.1 - 7.10)
        Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::post('settings', [SettingsController::class, 'update'])->name('settings.update');
        Route::post('settings/purge-cache', [SettingsController::class, 'purgeCache'])->name('settings.purge-cache');
        Route::get('settings/export', [SettingsController::class, 'export'])->name('settings.export');
        Route::post('settings/import', [SettingsController::class, 'import'])->name('settings.import');
        Route::get('settings/backup', [SettingsController::class, 'backup'])->name('settings.backup');

        // User Management (Stories 8.1 - 8.3)
        Route::get('users', [UserController::class, 'index'])->name('users.index');
        Route::post('users/{user}/role', [UserController::class, 'updateRole'])->name('users.role');
        Route::post('users/{user}/ban', [UserController::class, 'ban'])->name('users.ban');
        Route::post('users/{user}/unban', [UserController::class, 'unban'])->name('users.unban');
        Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });
    
    // Link reporting (Story 6.1 - public endpoint)
    Route::post('links/{link}/report', [ReportController::class, 'store'])->name('links.report');
});
