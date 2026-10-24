document.addEventListener('DOMContentLoaded', () => {
    const catalogElement = document.getElementById('pos-catalog-data');
    const page = document.querySelector('[data-pos-checkout-url]');
    const checkoutForm = document.getElementById('pos-checkout-form');

    if (!catalogElement || !page || !checkoutForm) return;

    const products = new Map(JSON.parse(catalogElement.textContent).map((product) => [Number(product.id), product]));
    const cart = new Map();
    const currency = new Intl.NumberFormat('es-PE', { style: 'currency', currency: 'PEN' });
    const productModalElement = document.getElementById('pos-product-modal');
    const productModal = bootstrap.Modal.getOrCreateInstance(productModalElement);
    const detailQuantity = document.getElementById('pos-detail-quantity');
    let activeProduct = null;

    const showStatus = (message) => {
        Swal.fire({
            icon: 'error',
            title: 'No se pudo completar la acción',
            text: message,
            confirmButtonText: 'Entendido',
        });
    };

    const cartQuantity = (productId) => cart.get(productId)?.quantity ?? 0;
    const remainingStock = (product) => product.stock - cartQuantity(Number(product.id));

    const updateCatalogStock = () => {
        document.querySelectorAll('[data-product-card]').forEach((card) => {
            const product = products.get(Number(card.dataset.productId));
            if (!product) return;

            const remaining = remainingStock(product);
            card.querySelector('[data-product-stock]').textContent = `Disponibles: ${remaining}`;
            card.querySelector('[data-pos-add]').disabled = remaining <= 0;
        });
    };

    const renderCart = () => {
        const list = document.getElementById('pos-cart-items');
        const itemCount = [...cart.values()].reduce((count, item) => count + item.quantity, 0);
        const subtotal = [...cart.values()].reduce((sum, item) => sum + item.price * item.quantity, 0);
        list.replaceChildren();

        if (cart.size === 0) {
            const empty = document.createElement('li');
            empty.className = 'pos-cart-empty';
            empty.innerHTML = '<svg viewBox="0 0 64 64" fill="none" aria-hidden="true"><path d="M12 20h40l-4 34H16l-4-34Z" stroke="currentColor" stroke-width="3" stroke-linejoin="round"/><path d="M23 25v-6a9 9 0 0 1 18 0v6" stroke="currentColor" stroke-width="3" stroke-linecap="round"/><path d="M25 38h14" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg><div>Tu pedido está vacío</div><small>Agrega un producto del catálogo para comenzar.</small>';
            const browseButton = document.createElement('button');
            browseButton.type = 'button';
            browseButton.className = 'btn btn-primary rounded-3 px-3 py-2 mt-2';
            browseButton.dataset.posBrowse = '';
            browseButton.textContent = 'Seguir explorando';
            empty.append(browseButton);
            list.append(empty);
        } else {
            cart.forEach((item, productId) => {
                const row = document.createElement('li');
                row.className = 'pos-cart-line';

                const description = document.createElement('div');
                const name = document.createElement('span');
                name.className = 'pos-cart-line-name';
                name.textContent = item.name;
                const price = document.createElement('span');
                price.className = 'pos-cart-line-price';
                price.textContent = `${currency.format(item.price)} c/u · ${currency.format(item.price * item.quantity)}`;
                description.append(name, price);

                const actions = document.createElement('div');
                actions.className = 'pos-cart-line-actions';
                actions.append(
                    createCartButton('−', 'decrease', productId, 'Restar una unidad'),
                    Object.assign(document.createElement('span'), { className: 'pos-quantity-value', textContent: item.quantity }),
                    createCartButton('+', 'increase', productId, 'Sumar una unidad'),
                    createCartButton('×', 'remove', productId, 'Quitar del pedido', 'pos-cart-remove'),
                );
                row.append(description, actions);
                list.append(row);
            });
        }

        document.getElementById('pos-item-count').textContent = itemCount;
        document.getElementById('pos-mobile-count').textContent = itemCount;
        document.getElementById('pos-subtotal').textContent = currency.format(subtotal);
        document.getElementById('pos-total').textContent = currency.format(subtotal);
        document.getElementById('pos-checkout-button').disabled = cart.size === 0;
        updateCatalogStock();
    };

    const createCartButton = (label, action, productId, accessibleName, className = 'pos-quantity-button') => {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = className;
        button.dataset.cartAction = action;
        button.dataset.productId = productId;
        button.setAttribute('aria-label', accessibleName);
        button.textContent = label;

        return button;
    };

    const addProduct = (product, quantity = 1) => {
        const available = remainingStock(product);

        if (quantity < 1 || quantity > available) {
            showStatus(`Solo quedan ${Math.max(available, 0)} unidades de ${product.nombre}.`);
            return false;
        }

        const existing = cart.get(Number(product.id));
        cart.set(Number(product.id), {
            name: product.nombre,
            price: Number(product.precio),
            quantity: (existing?.quantity ?? 0) + quantity,
        });
        renderCart();

        return true;
    };

    const openProductDetail = (productId) => {
        activeProduct = products.get(Number(productId));
        if (!activeProduct) return;

        document.getElementById('pos-product-title').textContent = activeProduct.nombre;
        document.getElementById('pos-detail-image').src = activeProduct.imagen;
        document.getElementById('pos-detail-image').alt = `Imagen de ${activeProduct.nombre}`;
        document.getElementById('pos-detail-description').textContent = activeProduct.descripcion || 'Este producto no tiene una descripción adicional.';
        document.getElementById('pos-detail-price').textContent = currency.format(activeProduct.precio);
        document.getElementById('pos-detail-stock').textContent = `Disponibles: ${remainingStock(activeProduct)}`;
        detailQuantity.value = '1';
        detailQuantity.max = Math.max(remainingStock(activeProduct), 1);
        document.getElementById('pos-detail-add').disabled = remainingStock(activeProduct) < 1;
        productModal.show();
    };

    document.getElementById('pos-product-grid').addEventListener('click', (event) => {
        const addButton = event.target.closest('[data-pos-add]');
        if (addButton) {
            const product = products.get(Number(addButton.dataset.posAdd));
            if (product) addProduct(product);
            return;
        }

        const detailsButton = event.target.closest('[data-pos-open]');
        if (detailsButton) openProductDetail(detailsButton.dataset.posOpen);
    });

    document.getElementById('pos-cart-items').addEventListener('click', (event) => {
        if (!event.target.closest('[data-pos-browse]')) return;

        const cartPanel = document.getElementById('posCart');
        if (window.matchMedia('(max-width: 991.98px)').matches) {
            cartPanel.addEventListener('hidden.bs.offcanvas', () => {
                document.getElementById('pos-search').focus({ preventScroll: true });
            }, { once: true });
            bootstrap.Offcanvas.getOrCreateInstance(cartPanel).hide();
            return;
        }
        document.getElementById('pos-search').focus({ preventScroll: true });
    });

    document.getElementById('pos-product-grid').addEventListener('keydown', (event) => {
        if (event.key !== 'Enter' && event.key !== ' ') return;
        const card = event.target.closest('[data-product-card]');
        if (!card || event.target.closest('button')) return;
        event.preventDefault();
        openProductDetail(card.dataset.productId);
    });

    document.getElementById('pos-search').addEventListener('input', (event) => {
        const term = event.target.value.trim().toLocaleLowerCase('es');
        let visible = 0;
        document.querySelectorAll('[data-product-card]').forEach((card) => {
            const matches = card.dataset.productName.toLocaleLowerCase('es').includes(term);
            card.hidden = !matches;
            if (matches) visible += 1;
        });
        document.getElementById('pos-visible-count').textContent = visible;
        document.getElementById('pos-search-empty').hidden = visible > 0;
    });

    document.getElementById('pos-cart-items').addEventListener('click', (event) => {
        const button = event.target.closest('[data-cart-action]');
        if (!button) return;

        const productId = Number(button.dataset.productId);
        const item = cart.get(productId);
        const product = products.get(productId);
        if (!item || !product) return;

        if (button.dataset.cartAction === 'remove' || (button.dataset.cartAction === 'decrease' && item.quantity === 1)) {
            cart.delete(productId);
        } else if (button.dataset.cartAction === 'decrease') {
            item.quantity -= 1;
        } else if (button.dataset.cartAction === 'increase' && item.quantity < product.stock) {
            item.quantity += 1;
        }

        renderCart();
    });

    document.querySelectorAll('[data-detail-quantity]').forEach((button) => {
        button.addEventListener('click', () => {
            const nextValue = Number(detailQuantity.value) + Number(button.dataset.detailQuantity);
            detailQuantity.value = String(Math.max(1, Math.min(nextValue, Number(detailQuantity.max))));
        });
    });

    document.getElementById('pos-detail-add').addEventListener('click', () => {
        if (activeProduct && addProduct(activeProduct, Number(detailQuantity.value))) productModal.hide();
    });

    checkoutForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (cart.size === 0) {
            showStatus('Agrega al menos un producto antes de confirmar.');
            return;
        }

        const button = document.getElementById('pos-checkout-button');
        button.disabled = true;
        button.disabled = true;
        button.innerHTML = '<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>Procesando venta…';

        try {
            const response = await fetch(page.dataset.posCheckoutUrl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({
                    cliente_nombre: document.getElementById('pos-client-name').value || null,
                    cliente_documento: document.getElementById('pos-client-document').value || null,
                    metodo_pago: document.getElementById('pos-payment-method').value,
                    items: [...cart.entries()].map(([id_producto, item]) => ({ id_producto, cantidad: item.quantity })),
                }),
            });
            const result = await response.json();

            if (!response.ok) {
                const message = Object.values(result.errors ?? {}).flat()[0] ?? result.message ?? 'No se pudo completar la venta.';
                showStatus(message);
                return;
            }

            cart.forEach((item, productId) => {
                const product = products.get(productId);
                if (product) product.stock -= item.quantity;
            });
            cart.clear();
            checkoutForm.reset();
            renderCart();
            Swal.fire({
                icon: 'success',
                title: '¡Venta registrada!',
                text: `Venta #${result.venta_id} · Total ${currency.format(Number(result.total_venta))}`,
                showCancelButton: true,
                confirmButtonText: 'Descargar recibo',
                cancelButtonText: 'Continuar',
                reverseButtons: true,
            }).then(({ isConfirmed }) => {
                if (isConfirmed) {
                    window.location.assign(result.recibo_url);
                }
            });
        } catch (error) {
            showStatus(error instanceof Error ? `No se pudo conectar con el servidor: ${error.message}` : 'No se pudo conectar con el servidor.');
        } finally {
            button.innerHTML = '<i class="bi bi-lock-fill me-2" aria-hidden="true"></i>Confirmar venta';
            button.disabled = cart.size === 0;
        }
    });

    renderCart();
});
