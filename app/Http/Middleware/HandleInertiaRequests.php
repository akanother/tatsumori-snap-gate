<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Illuminate\Support\Str;

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
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        if(! is_null($user)) {

            $user->load('organization', 'post');

        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            'config' => [
                'appEnv' => config('app.env'),
                'appName' => config('app.name'),
                'appUrl' => config('app.url'),
            ],
        ];
    }
}
