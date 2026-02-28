<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\ClientBundle;
use App\Models\PriceList;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $activePriceList = PriceList::where('is_active', true)->first();

        $totalClients    = Client::where('is_active', true)->count();
        $naturalClients  = Client::where('is_active', true)->where('type', 'natural')->count();
        $juridicaClients = Client::where('is_active', true)->where('type', 'juridica')->count();

        $clientsWithPrice   = 0;
        $clientsWithoutList = collect();

        if ($activePriceList) {
            $withPriceIds = Client::where('is_active', true)
                ->whereHas('prices', fn ($q) => $q->where('price_list_id', $activePriceList->id))
                ->pluck('id');

            $clientsWithPrice = $withPriceIds->count();

            $clientsWithoutList = Client::where('is_active', true)
                ->whereNotIn('id', $withPriceIds)
                ->orderBy('business_name')
                ->limit(10)
                ->get(['id', 'business_name', 'document_number', 'type']);
        }

        $activeBundles = ClientBundle::with(['client', 'bundleTier.serviceType'])
            ->where('is_active', true)
            ->get()
            ->map(fn ($bundle) => [
                'id'                  => $bundle->id,
                'client_id'           => $bundle->client_id,
                'client_name'         => $bundle->client->business_name,
                'service_name'        => $bundle->bundleTier->serviceType->name,
                'tier_name'           => $bundle->bundleTier->name,
                'quantity_purchased'  => $bundle->quantity_purchased,
                'quantity_consumed'   => $bundle->quantity_consumed,
                'quantity_remaining'  => $bundle->quantity_remaining,
                'consumption_percent' => $bundle->consumption_percentage,
                'price_paid'          => $bundle->price_paid,
            ]);

        $bundlesAtRisk = $activeBundles->filter(
            fn ($b) => $b['consumption_percent'] >= 90
        )->values();

        $totalBundleValue = $activeBundles->sum('price_paid');

        $recentActivity = ActivityLog::with('user:id,name')
            ->orderByDesc('created_at')
            ->limit(8)
            ->get(['id', 'user_id', 'action', 'module', 'description', 'created_at']);

        return Inertia::render('Dashboard/Index', [
            'stats' => [
                'total_clients'      => $totalClients,
                'natural_clients'    => $naturalClients,
                'juridica_clients'   => $juridicaClients,
                'clients_with_price' => $clientsWithPrice,
                'clients_without'    => $clientsWithoutList->count(),
                'active_bundles'     => $activeBundles->count(),
                'total_bundle_value' => $totalBundleValue,
            ],
            'activeBundles'      => $activeBundles,
            'bundlesAtRisk'      => $bundlesAtRisk,
            'activePriceList'    => $activePriceList,
            'clientsWithoutList' => $clientsWithoutList,
            'recentActivity'     => $recentActivity,
        ]);
    }
}
