<?php
// © Atia Hegazy — atiaeno.com

namespace App\Http\Controllers;

use App\Models\Affiliate;
use App\Models\AffiliateTier;
use App\Models\Payout;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class AffiliateController extends Controller
{
    /**
     * Story 4.3: Affiliate enrollment + Story 4.4: Earnings dashboard.
     */
    public function index(): Response
    {
        $user = Auth::user();
        $affiliate = Affiliate::with(['tier.countryRates', 'payouts' => fn ($q) => $q->latest()->limit(10)])
            ->where('user_id', $user->id)
            ->first();

        $tiers = AffiliateTier::active()->orderBy('visit_threshold')->get();

        $nextTier = $affiliate
            ? $tiers->where('visit_threshold', '>', $affiliate->total_visits)->first()
            : null;

        return Inertia::render('Affiliate/Dashboard', [
            'affiliate'  => $affiliate,
            'tiers'      => $tiers,
            'nextTier'   => $nextTier,
            'minPayout'  => (float) Setting::get('affiliate_min_payout', 50),
        ]);
    }

    /**
     * Story 4.3: Enroll user in affiliate program.
     */
    public function enroll(): RedirectResponse
    {
        $user = Auth::user();

        if (Affiliate::where('user_id', $user->id)->exists()) {
            return back()->with('error', 'You are already enrolled.');
        }

        $defaultTier = AffiliateTier::active()->orderBy('visit_threshold')->firstOrFail();

        Affiliate::create([
            'user_id'      => $user->id,
            'tier_id'      => $defaultTier->id,
            'referral_code' => Affiliate::generateReferralCode(),
        ]);

        return redirect()->route('affiliate.index')
            ->with('success', 'Welcome to the affiliate program!');
    }

    /**
     * Story 4.5: Submit payout request.
     */
    public function requestPayout(Request $request): RedirectResponse
    {
        $affiliate = Affiliate::where('user_id', Auth::id())->firstOrFail();

        $minPayout = (float) Setting::get('affiliate_min_payout', 50);

        if (! $affiliate->canRequestPayout($minPayout)) {
            return back()->with('error', "Minimum payout is \${$minPayout}.");
        }

        if (Payout::where('affiliate_id', $affiliate->id)->pending()->exists()) {
            return back()->with('error', 'You already have a pending payout request.');
        }

        $validated = $request->validate([
            'paypal_email' => ['required', 'email', 'max:255'],
        ]);

        Payout::create([
            'affiliate_id'  => $affiliate->id,
            'amount'        => $affiliate->pending_earnings,
            'status'        => Payout::STATUS_PENDING,
            'paypal_email'  => $validated['paypal_email'],
        ]);

        return back()->with('success', 'Payout request submitted. We will review it shortly.');
    }

    /**
     * Story 4.6: Payout history.
     */
    public function payouts(): Response
    {
        $affiliate = Affiliate::where('user_id', Auth::id())->firstOrFail();

        $payouts = Payout::with('processedBy:id,name')
            ->where('affiliate_id', $affiliate->id)
            ->latest()
            ->paginate(15);

        return Inertia::render('Affiliate/Payouts', [
            'affiliate' => $affiliate,
            'payouts'   => $payouts,
        ]);
    }
}
