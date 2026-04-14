<?php
// © Atia Hegazy — atiaeno.com

namespace App\Http\Controllers;

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

        // Queue click logging (async, doesn't block redirect)
        LogClickJob::dispatch($link->id, $this->getClickData($request));

        // Increment click count (lightweight, doesn't block)
        $link->incrementClicks();

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
}
