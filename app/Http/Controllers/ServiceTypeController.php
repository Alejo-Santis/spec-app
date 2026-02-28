<?php

namespace App\Http\Controllers;

use App\Http\Concerns\WithFlashMessage;
use App\Models\ServiceType;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ServiceTypeController extends Controller
{
    use WithFlashMessage;

    public function __construct(private readonly ActivityLogService $log) {}
    public function index(): Response
    {
        return Inertia::render('ServiceTypes/Index', [
            'serviceTypes' => ServiceType::orderBy('name')->get(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('ServiceTypes/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'           => ['required', 'string', 'max:255', 'unique:service_types,name'],
            'billing_type'   => ['required', 'in:unit,bundle'],
            'applies_iva'    => ['boolean'],
            'iva_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'description'    => ['nullable', 'string'],
            'is_active'      => ['boolean'],
        ]);

        $st = ServiceType::create($data);

        $this->log->log('created', 'service-types', "Tipo de servicio '{$st->name}' creado.", $st->id, $st->name);

        return redirect()->route('service-types.index')
            ->with(...$this->success('Tipo de servicio creado correctamente.'));
    }

    public function edit(ServiceType $serviceType): Response
    {
        return Inertia::render('ServiceTypes/Edit', [
            'serviceType' => $serviceType,
        ]);
    }

    public function update(Request $request, ServiceType $serviceType): RedirectResponse
    {
        $data = $request->validate([
            'name'           => ['required', 'string', 'max:255', 'unique:service_types,name,' . $serviceType->id],
            'billing_type'   => ['required', 'in:unit,bundle'],
            'applies_iva'    => ['boolean'],
            'iva_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'description'    => ['nullable', 'string'],
            'is_active'      => ['boolean'],
        ]);

        $serviceType->update($data);

        $this->log->log('updated', 'service-types', "Tipo de servicio '{$serviceType->name}' actualizado.", $serviceType->id, $serviceType->name);

        return redirect()->route('service-types.index')
            ->with(...$this->success('Tipo de servicio actualizado correctamente.'));
    }

    public function destroy(ServiceType $serviceType): RedirectResponse
    {
        $serviceType->update(['is_active' => false]);

        $this->log->log('deleted', 'service-types', "Tipo de servicio '{$serviceType->name}' desactivado.", $serviceType->id, $serviceType->name);

        return redirect()->route('service-types.index')
            ->with(...$this->success('Tipo de servicio desactivado correctamente.'));
    }
}
