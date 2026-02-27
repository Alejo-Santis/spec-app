<?php

namespace App\Http\Middleware;

use App\Models\PriceList;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user() ? [
                    'id'    => $request->user()->id,
                    'name'  => $request->user()->name,
                    'email' => $request->user()->email,
                    'roles' => $request->user()->getRoleNames(),
                ] : null,
            ],
            'activePriceList' => fn () => PriceList::where('is_active', true)
                ->select('id', 'year', 'name')
                ->first(),
            'flash'  => fn () => $request->session()->get('message'),
            'ziggy'  => fn () => [...(new Ziggy)->toArray(), 'location' => $request->url()],
        ];
    }
}
