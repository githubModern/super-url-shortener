<?php
// © Atia Hegazy — atiaeno.com

use App\Http\Controllers\Admin\AffiliateTierController;
use App\Http\Controllers\Admin\PayoutController;
use App\Http\Controllers\AffiliateController;
use App\Http\Controllers\BulkLinkController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GuestLinkController;
use App\Http\Controllers\LinkController;
use App\Http\Controllers\ProfileController;
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

// Public redirect endpoint (short URLs)
Route::get('/{shortCode}', RedirectController::class)
    ->where('shortCode', '[a-zA-Z0-9]+')
    ->middleware(\App\Http\Middleware\RateLimitRedirects::class)
    ->name('redirect');

Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

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
    });
});

require __DIR__.'/auth.php';
