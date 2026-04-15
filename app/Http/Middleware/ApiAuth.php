<?php
// © Atia Hegazy — atiaeno.com

namespace App\Http\Middleware;

use App\Models\ApiToken;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $this->extractToken($request);

        if (!$token) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'API token is required.',
            ], 401);
        }

        $apiToken = ApiToken::where('token', $token)
            ->valid()
            ->first();

        if (!$apiToken) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Invalid or expired API token.',
            ], 401);
        }

        // Update last used timestamp (throttled to once per minute)
        if ($apiToken->last_used_at === null || $apiToken->last_used_at->diffInMinutes(now()) >= 1) {
            $apiToken->markAsUsed();
        }

        // Set authenticated user
        auth()->setUser($apiToken->user);

        return $next($request);
    }

    private function extractToken(Request $request): ?string
    {
        // Check Authorization header (Bearer token)
        $header = $request->header('Authorization');
        if ($header && str_starts_with($header, 'Bearer ')) {
            return substr($header, 7);
        }

        // Check query parameter (for testing/debugging)
        if ($request->has('api_key')) {
            return $request->query('api_key');
        }

        return null;
    }
}
