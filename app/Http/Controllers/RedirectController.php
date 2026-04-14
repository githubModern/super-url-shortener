<?php
// © Atia Hegazy — atiaeno.com

namespace App\Http\Controllers;

use App\Models\Ad;
use App\Models\Link;
use App\Jobs\LogClickJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redirect;
use Jenssegers\Agent\Agent;

class RedirectController extends Controller
{
    /**
     * Handle short URL redirect.
     */
    public function __invoke(string $shortCode, Request $request)
    {
        // Try cache first (NFR-P1: <1s response)
        $destinationUrl = Cache::get("redirect:{$shortCode}");

        if (!$destinationUrl) {
            // Fallback to database
            $link = Link::where('short_code', $shortCode)
                ->orWhere('custom_alias', $shortCode)
                ->active()
                ->first();

            if (!$link) {
                abort(404, 'Link not found or expired.');
            }

            $destinationUrl = $link->destination_url;

            // Cache for next time (24h TTL per architecture)
            Cache::put("redirect:{$shortCode}", $destinationUrl, now()->addHours(24));
        } else {
            // Still need link ID for click logging
            $link = Link::where('short_code', $shortCode)
                ->orWhere('custom_alias', $shortCode)
                ->active()
                ->first();

            if (!$link) {
                abort(404, 'Link not found or expired.');
            }
        }

        // Story 1.7: Serve OG meta page for social crawlers instead of redirecting
        if ($this->isSocialCrawler($request->userAgent() ?? '')) {
            return response($this->buildOgPage($link, $destinationUrl), 200)
                ->header('Content-Type', 'text/html; charset=utf-8');
        }

        // Story 5.4: Check for ad display
        $ad = $this->getAdForLink($link, $request);
        
        if ($ad && $ad->format === 'interstitial' && !$this->hasSeenAd($request, $link->id)) {
            // Queue click logging
            LogClickJob::dispatch($link->id, $this->getClickData($request));
            $link->incrementClicks();
            
            // Mark ad as seen in session
            $this->markAdSeen($request, $link->id);
            
            // Show interstitial page
            return response($this->buildInterstitialPage($link, $destinationUrl, $ad), 200);
        }

        // Queue click logging (async, doesn't block redirect)
        LogClickJob::dispatch($link->id, $this->getClickData($request));

        // Increment click count (lightweight, doesn't block)
        $link->incrementClicks();

        // Story 5.4: If banner ad, pass to view or include in redirect
        // For now, redirect with banner data in session for frontend if needed
        if ($ad && $ad->format === 'banner') {
            session(['banner_ad' => $ad]);
        }

        return Redirect::away($destinationUrl, 302);
    }

    /**
     * Story 1.7: Detect social media crawlers by user-agent.
     */
    private function isSocialCrawler(string $ua): bool
    {
        $crawlers = [
            'facebookexternalhit', 'twitterbot', 'linkedinbot',
            'whatsapp', 'telegrambot', 'slackbot', 'discordbot',
            'googlebot', 'bingbot',
        ];

        $ua = strtolower($ua);

        foreach ($crawlers as $bot) {
            if (str_contains($ua, $bot)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Story 1.7: Build a minimal HTML page with OG/Schema meta tags for social crawlers.
     */
    private function buildOgPage(Link $link, string $destinationUrl): string
    {
        $shortUrl    = htmlspecialchars($link->short_url,    ENT_QUOTES, 'UTF-8');
        $destination = htmlspecialchars($destinationUrl,     ENT_QUOTES, 'UTF-8');
        $ogTitle     = htmlspecialchars($link->og_title     ?? 'Short Link', ENT_QUOTES, 'UTF-8');
        $ogDesc      = htmlspecialchars($link->og_description ?? "Visit {$destinationUrl}", ENT_QUOTES, 'UTF-8');
        $ogImage     = htmlspecialchars($link->og_image     ?? '', ENT_QUOTES, 'UTF-8');
        $appName     = htmlspecialchars(config('app.name', 'ShortLink'), ENT_QUOTES, 'UTF-8');

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>{$ogTitle}</title>
<meta http-equiv="refresh" content="0;url={$destination}">
<meta property="og:title"       content="{$ogTitle}">
<meta property="og:description" content="{$ogDesc}">
<meta property="og:url"         content="{$shortUrl}">
<meta property="og:type"        content="website">
{$this->ogImageTag($ogImage)}
<meta name="twitter:card"        content="summary_large_image">
<meta name="twitter:title"       content="{$ogTitle}">
<meta name="twitter:description" content="{$ogDesc}">
{$this->ogImageTag($ogImage, 'twitter')}
<script type="application/ld+json">
{"@context":"https://schema.org","@type":"WebPage","name":"{$ogTitle}","url":"{$shortUrl}","description":"{$ogDesc}","publisher":{"@type":"Organization","name":"{$appName}"}}
</script>
</head>
<body>Redirecting…</body>
</html>
HTML;
    }

    private function ogImageTag(string $url, string $prefix = 'og'): string
    {
        if (empty($url)) {
            return '';
        }

        return "<meta property=\"{$prefix}:image\" content=\"{$url}\">";
    }

    /**
     * Get click analytics data from request.
     */
    private function getClickData(Request $request): array
    {
        $agent = new Agent();
        $agent->setUserAgent($request->userAgent());

        $referrer = $request->headers->get('referer');
        $referrerDomain = $referrer ? parse_url($referrer, PHP_URL_HOST) : null;

        return [
            'ip_hash' => hash('sha256', $request->ip()),
            'country_code' => $this->getCountryCode($request->ip()),
            'device_type' => $agent->isMobile() ? 'mobile' : ($agent->isTablet() ? 'tablet' : 'desktop'),
            'browser' => $agent->browser(),
            'os' => $agent->platform(),
            'referrer' => $referrer,
            'referrer_domain' => $referrerDomain,
        ];
    }

    /**
     * Get country code from IP (using cache).
     */
    private function getCountryCode(?string $ip): ?string
    {
        if (!$ip) {
            return null;
        }

        return Cache::remember("geo:{$ip}", now()->addHours(24), function () use ($ip) {
            // TODO: Integrate with geolocation service (MaxMind, IP-API, etc.)
            // For now, return null or a default
            return null;
        });
    }

    /**
     * Story 5.4: Get appropriate ad for link based on override settings.
     */
    private function getAdForLink(Link $link, Request $request): ?Ad
    {
        // Check link override
        if ($link->ad_override === 'disable') {
            return null;
        }

        if ($link->ad_override === 'force' && $link->ad_id) {
            return Ad::active()->find($link->ad_id);
        }

        // Default: find active ad matching visitor country
        $countryCode = $this->getCountryCode($request->ip());
        
        return Ad::active()
            ->when($countryCode, fn ($q) => $q->forCountry($countryCode))
            ->inRandomOrder()
            ->first();
    }

    /**
     * Check if user has already seen an ad for this link in current session.
     */
    private function hasSeenAd(Request $request, int $linkId): bool
    {
        $seen = session('seen_ads', []);
        return in_array($linkId, $seen);
    }

    /**
     * Mark ad as seen for this link in current session.
     */
    private function markAdSeen(Request $request, int $linkId): void
    {
        $seen = session('seen_ads', []);
        $seen[] = $linkId;
        session(['seen_ads' => array_unique($seen)]);
    }

    /**
     * Build interstitial page with countdown.
     */
    private function buildInterstitialPage(Link $link, string $destinationUrl, Ad $ad): string
    {
        $destination = htmlspecialchars($destinationUrl, ENT_QUOTES, 'UTF-8');
        $shortCode = htmlspecialchars($link->short_code, ENT_QUOTES, 'UTF-8');
        $countdown = (int) $ad->countdown_seconds;
        $adContent = $ad->content ?? '';
        
        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redirecting...</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #0a0a0a;
            color: #fff;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .countdown {
            font-size: 48px;
            font-weight: 700;
            color: #22d3ee;
            margin-bottom: 20px;
        }
        .message {
            color: #a1a1aa;
            margin-bottom: 40px;
        }
        .ad-container {
            max-width: 800px;
            width: 100%;
            background: #141414;
            border-radius: 12px;
            padding: 40px;
            margin-bottom: 40px;
        }
        .skip-btn {
            padding: 12px 32px;
            background: #22d3ee;
            color: #0a0a0a;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            opacity: 0.5;
            pointer-events: none;
            transition: opacity 0.3s;
        }
        .skip-btn.active {
            opacity: 1;
            pointer-events: auto;
        }
        .skip-btn:hover.active {
            background: #06b6d4;
        }
    </style>
</head>
<body>
    <div class="countdown" id="countdown">{$countdown}</div>
    <p class="message">You'll be redirected in <span id="seconds">{$countdown}</span> seconds</p>
    <div class="ad-container">
        {$adContent}
    </div>
    <a href="{$destination}" class="skip-btn" id="skipBtn">Skip Ad</a>
    <script>
        let seconds = {$countdown};
        const countdownEl = document.getElementById('countdown');
        const secondsEl = document.getElementById('seconds');
        const skipBtn = document.getElementById('skipBtn');
        
        const timer = setInterval(() => {
            seconds--;
            countdownEl.textContent = seconds;
            secondsEl.textContent = seconds;
            
            if (seconds <= 0) {
                clearInterval(timer);
                countdownEl.style.display = 'none';
                skipBtn.classList.add('active');
                skipBtn.textContent = 'Continue to destination';
                window.location.href = '{$destination}';
            }
        }, 1000);
    </script>
</body>
</html>
HTML;
    }
}
