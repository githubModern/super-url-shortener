<?php
// © Atia Hegazy — atiaeno.com

namespace Database\Seeders;

use App\Models\Link;
use App\Models\Click;
use App\Models\User;
use Illuminate\Database\Seeder;

class LinkSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::where('ban_type', 'none')->get();
        $domains = [
            'https://example.com/article/',
            'https://news.site/story/',
            'https://blog.io/post/',
            'https://tech.dev/guide/',
            'https://shop.store/product/',
        ];

        $campaigns = ['summer-promo', 'winter-sale', 'newsletter', 'social', 'referral'];
        $countries = ['US', 'GB', 'CA', 'AU', 'DE', 'FR', 'BR', 'IN', 'JP', 'NG'];
        $browsers = ['Chrome', 'Safari', 'Firefox', 'Edge', 'Opera'];
        $devices = ['desktop', 'mobile', 'tablet'];

        // Create 20 links with analytics
        for ($i = 1; $i <= 20; $i++) {
            $user = $users->random();
            $shortCode = $this->generateShortCode();
            $domain = $domains[array_rand($domains)];

            $link = Link::create([
                'user_id' => $user->id,
                'short_code' => $shortCode,
                'destination_url' => $domain . 'page-' . $i,
                'custom_alias' => $i <= 5 ? 'custom-' . $i : null,
                'campaign_tag' => $i <= 10 ? $campaigns[array_rand($campaigns)] : null,
                'og_title' => $i <= 8 ? 'Amazing Article #' . $i : null,
                'og_description' => $i <= 8 ? 'Read this incredible story about topic ' . $i : null,
                'is_active' => $i <= 18, // 2 inactive links
                'clicks_count' => 0,
            ]);

            // Generate 50-200 clicks per link
            $clickCount = rand(50, 200);
            for ($j = 0; $j < $clickCount; $j++) {
                Click::create([
                    'link_id' => $link->id,
                    'ip_hash' => hash('sha256', '192.168.1.' . rand(1, 255)),
                    'country_code' => $countries[array_rand($countries)],
                    'device_type' => $devices[array_rand($devices)],
                    'browser' => $browsers[array_rand($browsers)],
                    'os' => ['Windows', 'macOS', 'Linux', 'iOS', 'Android'][array_rand(['Windows', 'macOS', 'Linux', 'iOS', 'Android'])],
                    'referrer_domain' => ['google.com', 'facebook.com', 'twitter.com', 'linkedin.com', null][array_rand(['google.com', 'facebook.com', 'twitter.com', 'linkedin.com', null])],
                    'created_at' => now()->subDays(rand(0, 30))->subHours(rand(0, 23)),
                ]);
            }

            // Update click count
            $link->update(['clicks_count' => $clickCount]);
        }

        // Create a few guest links (no user)
        for ($i = 21; $i <= 25; $i++) {
            Link::create([
                'user_id' => null,
                'short_code' => $this->generateShortCode(),
                'destination_url' => 'https://example.com/guest-page-' . $i,
                'is_active' => true,
                'clicks_count' => rand(10, 50),
            ]);
        }
    }

    private function generateShortCode(): string
    {
        $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $code = '';
        for ($i = 0; $i < 6; $i++) {
            $code .= $chars[rand(0, strlen($chars) - 1)];
        }
        return $code;
    }
}
