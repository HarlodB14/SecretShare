<?php

namespace App\Http\Controllers;

use App\Contracts\SecretServiceInterface;
use App\Http\Requests\StoreSecretRequest;
use App\Models\Secret;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\View\View;

class SecretController extends Controller
{
    public function __construct(private readonly SecretServiceInterface $secretService)
    {
    }


    public function create(): View
    {
        return view('secret.create');
    }


    public function store(StoreSecretRequest $request): RedirectResponse
    {
        $secret = $this->secretService->create(
            password: $request->validated('password'),
            maxViews: $request->validated('max_views'),
            expiresAt: $request->validated('expires_at'),
        );

        return redirect()->route('secret.created', $secret->token);
    }

    public function created(string $token): View
    {
        return view('secret.created', [
            'link' => route('secret.show', $token),
        ]);
    }

    public function expired(): View|RedirectResponse
    {

        return view('secret.expired');
    }


    public function show(string $token): Response|RedirectResponse
    {
        $secret = Secret::where('token', $token)->firstOrFail();

        if (!$secret->isAccessible()) {
            $this->secretService->revoke($secret);

            return redirect()->route('secret.expired');
        }

        $password = $this->secretService->reveal($secret);

        $secondsUntilExpiry = $secret->expires_at
            ? ($secret->expires_at->getTimestamp() - now()->getTimestamp())
            : null;

        $response = response()->view('secret.show', [
            'password' => $password,
            'expiresAt' => $secret->expires_at?->toIso8601String(),
        ])->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0, private')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');

        if ($secondsUntilExpiry !== null && $secondsUntilExpiry > 0) {
            $response->header('Refresh', "{$secondsUntilExpiry};url=" . route('secret.expired'));
        }

        return $response;
    }
}
