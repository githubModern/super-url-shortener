<?php
// © Atia Hegazy — atiaeno.com

namespace Database\Seeders;

use App\Models\Ad;
use Illuminate\Database\Seeder;

class AdSeeder extends Seeder
{
    public function run(): void
    {
        // Banner ads
        Ad::create([
            'name' => 'Hosting Banner - Global',
            'format' => 'banner',
            'content' => '<a href="https://hosting.com" target="_blank"><img src="https://via.placeholder.com/728x90/22D3EE/FFFFFF?text=Premium+Hosting" alt="Hosting" style="max-width:100%;"></a>',
            'target_url' => 'https://hosting.com',
            'target_countries' => null, // All countries
            'countdown_seconds' => 5,
            'is_active' => true,
        ]);

        Ad::create([
            'name' => 'VPN Service - US/CA/UK Only',
            'format' => 'banner',
            'content' => '<a href="https://vpn.com" target="_blank"><img src="https://via.placeholder.com/728x90/8B5CF6/FFFFFF?text=Secure+VPN" alt="VPN" style="max-width:100%;"></a>',
            'target_url' => 'https://vpn.com',
            'target_countries' => ['US', 'CA', 'GB'],
            'countdown_seconds' => 5,
            'is_active' => true,
        ]);

        // Interstitial ads
        Ad::create([
            'name' => 'Mobile App - Interstitial',
            'format' => 'interstitial',
            'content' => '<div style="text-align:center;"><h2>Try Our New App!</h2><p>Download now and get 50% off premium features.</p><a href="https://app.com/download" style="display:inline-block;padding:12px 24px;background:#22D3EE;color:#000;text-decoration:none;border-radius:8px;font-weight:bold;">Download Now</a></div>',
            'target_url' => 'https://app.com/download',
            'target_countries' => null,
            'countdown_seconds' => 5,
            'is_active' => true,
        ]);

        Ad::create([
            'name' => 'E-book Offer - Interstitial',
            'format' => 'interstitial',
            'content' => '<div style="text-align:center;"><h2>Free E-book Download</h2><p>Get our exclusive guide on digital marketing.</p><a href="https://ebook.com" style="display:inline-block;padding:12px 24px;background:#F59E0B;color:#000;text-decoration:none;border-radius:8px;font-weight:bold;">Get Free E-book</a></div>',
            'target_url' => 'https://ebook.com',
            'target_countries' => ['US', 'GB', 'AU'],
            'countdown_seconds' => 8,
            'is_active' => true,
        ]);

        // Inactive ad
        Ad::create([
            'name' => 'Expired Campaign',
            'format' => 'banner',
            'content' => '<div>Expired</div>',
            'target_url' => null,
            'target_countries' => null,
            'countdown_seconds' => 5,
            'is_active' => false,
        ]);
    }
}
