<?php
// © Atia Hegazy — atiaeno.com

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class SitemapController extends Controller
{
    /**
     * Story 1.8: Generate and return the XML sitemap.
     */
    public function index(): Response
    {
        $sitemap = Sitemap::create()
            ->add(Url::create('/')
                ->setChangeFrequency('weekly')
                ->setPriority(1.0))
            ->add(Url::create('/login')
                ->setChangeFrequency('monthly')
                ->setPriority(0.5))
            ->add(Url::create('/register')
                ->setChangeFrequency('monthly')
                ->setPriority(0.6));

        return response($sitemap->render(), 200, [
            'Content-Type' => 'application/xml',
        ]);
    }

    /**
     * Story 1.8: Return robots.txt.
     */
    public function robots(): Response
    {
        $content = "User-agent: *\nAllow: /\nDisallow: /dashboard\nDisallow: /links\nDisallow: /admin\nSitemap: " . url('/sitemap.xml');

        return response($content, 200, [
            'Content-Type' => 'text/plain',
        ]);
    }
}
