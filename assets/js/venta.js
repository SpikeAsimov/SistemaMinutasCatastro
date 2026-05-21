document.addEventListener('DOMContentLoaded', function() {
    const selectMinuta = document.getElementById('minutaSelect');
    const selectTipo = document.getElementById('tipoPrecio');
    const totalSpan = document.getElementById('totalCalculado');
    const btnCF = document.getElementById('btnConsumidorFinal');
    const inputComprador = document.getElementById('comprador');

    function actualizarTotal() {
        const opcion = selectMinuta.options[selectMinuta.selectedIndex];
        if (!opcion.value) { totalSpan.textContent = '0.00'; return; }
        const normal = parseFloat(opcion.dataset.normal) || 0;
        const urgente = parseFloat(opcion.dataset.urgente) || 0;
        const precio = selectTipo.value === 'urgente' ? urgente : normal;
        totalSpan.textContent = precio.toFixed(2);
    }

    selectMinuta.addEventListener('change', actualizarTotal);
    selectTipo.addEventListener('change', actualizarTotal);

    btnCF.addEventListener('click', function() {
        inputComprador.value = 'Consumidor Final';
    });

    // Validación mínima antes de enviar (evitar enviar sin número)
    document.getElementById('formVenta').addEventListener('submit', function(e) {
        const numero = document.getElementById('numeroMinuta').value;
        if (!numero || parseInt(numero) < 1) {
            e.preventDefault();
            alert('Ingrese un número de minuta válido.');
        }
    });
});