<script>
  import { useForm } from '@inertiajs/svelte';
  import AppLayout from '../../Layouts/AppLayout.svelte';
  import ClientForm from '../../Components/ClientForm.svelte';

  const form = useForm({
    type: 'juridica',
    business_name: '',
    trade_name: '',
    document_number: '',
    dv: '',
    tax_regime: 'ordinario',
    tax_responsibilities: [],
    ciiu_code: '',
    email: '',
    email_billing: '',
    phone: '',
    mobile: '',
    address: '',
    city: '',
    department: '',
    country: 'CO',
    postal_code: '',
    is_active: true,
    notes: '',
  });

  function submit(e) {
    e.preventDefault();
    $form.post('/clients');
  }
</script>




<svelte:head><title>Nuevo cliente - SPEC</title></svelte:head>
<AppLayout>
  <div class="page-header">
    <div class="page-block">
      <div class="row align-items-center">
        <div class="col-md-12">
          <div class="page-header-title">
            <h5 class="m-0">Nuevo cliente</h5>
          </div>
          <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="/">Inicio</a></li>
            <li class="breadcrumb-item"><a href="/clients">Clientes</a></li>
            <li class="breadcrumb-item active">Nuevo</li>
          </ul>
        </div>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-header">
      <h5 class="mb-0"><i class="ti ti-user-plus me-2"></i>Registrar nuevo cliente</h5>
    </div>
    <div class="card-body">
      <ClientForm {form} onsubmit={submit} />
    </div>
  </div>
</AppLayout>
