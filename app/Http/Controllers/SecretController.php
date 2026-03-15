<?php

namespace App\Http\Controllers;

use App\Contracts\SecretServiceInterface;
use App\Http\Requests\StoreSecretRequest;
use App\Models\Secret;
use Illuminate\View\View;

class SecretController extends Controller
{
    public function __construct(private readonly SecretServiceInterface $secretService) {}


    public function create(): View
    {
        return view('secret.create');
    }


    public function store(StoreSecretRequest $request): View
    {
        $secret = $this->secretService->create(
            password:  $request->validated('password'),
            maxViews:  $request->validated('max_views'),
            expiresAt: $request->validated('expires_at'),
        );

        return view('secret.created', [
            'link' => route('secret.show', $secret->token),
        ]);
    }


    public function show(string $token): View
    {
        $secret = Secret::where('token', $token)->firstOrFail();

        if (! $secret->isAccessible()) {
            $secret->delete();

            return view('secret.expired');
        }

        $password = $this->secretService->reveal($secret);

        return view('secret.show', compact('password'));
    }
}
