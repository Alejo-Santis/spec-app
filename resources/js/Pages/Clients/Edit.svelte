<script>
  import { useForm } from '@inertiajs/svelte';
  import AppLayout from '../../Layouts/AppLayout.svelte';
  import ClientForm from '../../Components/ClientForm.svelte';

  let { client } = $props();

  const form = useForm({
    type:                 client.type,
    business_name:        client.business_name,
    trade_name:           client.trade_name ?? '',
    document_number:      client.document_number,
    dv:                   client.dv ?? '',
    tax_regime:           client.tax_regime,
    tax_responsibilities: client.tax_responsibilities ?? [],
    ciiu_code:            client.ciiu_code ?? '',
    email:                client.email ?? '',
    email_billing:        client.email_billing ?? '',
    phone:                client.phone ?? '',
    mobile:               client.mobile ?? '',
    address:              client.address ?? '',
    city:                 client.city ?? '',
    department:           client.department ?? '',
    country:              client.country ?? 'CO',
    postal_code:          client.postal_code ?? '',
    is_active:            client.is_active,
    notes:                client.notes ?? '',
  });

  function submit(e) {
    e.preventDefault();
    $form.put(`/clients/${client.id}`);
  }
</script>

<AppLayout>
  <div class="page-header">
    <div class="page-block">
      <div class="row align-items-center">
        <div class="col-md-12">
          <div class="page-header-title">
            <h5 class="m-0">Editar cliente</h5>
          </div>
          <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="/">Inicio</a></li>
            <li class="breadcrumb-item"><a href="/clients">Clientes</a></li>
            <li class="breadcrumb-item"><a href="/clients/{client.id}">{client.business_name}</a></li>
            <li class="breadcrumb-item active">Editar</li>
          </ul>
        </div>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-header">
      <h5 class="mb-0">
        <i class="ti ti-pencil me-2"></i>Editando: {client.business_name}
      </h5>
    </div>
    <div class="card-body">
      <ClientForm {form} onsubmit={submit} />
    </div>
  </div>
</AppLayout>
