document.addEventListener('DOMContentLoaded', function() {
    const minutaSelect = document.getElementById('minutaSelect');
    const numeroMinutaInput = document.getElementById('numeroMinuta');
    const tipoPrecioSelect = document.getElementById('tipoPrecio');
    const btnAgregar = document.getElementById('btnAgregarItem');
    const tablaCarrito = document.getElementById('tablaCarrito')?.querySelector('tbody');
    const totalSpan = document.getElementById('totalCarrito');
    const btnConsumidorFinal = document.getElementById('btnConsumidorFinal');
    const compradorNombre = document.getElementById('compradorNombre');
    const observaciones = document.getElementById('observaciones');
    const formaPago = document.getElementById('formaPago');
    const btnGuardar = document.getElementById('btnGuardarVenta');
    const modalVenta = document.getElementById('modalVenta');

    if (!minutaSelect || !numeroMinutaInput || !tipoPrecioSelect || !btnAgregar || !tablaCarrito || !totalSpan || !formaPago || !btnGuardar) {
        return;
    }

    let carrito = Array.isArray(window.ventaFormData?.carrito)
        ? window.ventaFormData.carrito.map(item => ({ ...item, precio: Number(item.precio) || 0 }))
        : [];

    function money(value) {
        return '$' + Number(value || 0).toFixed(2);
    }

    function notify(icon, title, text) {
        Swal.fire({
            icon: icon,
            title: title,
            text: text,
            confirmButtonColor: '#950606'
        });
    }

    function actualizarTotal() {
        const total = carrito.reduce((sum, item) => sum + item.precio, 0);
        totalSpan.textContent = money(total);
    }

    function cell(text, className) {
        const td = document.createElement('td');
        td.textContent = text;
        if (className) {
            td.className = className;
        }
        return td;
    }

    function renderCarrito() {
        tablaCarrito.replaceChildren();

        if (carrito.length === 0) {
            const row = document.createElement('tr');
            const td = cell('El carrito esta vacio.', 'text-center text-muted py-4');
            td.colSpan = 5;
            row.appendChild(td);
            tablaCarrito.appendChild(row);
            actualizarTotal();
            return;
        }

        carrito.forEach((item, index) => {
            const row = document.createElement('tr');
            row.appendChild(cell(item.nombreMinuta));
            row.appendChild(cell(item.numero));
            row.appendChild(cell(item.tipo === 'urgente' ? 'Urgente' : 'Comun'));
            row.appendChild(cell(money(item.precio), 'text-end money'));

            const actions = cell('', 'text-end');
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'btn btn-sm btn-outline-danger btnEliminar';
            button.dataset.index = index;
            button.setAttribute('data-bs-toggle', 'tooltip');
            button.title = 'Quitar item';
            button.innerHTML = '<i class="fa-solid fa-trash"></i>';
            actions.appendChild(button);
            row.appendChild(actions);
            tablaCarrito.appendChild(row);
        });

        tablaCarrito.querySelectorAll('.btnEliminar').forEach(btn => {
            btn.addEventListener('click', function() {
                const idx = parseInt(this.dataset.index, 10);
                Swal.fire({
                    icon: 'question',
                    title: 'Quitar item',
                    text: 'Esta minuta se quitara del carrito.',
                    showCancelButton: true,
                    confirmButtonText: 'Si, quitar',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#950606',
                    cancelButtonColor: '#6c757d'
                }).then(result => {
                    if (result.isConfirmed) {
                        carrito.splice(idx, 1);
                        renderCarrito();
                    }
                });
            });
        });

        actualizarTotal();
    }

    btnAgregar.addEventListener('click', function() {
        const minutaId = minutaSelect.value;
        const numero = parseInt(numeroMinutaInput.value, 10);
        const tipo = tipoPrecioSelect.value;

        if (!minutaId || Number.isNaN(numero) || numero < 1) {
            notify('warning', 'Datos incompletos', 'Seleccione una minuta y un numero valido.');
            return;
        }

        const duplicado = carrito.some(item => item.minutaId === minutaId && item.numero === numero);
        if (duplicado) {
            notify('warning', 'Minuta duplicada', 'Esa minuta con ese numero ya esta en el carrito.');
            return;
        }

        const option = minutaSelect.options[minutaSelect.selectedIndex];
        const precioNormal = parseFloat(option.dataset.normal) || 0;
        const precioUrgente = parseFloat(option.dataset.urgente) || 0;
        const precio = tipo === 'urgente' ? precioUrgente : precioNormal;

        carrito.push({
            minutaId: minutaId,
            numero: numero,
            tipo: tipo,
            precio: precio,
            nombreMinuta: option.dataset.nombre || option.textContent.trim()
        });

        numeroMinutaInput.value = '';
        minutaSelect.selectedIndex = 0;
        tipoPrecioSelect.value = 'normal';
        numeroMinutaInput.focus();
        renderCarrito();
    });

    btnConsumidorFinal?.addEventListener('click', function() {
        compradorNombre.value = 'Consumidor Final';
        compradorNombre.dispatchEvent(new Event('input'));
    });

    btnGuardar.addEventListener('click', function() {
        if (carrito.length === 0) {
            notify('warning', 'Carrito vacio', 'Agregue al menos una minuta al carrito.');
            return;
        }

        if (!formaPago.value) {
            notify('warning', 'Forma de pago pendiente', 'Seleccione efectivo o transferencia / depósito bancario.');
            formaPago.focus();
            return;
        }

        btnGuardar.disabled = true;
        btnGuardar.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Guardando';

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'index.php?page=ventas&action=store';
        form.className = 'd-none';

        form.appendChild(crearInput('comprador_nombre', compradorNombre.value.trim() || 'Consumidor Final'));
        form.appendChild(crearInput('observaciones', observaciones.value.trim()));
        form.appendChild(crearInput('forma_pago', formaPago.value));

        carrito.forEach(item => {
            form.appendChild(crearInput('items_minuta_id[]', item.minutaId));
            form.appendChild(crearInput('items_numero[]', item.numero));
            form.appendChild(crearInput('items_tipo[]', item.tipo));
            form.appendChild(crearInput('items_precio[]', item.precio));
        });

        document.body.appendChild(form);
        form.submit();
    });

    function crearInput(name, value) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = name;
        input.value = value;
        return input;
    }

    modalVenta?.addEventListener('hidden.bs.modal', function() {
        carrito = [];
        renderCarrito();
        compradorNombre.value = '';
        observaciones.value = '';
        formaPago.value = '';
        numeroMinutaInput.value = '';
        minutaSelect.selectedIndex = 0;
        tipoPrecioSelect.value = 'normal';
    });

    renderCarrito();

    if (window.ventaFormData?.reabrir && modalVenta) {
        bootstrap.Modal.getOrCreateInstance(modalVenta).show();
    }
});
