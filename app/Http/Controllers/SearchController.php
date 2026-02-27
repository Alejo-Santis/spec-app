<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $q = trim($request->get('q', ''));

        if (strlen($q) < 2) {
            return response()->json(['results' => []]);
        }

        $clients = Client::query()
            ->where(fn ($query) => $query
                ->where('business_name', 'ilike', "%{$q}%")
                ->orWhere('trade_name', 'ilike', "%{$q}%")
                ->orWhere('document_number', 'ilike', "%{$q}%")
                ->orWhere('email', 'ilike', "%{$q}%")
            )
            ->where('is_active', true)
            ->orderBy('business_name')
            ->limit(8)
            ->get(['id', 'business_name', 'trade_name', 'document_number', 'type', 'city']);

        $results = $clients->map(fn ($c) => [
            'type'     => 'client',
            'id'       => $c->id,
            'label'    => $c->business_name,
            'sublabel' => $c->document_number . ($c->city ? ' · ' . $c->city : ''),
            'badge'    => $c->type === 'juridica' ? 'Jurídica' : 'Natural',
            'badgeClass' => $c->type === 'juridica' ? 'bg-light-primary text-primary' : 'bg-light-secondary text-secondary',
            'url'      => '/clients/' . $c->id,
            'icon'     => $c->type === 'juridica' ? 'ti ti-building' : 'ti ti-user',
        ]);

        return response()->json(['results' => $results]);
    }
}
