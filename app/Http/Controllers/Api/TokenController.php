<?php
// © Atia Hegazy — atiaeno.com

namespace App\Http\Controllers\Api;

use App\Models\ApiToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class TokenController extends ApiController
{
    /**
     * List all API tokens for the authenticated user.
     * GET /api/v1/tokens
     */
    public function index(): \Illuminate\Http\JsonResponse
    {
        $tokens = ApiToken::where('user_id', auth()->id())
            ->select('id', 'name', 'last_used_at', 'expires_at', 'created_at')
            ->orderByDesc('created_at')
            ->get();

        return $this->success($tokens);
    }

    /**
     * Create a new API token.
     * POST /api/v1/tokens
     */
    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => ['nullable', 'string', 'max:255'],
            'expires_days' => ['nullable', 'integer', 'min:1', 'max:365'],
        ]);

        if ($validator->fails()) {
            return $this->error('Validation failed', 422, $validator->errors()->toArray());
        }

        $token = Str::random(64);

        $apiToken = ApiToken::create([
            'user_id' => auth()->id(),
            'token' => $token,
            'name' => $request->input('name', 'API Token'),
            'expires_at' => $request->has('expires_days')
                ? now()->addDays($request->input('expires_days'))
                : null,
        ]);

        // Return the plain text token only once
        return $this->success([
            'id' => $apiToken->id,
            'name' => $apiToken->name,
            'token' => $token, // Only shown once
            'expires_at' => $apiToken->expires_at?->toIso8601String(),
            'created_at' => $apiToken->created_at->toIso8601String(),
        ], 'API token created successfully. Store this token securely - it will not be shown again.', 201);
    }

    /**
     * Revoke an API token.
     * DELETE /api/v1/tokens/{id}
     */
    public function destroy(int $id): \Illuminate\Http\JsonResponse
    {
        $token = ApiToken::where('id', $id)
            ->where('user_id', auth()->id())
            ->first();

        if (!$token) {
            return $this->error('Token not found', 404);
        }

        $token->delete();

        return $this->success([], 'Token revoked successfully');
    }
}
