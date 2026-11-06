<div class="supplier-product-list">
    @forelse ($proveedor->productosProveedores as $precio)
        @php
            $priceFormKey = 'precio-'.$precio->id_producto.'-'.$proveedor->id_proveedor;
            $isPriceForm = old('_form') === $priceFormKey;
        @endphp
        <article
            class="supplier-product-card"
            data-search-row
            data-search-value="{{ $precio->producto->nombre_producto }} {{ $precio->precio_compra }}"
            data-suggestion-label="{{ $precio->producto->nombre_producto }} · S/ {{ number_format((float) $precio->precio_compra, 2) }}"
        >
            <div class="supplier-product-identity">
                <img
                    class="supplier-product-thumbnail"
                    src="{{ $precio->producto->imagen_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($precio->producto->imagen_path) : asset('images/product-placeholder.svg') }}"
                    alt="Imagen de {{ $precio->producto->nombre_producto }}"
                    loading="lazy"
                >
                <div class="supplier-product-details">
                    <h3 class="supplier-product-name">{{ $precio->producto->nombre_producto }}</h3>
                    <span class="supplier-product-label">Precio de compra</span>
                </div>
            </div>
            <div class="supplier-product-actions">
                <form
                    action="{{ route('proveedores.precios.update', [$proveedor, $precio->producto]) }}"
                    method="POST"
                    class="supplier-product-price-form"
                >
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="_form" value="{{ $priceFormKey }}">
                    @if ($returnToList ?? false)
                        <input type="hidden" name="_return_to_list" value="1">
                    @endif
                    <label class="visually-hidden" for="precio-{{ $proveedor->id_proveedor }}-{{ $precio->id_producto }}">
                        Precio de compra de {{ $precio->producto->nombre_producto }}
                    </label>
                    <span class="supplier-product-currency" aria-hidden="true">S/</span>
                    <input
                        id="precio-{{ $proveedor->id_proveedor }}-{{ $precio->id_producto }}"
                        type="number"
                        name="precio_compra"
                        class="form-control inline-price supplier-product-price {{ $isPriceForm && $errors->has('precio_compra') ? 'is-invalid' : '' }}"
                        min="0"
                        max="99999999.99"
                        step="0.01"
                        value="{{ $isPriceForm ? old('precio_compra') : $precio->precio_compra }}"
                        required
                    >
                    @if ($isPriceForm)
                        @error('precio_compra')
                            <span class="invalid-feedback d-block supplier-product-error">{{ $message }}</span>
                        @enderror
                    @endif
                    <button
                        type="submit"
                        class="btn-icon btn-icon-success"
                        aria-label="Guardar precio de {{ $precio->producto->nombre_producto }}"
                        title="Guardar precio"
                    >
                        <i class="bi bi-check-lg" aria-hidden="true"></i>
                    </button>
                </form>
                <form
                    action="{{ route('proveedores.precios.destroy', [$proveedor, $precio->producto]) }}"
                    method="POST"
                    data-confirm="¿Quitar {{ $precio->producto->nombre_producto }} de este proveedor?"
                >
                    @csrf
                    @method('DELETE')
                    @if ($returnToList ?? false)
                        <input type="hidden" name="_return_to_list" value="1">
                    @endif
                    <button
                        type="submit"
                        class="btn-icon btn-icon-danger"
                        aria-label="Quitar {{ $precio->producto->nombre_producto }} de este proveedor"
                        title="Quitar producto"
                    >
                        <i class="bi bi-trash3" aria-hidden="true"></i>
                    </button>
                </form>
            </div>
        </article>
    @empty
        <p class="text-muted mb-0">Este proveedor todavía no tiene productos registrados.</p>
    @endforelse
    @if ($proveedor->productosProveedores->isNotEmpty())
        <p class="text-center text-muted py-3 mb-0" data-search-empty hidden>No encontramos productos que coincidan.</p>
    @endif
</div>
