<?php

namespace App\Http\Controllers;

use App\Http\Concerns\WithFlashMessage;
use App\Models\BundleTier;
use App\Models\PriceList;
use App\Models\ServiceType;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BundleTierController extends Controller
{
    use WithFlashMessage;

    public function __construct(private readonly ActivityLogService $log) {}

    public function create(PriceList $priceList): Response
    {
        return Inertia::render('BundleTiers/Create', [
            'priceList'    => $priceList,
            'serviceTypes' => ServiceType::where('billing_type', 'bundle')
                ->where('is_active', true)
                ->get(),
        ]);
    }

    public function store(Request $request, PriceList $priceList): RedirectResponse
    {
        $data = $request->validate([
            'service_type_id' => ['required', 'exists:service_types,id'],
            'name'            => ['required', 'string', 'max:255'],
            'quantity'        => ['required', 'integer', 'min:1'],
            'price'           => ['required', 'numeric', 'min:0'],
            'is_active'       => ['boolean'],
        ]);

        $tier = $priceList->bundleTiers()->create($data);

        $this->log->log('created', 'bundle-tiers', "Tier '{$tier->name}' creado en lista '{$priceList->name}'.", $tier->id, $tier->name, [
            'price_list' => $priceList->name,
            'quantity'   => $tier->quantity,
            'price'      => $tier->price,
        ]);

        return redirect()->route('price-lists.show', $priceList)
            ->with(...$this->success('Tier de bolsa creado correctamente.'));
    }

    public function edit(BundleTier $bundleTier): Response
    {
        return Inertia::render('BundleTiers/Edit', [
            'bundleTier'   => $bundleTier->load('priceList', 'serviceType'),
            'serviceTypes' => ServiceType::where('billing_type', 'bundle')
                ->where('is_active', true)
                ->get(),
        ]);
    }

    public function update(Request $request, BundleTier $bundleTier): RedirectResponse
    {
        $data = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'quantity'  => ['required', 'integer', 'min:1'],
            'price'     => ['required', 'numeric', 'min:0'],
            'is_active' => ['boolean'],
        ]);

        $bundleTier->update($data);

        $this->log->log('updated', 'bundle-tiers', "Tier '{$bundleTier->name}' actualizado.", $bundleTier->id, $bundleTier->name);

        return redirect()->route('price-lists.show', $bundleTier->price_list_id)
            ->with(...$this->success('Tier de bolsa actualizado correctamente.'));
    }

    public function destroy(BundleTier $bundleTier): RedirectResponse
    {
        $priceListId = $bundleTier->price_list_id;
        $name        = $bundleTier->name;

        $this->log->log('deleted', 'bundle-tiers', "Tier '{$name}' eliminado.", $bundleTier->id, $name);

        $bundleTier->delete();

        return redirect()->route('price-lists.show', $priceListId)
            ->with(...$this->success('Tier de bolsa eliminado correctamente.'));
    }
}
