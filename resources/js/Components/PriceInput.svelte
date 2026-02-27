<script>
  /**
   * Input que formatea valores en COP al escribir.
   * bind:value recibe/emite el número crudo (float).
   */
  let {
    value = $bindable(0),
    id = '',
    name = '',
    placeholder = '$ 0',
    disabled = false,
    class: cls = '',
    required = false,
  } = $props();

  let display = $state('');

  function formatCop(num) {
    if (!num && num !== 0) return '';
    return '$ ' + Number(num).toLocaleString('es-CO', { minimumFractionDigits: 0 });
  }

  function parseRaw(str) {
    return parseFloat(str.replace(/[^0-9.]/g, '')) || 0;
  }

  $effect(() => {
    display = formatCop(value);
  });

  function handleInput(e) {
    const raw = parseRaw(e.target.value);
    value = raw;
    display = formatCop(raw);
  }

  function handleBlur(e) {
    display = formatCop(value);
  }

  function handleFocus(e) {
    display = value ? String(value) : '';
  }
</script>

<input
  {id}
  {name}
  type="text"
  class="form-control {cls}"
  {placeholder}
  {disabled}
  {required}
  value={display}
  oninput={handleInput}
  onblur={handleBlur}
  onfocus={handleFocus}
  inputmode="numeric"
/>
