<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     *
     * @param Request $request
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @param  Request              $request
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user() ?? null;
        $userId = $user->id ?? null;

        $notificationsCount = Cache::remember("user_{$userId}", now()->addHours(), function () use ($user) {
            return $user?->unreadNotifications->count() ?? 0;
        });

        return [
            ...parent::share($request),
            'auth' => [
                'user'               => $request->user(),
                'notificationsCount' => $notificationsCount,
            ],
            'flash' => [
                // Flash session variables -> !!! Vue plugin doesn't update the values, so we can check them in the components via {{ }} !!!
                'status'  => fn () => session('status'),
                'success' => fn () => session('success'),
                'errors'  => function () {
                    $errorBag = session('errors');

                    if ($errorBag) {
                        return $errorBag->toArray();
                    }

                    return [];
                },
                'error' => fn () => session('error'),
            ],
        ];
    }
}
