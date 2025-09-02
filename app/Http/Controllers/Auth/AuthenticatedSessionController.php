<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        $autoLoginUsers = [];

        // Only for TEST
        if(app()->environment('local')) {
            $autoLoginUsers = $this->getAutoLoginUsers();
        }

        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
            'autoLoginUsers' => $autoLoginUsers,
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * Get auto login users for testing purposes.
     *
     * @return array
     */
    private function getAutoLoginUsers()
    {
        $shippingResponsePermissions = config('permissions.shipping_response_permissions', []);
        $query = User::select('email', 'name', 'org_id', 'post_id')
            ->where(function ($query) {
                $query->where('org_id', 1)
                    ->where('post_id', 2);
            });

        foreach ($shippingResponsePermissions as $orgId => $postIds) {
            $query->orWhere(function ($q) use ($orgId, $postIds) {
                $q->where('org_id', $orgId)
                    ->whereIn('post_id', $postIds);
            });
        }

        return $query->get();
    }
}
