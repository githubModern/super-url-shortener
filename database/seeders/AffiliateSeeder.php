<?php
// © Atia Hegazy — atiaeno.com

namespace Database\Seeders;

use App\Models\Affiliate;
use App\Models\AffiliateTier;
use App\Models\AffiliateCountryRate;
use App\Models\Payout;
use App\Models\PayoutAuditLog;
use App\Models\User;
use Illuminate\Database\Seeder;

class AffiliateSeeder extends Seeder
{
    public function run(): void
    {
        // Create affiliate tiers
        $bronze = AffiliateTier::create([
            'name' => 'Bronze',
            'visit_threshold' => 0,
            'commission_rate' => 1.50,
        ]);

        $silver = AffiliateTier::create([
            'name' => 'Silver',
            'visit_threshold' => 1000,
            'commission_rate' => 2.50,
        ]);

        $gold = AffiliateTier::create([
            'name' => 'Gold',
            'visit_threshold' => 10000,
            'commission_rate' => 4.00,
        ]);

        // Create country rates for Gold tier
        $premiumCountries = ['US', 'GB', 'CA', 'AU', 'DE', 'FR', 'JP'];
        foreach ($premiumCountries as $country) {
            AffiliateCountryRate::create([
                'affiliate_tier_id' => $gold->id,
                'country_code' => $country,
                'commission_rate' => 6.00, // 1.5x of base 4.00
            ]);
        }

        // Create affiliates
        $affiliateUser = User::where('email', 'affiliate@example.com')->first();
        $regularUser = User::where('email', 'john@example.com')->first();

        Affiliate::create([
            'user_id' => $affiliateUser->id,
            'tier_id' => $gold->id,
            'referral_code' => 'AFF001',
            'total_earnings' => 1250.50,
            'pending_earnings' => 150.00,
            'total_visits' => 50000,
            'is_active' => true,
        ]);

        Affiliate::create([
            'user_id' => $regularUser->id,
            'tier_id' => $bronze->id,
            'referral_code' => 'AFF002',
            'total_earnings' => 45.00,
            'pending_earnings' => 12.50,
            'total_visits' => 800,
            'is_active' => true,
        ]);

        // Create affiliate records first to get IDs
        $aff1 = Affiliate::where('referral_code', 'AFF001')->first();
        $aff2 = Affiliate::where('referral_code', 'AFF002')->first();

        // Create payout requests
        $payout = Payout::create([
            'affiliate_id' => $aff1->id,
            'amount' => 250.00,
            'status' => 'completed',
            'paypal_email' => 'affiliate@paypal.com',
            'processed_at' => now()->subDays(10),
            'processed_by' => 1,
        ]);

        // Add audit log for payout
        PayoutAuditLog::create([
            'payout_id' => $payout->id,
            'old_status' => 'pending',
            'new_status' => 'completed',
            'actor_id' => 1,
        ]);

        // Pending payout
        Payout::create([
            'affiliate_id' => $aff2->id,
            'amount' => 45.00,
            'status' => 'pending',
            'paypal_email' => 'john@paypal.com',
        ]);

        // Rejected payout
        Payout::create([
            'affiliate_id' => $aff1->id,
            'amount' => 500.00,
            'status' => 'rejected',
            'paypal_email' => 'invalid@email.com',
            'admin_note' => 'Invalid PayPal email address',
            'processed_at' => now()->subDays(5),
            'processed_by' => 1,
        ]);
    }
}
