document.addEventListener('DOMContentLoaded', () => {

    // ===== VALIDACIÓN DEL LOGIN =====
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', (e) => {
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value.trim();
            const btn = document.getElementById('btnLogin');

            if (!email || !password) {
                e.preventDefault();
                return;
            }
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Ingresando...';
            btn.disabled = true;
        });
    }

    // ===== CARRITO DE VENTAS =====
    const carritoBody = document.getElementById('carritoBody');
    
    if (carritoBody) {
        const carrito = [];
        const totalTexto = document.getElementById('totalTexto');
        const inputCarrito = document.getElementById('inputCarrito');
        const inputTotal = document.getElementById('inputTotal');
        const btnVender = document.getElementById('btnVender');
        const buscador = document.getElementById('buscarProducto');

        // Agregar productos al carrito
        document.querySelectorAll('.btn-agregar').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.dataset.id;
                const nombre = btn.dataset.nombre;
                const precio = parseFloat(btn.dataset.precio);
                const stockMax = parseInt(btn.dataset.stock);

                const existente = carrito.find(item => item.id == id);
                if (existente) {
                    if (existente.cantidad < stockMax) {
                        existente.cantidad++;
                    } else {
                        alert('⚠️ No hay más stock disponible de ' + nombre);
                        return;
                    }
                } else {
                    carrito.push({ id, nombre, precio, cantidad: 1, stockMax });
                }
                renderCarrito();
            });
        });

        function renderCarrito() {
            carritoBody.innerHTML = '';
            
            if (carrito.length === 0) {
                carritoBody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">Carrito vacío</td></tr>';
                totalTexto.textContent = '0.00';
                inputCarrito.value = '[]';
                inputTotal.value = '0';
                btnVender.disabled = true;
                return;
            }

            let total = 0;
            carrito.forEach((item, index) => {
                const subtotal = item.precio * item.cantidad;
                total += subtotal;
                carritoBody.innerHTML += `
                    <tr>
                        <td><small>${item.nombre}</small></td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-outline-danger py-0 px-1" onclick="cambiarCantidad(${index}, -1)">−</button>
                            <span class="mx-1">${item.cantidad}</span>
                            <button type="button" class="btn btn-sm btn-outline-success py-0 px-1" onclick="cambiarCantidad(${index}, 1)">+</button>
                        </td>
                        <td class="text-end">S/ ${subtotal.toFixed(2)}</td>
                        <td><button type="button" class="btn btn-sm btn-danger py-0 px-1" onclick="eliminarItem(${index})">×</button></td>
                    </tr>
                `;
            });

            totalTexto.textContent = total.toFixed(2);
            inputCarrito.value = JSON.stringify(carrito);
            inputTotal.value = total.toFixed(2);
            btnVender.disabled = false;
        }

        window.cambiarCantidad = (index, delta) => {
            const item = carrito[index];
            const nueva = item.cantidad + delta;
            if (nueva < 1) return;
            if (nueva > item.stockMax) {
                alert('⚠️ Solo hay ' + item.stockMax + ' unidades disponibles.');
                return;
            }
            item.cantidad = nueva;
            renderCarrito();
        };

        window.eliminarItem = (index) => {
            carrito.splice(index, 1);
            renderCarrito();
        };

        // Buscador
        if (buscador) {
            buscador.addEventListener('input', (e) => {
                const term = e.target.value.toLowerCase();
                document.querySelectorAll('.producto-item').forEach(item => {
                    item.style.display = item.dataset.nombre.includes(term) ? 'block' : 'none';
                });
            });
        }

        // Confirmar antes de enviar
        document.getElementById('formVenta').addEventListener('submit', (e) => {
            if (carrito.length === 0) {
                e.preventDefault();
                alert('El carrito está vacío.');
                return;
            }
            if (!confirm('¿Confirmar venta por S/ ' + totalTexto.textContent + '?')) {
                e.preventDefault();
            }
        });
    }

    // ===== AUTO-CERRAR ALERTAS =====
    setTimeout(() => {
        document.querySelectorAll('.alert-dismissible').forEach(alert => {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        });
    }, 5000);
});