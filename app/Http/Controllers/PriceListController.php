<?php

namespace App\Http\Controllers;

use App\Http\Concerns\WithFlashMessage;
use App\Models\PriceList;
use App\Models\ServiceType;
use App\Services\ActivityLogService;
use App\Services\PriceListGeneratorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PriceListController extends Controller
{
    use WithFlashMessage;

    public function __construct(private ActivityLogService $activity) {}

    public function index(): Response
    {
        $priceLists = PriceList::withCount('clientPrices')
            ->orderByDesc('year')
            ->get();

        return Inertia::render('PriceLists/Index', [
            'priceLists' => $priceLists,
        ]);
    }

    public function create(): Response
    {
        $previousLists = PriceList::orderByDesc('year')->get(['id', 'year', 'name']);

        return Inertia::render('PriceLists/Create', [
            'previousLists' => $previousLists,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'year'                  => ['required', 'integer', 'min:2000', 'max:2100', 'unique:price_lists,year'],
            'name'                  => ['required', 'string', 'max:255'],
            'adjustment_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'notes'                 => ['nullable', 'string'],
        ]);

        $priceList = PriceList::create($data);

        $this->activity->log(
            action: 'created',
            module: 'PriceList',
            description: "Lista de precios creada: {$priceList->name}",
            subjectId: $priceList->id,
            subjectLabel: $priceList->name,
            properties: ['year' => $priceList->year, 'adjustment_percentage' => $priceList->adjustment_percentage],
        );

        return redirect()->route('price-lists.show', $priceList)
            ->with(...$this->success('Lista de precios creada correctamente.'));
    }

    public function show(PriceList $priceList): Response
    {
        $priceList->load([
            'bundleTiers.serviceType',
            'clientPrices.client',
            'clientPrices.serviceType',
        ]);

        $previousLists = PriceList::where('id', '!=', $priceList->id)
            ->orderByDesc('year')
            ->get(['id', 'year', 'name']);

        $bundleServiceTypes = ServiceType::where('billing_type', 'bundle')
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('PriceLists/Show', [
            'priceList'          => $priceList,
            'previousLists'      => $previousLists,
            'bundleServiceTypes' => $bundleServiceTypes,
        ]);
    }

    public function generateFromPrevious(Request $request, PriceList $priceList, PriceListGeneratorService $generator): RedirectResponse
    {
        $request->validate([
            'previous_price_list_id' => ['required', 'exists:price_lists,id'],
        ]);

        $previousList = PriceList::findOrFail($request->previous_price_list_id);

        $generator->generateFromPrevious($priceList, $previousList);

        $this->activity->log(
            action: 'created',
            module: 'PriceList',
            description: "Precios generados en {$priceList->name} desde {$previousList->name}",
            subjectId: $priceList->id,
            subjectLabel: $priceList->name,
            properties: ['desde' => $previousList->name],
        );

        return redirect()->route('price-lists.show', $priceList)
            ->with(...$this->success('Precios generados desde la lista anterior correctamente.'));
    }

    public function activate(PriceList $priceList): RedirectResponse
    {
        PriceList::where('is_active', true)->update(['is_active' => false]);
        $priceList->update(['is_active' => true]);

        $this->activity->log(
            action: 'activated',
            module: 'PriceList',
            description: "Lista activada: {$priceList->name}",
            subjectId: $priceList->id,
            subjectLabel: $priceList->name,
        );

        return redirect()->route('price-lists.show', $priceList)
            ->with(...$this->success("Lista {$priceList->name} activada correctamente."));
    }
}
