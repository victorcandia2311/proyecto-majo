@extends('layouts.app')

@section('title', 'Punto de venta')

@push('styles')
<style>
    /* ── Variables ────────────────────────────────────────────────── */
    .pos-page {
        --pos-ink:    #172033;
        --pos-muted:  #748096;
        --pos-accent: #f05a28;
        --pos-accent-hover: #d9471b;
        --pos-accent-glow: #f05a2840;
        --pos-surface: #ffffff;
        --pos-bg:     #f4f6fb;
        color: var(--pos-ink);
    }

    /* ── Page header ──────────────────────────────────────────────── */
    .pos-heading {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
    .pos-heading h1 {
        margin: 0;
        font-size: clamp(1.6rem, 3vw, 2.2rem);
        font-weight: 800;
        letter-spacing: -.04em;
    }
    .pos-heading p { margin: .35rem 0 0; color: var(--pos-muted); }

    /* ── Search bar ───────────────────────────────────────────────── */
    .pos-search-wrap {
        position: relative;
        max-width: 520px;
        flex: 1;
    }
    .pos-search-wrap .bi-search {
        position: absolute;
        left: 1.1rem;
        top: 50%;
        transform: translateY(-50%);
        color: #8a94a6;
        pointer-events: none;
        z-index: 2;
    }
    .pos-search-input {
        width: 100%;
        padding: .75rem 1.25rem .75rem 2.9rem;
        border: 1.5px solid #e5e9f1;
        border-radius: 50px;         /* pill */
        background: var(--pos-surface);
        box-shadow: 0 4px 16px #17203308;
        font-size: .95rem;
        transition: border-color .2s, box-shadow .2s;
    }
    .pos-search-input:focus {
        outline: none;
        border-color: var(--pos-accent);
        box-shadow: 0 0 0 3px var(--pos-accent-glow), 0 4px 16px #17203308;
    }
    .pos-toolbar {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
    }
    .pos-result-count {
        color: var(--pos-muted);
        font-size: .85rem;
        white-space: nowrap;
    }

    /* ── Product grid ─────────────────────────────────────────────── */
    .pos-product-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(210px, 1fr));
        gap: 1.25rem;
        padding-bottom: 7rem; /* room for FAB */
    }

    /* ── Product card (Soft UI) ───────────────────────────────────── */
    .pos-product-card {
        position: relative;
        background: var(--pos-surface);
        border: 0;
        border-radius: 1.25rem;
        box-shadow: 0 2px 12px #17203310;
        overflow: hidden;
        transition: transform .28s ease, box-shadow .28s ease;
        cursor: pointer;
    }
    .pos-product-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 14px 36px #17203322;
    }
    .pos-product-open {
        display: block;
        width: 100%;
        padding: 0;
        border: 0;
        background: transparent;
        text-align: left;
        color: inherit;
    }
    .pos-product-media {
        display: block;
        height: 185px;
        overflow: hidden;
        background: #f0f3f8;
    }
    .pos-product-media img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform .3s ease;
    }
    .pos-product-card:hover .pos-product-media img { transform: scale(1.04); }
    .pos-product-info {
        padding: 1rem 3.75rem 1.1rem 1rem;
    }
    .pos-product-name {
        display: -webkit-box;
        min-height: 2.8em;
        margin: 0 0 .5rem;
        overflow: hidden;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
        font-size: .95rem;
        font-weight: 700;
        line-height: 1.4;
    }
    .pos-product-price {
        color: var(--pos-accent);
        font-size: 1.15rem;
        font-weight: 800;
        font-variant-numeric: tabular-nums;
    }
    .pos-product-stock {
        display: block;
        margin-top: .2rem;
        color: var(--pos-muted);
        font-size: .75rem;
    }

    /* ── Add button (inside card) ─────────────────────────────────── */
    .pos-add-button {
        position: absolute;
        right: .85rem;
        bottom: .85rem;
        display: grid;
        width: 42px;
        height: 42px;
        place-items: center;
        border: 0;
        border-radius: 50px;
        background: var(--pos-accent);
        color: #fff;
        font-size: 1.25rem;
        box-shadow: 0 6px 18px var(--pos-accent-glow);
        transition: background .2s, transform .15s, box-shadow .2s;
    }
    .pos-add-button:hover {
        background: var(--pos-accent-hover);
        transform: scale(1.08);
        box-shadow: 0 8px 22px #f05a2855;
    }
    .pos-add-button:active { transform: scale(.94); }
    .pos-add-button:disabled {
        background: #b8bec9;
        box-shadow: none;
        cursor: not-allowed;
    }

    /* ── Empty catalog state ──────────────────────────────────────── */
    .pos-empty-catalog {
        grid-column: 1 / -1;
        padding: 3.5rem 1rem;
        border: 1.5px dashed #d9dee8;
        border-radius: 1.25rem;
        background: var(--pos-surface);
        text-align: center;
        color: var(--pos-muted);
    }
    .pos-empty-catalog svg { width: 64px; height: 64px; margin-bottom: .75rem; color: #aab3c2; }
    .pos-search-empty {
        grid-column: 1 / -1;
        padding: 2.5rem 1rem;
        text-align: center;
        color: var(--pos-muted);
    }

    /* ── FAB (Floating Action Button) ─────────────────────────────── */
    .pos-fab {
        position: fixed;
        bottom: 2rem;
        right: 2rem;
        z-index: 1050;
        width: 64px;
        height: 64px;
        display: grid;
        place-items: center;
        border: 0;
        border-radius: 50%;
        background: var(--pos-ink);
        color: #fff;
        font-size: 1.5rem;
        box-shadow: 0 8px 28px #172033aa;
        transition: background .2s, transform .2s, box-shadow .2s;
        cursor: pointer;
    }
    .pos-fab:hover {
        background: #2c3850;
        transform: scale(1.06);
        box-shadow: 0 12px 36px #172033cc;
    }
    .pos-fab:active { transform: scale(.94); }
    .pos-fab-badge {
        position: absolute;
        top: 0;
        left: 100%;
        transform: translate(-50%, -50%);
        min-width: 22px;
        height: 22px;
        padding: 0 .35rem;
        display: grid;
        place-items: center;
        border-radius: 99px;
        background: var(--pos-accent);
        color: #fff;
        font-size: .7rem;
        font-weight: 800;
        line-height: 1;
        pointer-events: none;
        transition: transform .2s;
    }
    .pos-fab-badge[data-empty="true"] { transform: translate(-50%, -50%) scale(0); }

    /* ── Offcanvas cart ───────────────────────────────────────────── */
    #posCart { width: min(440px, 96vw); }

    .pos-cart-header-inner {
        display: flex;
        align-items: center;
        gap: .6rem;
        flex: 1;
    }
    .pos-cart-title-icon {
        display: grid;
        width: 38px;
        height: 38px;
        place-items: center;
        border-radius: 12px;
        background: #fff4ee;
        color: var(--pos-accent);
        font-size: 1.1rem;
        flex-shrink: 0;
    }
    .pos-cart-title { margin: 0; font-size: 1.05rem; font-weight: 800; }
    .pos-cart-count {
        display: inline-grid;
        min-width: 26px;
        height: 26px;
        place-items: center;
        padding: 0 .4rem;
        border-radius: 99px;
        background: #fff0e9;
        color: var(--pos-accent-hover);
        font-size: .75rem;
        font-weight: 800;
    }

    /* ── Cart body (Flexbox column) ───────────────────────────────── */
    .pos-cart-body {
        display: flex;
        flex-direction: column;
        height: 100%;
        padding: 1rem 1.25rem 1.5rem;
    }

    /* ── Cart items list ──────────────────────────────────────────── */
    .pos-cart-items {
        flex-grow: 1;
        overflow-y: auto;
        margin: 0;
        padding: 0;
        list-style: none;
        min-height: 0;
    }
    .pos-cart-line {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: .5rem;
        padding: .85rem 0;
        border-bottom: 1px solid #f0f2f5;
    }
    .pos-cart-line:last-child { border-bottom: 0; }
    .pos-cart-line-name {
        display: block;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        font-size: .88rem;
        font-weight: 700;
    }
    .pos-cart-line-price {
        display: block;
        margin-top: .18rem;
        color: var(--pos-muted);
        font-size: .75rem;
    }
    .pos-cart-line-actions {
        display: flex;
        align-items: center;
        gap: .4rem;
    }
    .pos-quantity-button {
        width: 30px;
        height: 30px;
        border: 1.5px solid #e3e7ef;
        border-radius: 9px;
        background: #fff;
        color: var(--pos-ink);
        font-size: .9rem;
        display: grid;
        place-items: center;
        transition: border-color .15s, background .15s;
    }
    .pos-quantity-button:hover { border-color: var(--pos-accent); background: #fff4ee; }
    .pos-quantity-value {
        min-width: 1.4rem;
        text-align: center;
        font-size: .85rem;
        font-weight: 700;
    }
    .pos-cart-remove {
        border: 0;
        background: none;
        color: #9aa3b1;
        font-size: 1.1rem;
        line-height: 1;
        padding: 0 .2rem;
        transition: color .15s;
    }
    .pos-cart-remove:hover { color: #dc3545; }

    /* ── Empty cart state ─────────────────────────────────────────── */
    .pos-cart-empty {
        display: grid;
        justify-items: center;
        gap: .4rem;
        padding: 2.5rem .75rem;
        color: var(--pos-muted);
        text-align: center;
        opacity: .6;
    }
    .pos-cart-empty svg { width: 56px; height: 56px; margin-bottom: .5rem; color: #c5cbd5; }

    /* ── Cart footer (anchored) ───────────────────────────────────── */
    .pos-cart-footer { margin-top: auto; padding-top: 1rem; }

    .pos-cart-summary {
        display: grid;
        gap: .5rem;
        padding: 1rem 0;
        border-top: 1px solid #edf0f4;
    }
    .pos-summary-row {
        display: flex;
        justify-content: space-between;
        color: #667186;
        font-size: .85rem;
    }
    .pos-summary-total {
        padding-top: .75rem;
        border-top: 1px dashed #dfe4ec;
        color: var(--pos-ink);
        font-size: 1.25rem;
        font-weight: 800;
    }

    /* ── Checkout form fields ─────────────────────────────────────── */
    .pos-customer-fields {
        display: grid;
        gap: .65rem;
        margin: 1rem 0;
    }
    .pos-customer-fields label {
        margin-bottom: .3rem;
        font-size: .75rem;
        font-weight: 700;
        letter-spacing: .03em;
        text-transform: uppercase;
        color: var(--pos-muted);
    }
    .pos-customer-fields .form-control,
    .pos-customer-fields .form-select {
        border-radius: 11px;
        font-size: .88rem;
        border-color: #e5e9f1;
    }
    .pos-customer-fields .form-control:focus,
    .pos-customer-fields .form-select:focus {
        border-color: var(--pos-accent);
        box-shadow: 0 0 0 3px var(--pos-accent-glow);
    }

    /* ── Checkout button ──────────────────────────────────────────── */
    .pos-checkout-button {
        width: 100%;
        min-height: 52px;
        padding: .8rem 1rem;
        border: 0;
        border-radius: 50px;  /* pill */
        background: var(--pos-ink);
        color: #fff;
        font-weight: 750;
        font-size: 1rem;
        letter-spacing: .01em;
        transition: background .2s, transform .15s;
    }
    .pos-checkout-button:hover:not(:disabled) { background: #2c3850; }
    .pos-checkout-button:active:not(:disabled) { transform: scale(.97); }
    .pos-checkout-button:disabled {
        background: #b8bec9;
        cursor: not-allowed;
    }

    /* ── Product detail modal ─────────────────────────────────────── */
    .pos-detail-image {
        width: 100%;
        max-height: 260px;
        border-radius: 1rem;
        object-fit: cover;
    }
    .pos-detail-price {
        color: var(--pos-accent);
        font-size: 1.35rem;
        font-weight: 800;
    }

    /* ── Responsive tweaks ────────────────────────────────────────── */
    @media (max-width: 575.98px) {
        .pos-product-grid {
            grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
            gap: .85rem;
        }
        .pos-product-media { height: 150px; }
        .pos-product-info { padding: .8rem 3.25rem .9rem .8rem; }
        .pos-product-name { font-size: .88rem; }
        .pos-add-button { width: 38px; height: 38px; font-size: 1.1rem; }
        .pos-fab { bottom: 1.25rem; right: 1.25rem; width: 56px; height: 56px; font-size: 1.3rem; }
    }
</style>
@endpush

@section('content')
<div class="pos-page" data-pos-checkout-url="{{ route('ventas.confirmar') }}">

    {{-- ── Page Header ── --}}
    <header class="pos-heading">
        <div>
            <p class="text-uppercase small fw-bold text-secondary mb-2">MAJO · VENTA RÁPIDA</p>
            <h1>¿Qué llevamos hoy?</h1>
            <p>Selecciona tus productos y confirma la venta en segundos.</p>
        </div>
        <span class="badge rounded-pill text-bg-light border px-3 py-2">
            <i class="bi bi-lightning-charge-fill text-warning me-1" aria-hidden="true"></i> Caja lista
        </span>
    </header>

    {{-- ── Full-Width Catalog ── --}}
    <section aria-label="Catálogo de productos">

        {{-- Search toolbar --}}
        <div class="pos-toolbar">
            <div class="pos-search-wrap">
                <i class="bi bi-search" aria-hidden="true"></i>
                <label class="visually-hidden" for="pos-search">Buscar productos</label>
                <input id="pos-search"
                       class="pos-search-input"
                       type="search"
                       placeholder="Buscar por nombre…"
                       autocomplete="off">
            </div>
            <span class="pos-result-count">
                <span id="pos-visible-count">{{ $productos->count() }}</span> productos
            </span>
        </div>

        {{-- Product grid --}}
        <div class="pos-product-grid" id="pos-product-grid">
            @forelse ($productos as $producto)
                <article
                    class="pos-product-card"
                    data-product-card
                    data-product-id="{{ $producto->getKey() }}"
                    data-product-name="{{ $producto->nombre_producto }}"
                >
                    <button
                        type="button"
                        class="pos-product-open"
                        data-pos-open="{{ $producto->getKey() }}"
                        aria-label="Ver detalle de {{ $producto->nombre_producto }}"
                    >
                        <span class="pos-product-media">
                            <img
                                src="{{ $producto->imagen_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($producto->imagen_path) : asset('images/product-placeholder.svg') }}"
                                alt="Imagen de {{ $producto->nombre_producto }}"
                                loading="lazy"
                            >
                        </span>
                        <span class="pos-product-info">
                            <span class="pos-product-name">{{ $producto->nombre_producto }}</span>
                            <span class="pos-product-price">S/ {{ number_format((float) $producto->precio_venta, 2) }}</span>
                            <span class="pos-product-stock" data-product-stock>Stock: {{ $producto->stock_actual }}</span>
                        </span>
                    </button>
                    <button
                        type="button"
                        class="pos-add-button"
                        data-pos-add="{{ $producto->getKey() }}"
                        aria-label="Agregar {{ $producto->nombre_producto }} al carrito"
                    >
                        <i class="bi bi-plus-lg" aria-hidden="true"></i>
                    </button>
                </article>
            @empty
                <div class="pos-empty-catalog">
                    <svg viewBox="0 0 64 64" fill="none" aria-hidden="true">
                        <path d="M9 18h46l-4 36H13L9 18Z" stroke="currentColor" stroke-width="3" stroke-linejoin="round"/>
                        <path d="M22 24v-5a10 10 0 0 1 20 0v5" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                        <path d="M25 36h14" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                    </svg>
                    <h2 class="h5 text-dark">Aún no hay productos disponibles</h2>
                    <p class="mb-0">Pide al administrador que registre productos y actualice su inventario.</p>
                </div>
            @endforelse

            <div class="pos-search-empty" id="pos-search-empty" hidden>
                <i class="bi bi-search fs-2 d-block mb-2 text-secondary" aria-hidden="true"></i>
                No encontramos productos con ese nombre.
            </div>
        </div>
    </section>

    {{-- ── FAB: Floating Action Button ── --}}
    <button
        class="pos-fab"
        type="button"
        data-bs-toggle="offcanvas"
        data-bs-target="#posCart"
        aria-controls="posCart"
        aria-label="Abrir carrito de compras"
    >
        <i class="bi bi-bag-check" aria-hidden="true"></i>
        <span class="pos-fab-badge" id="pos-mobile-count" data-empty="true">0</span>
    </button>

</div>{{-- /pos-page --}}

{{-- ── Offcanvas Cart ────────────────────────────────────────────────── --}}
<div class="offcanvas offcanvas-end" tabindex="-1" id="posCart" aria-labelledby="pos-cart-title">

    {{-- Header --}}
    <div class="offcanvas-header border-bottom">
        <div class="pos-cart-header-inner">
            <span class="pos-cart-title-icon" aria-hidden="true">
                <i class="bi bi-bag-check"></i>
            </span>
            <h2 class="pos-cart-title" id="pos-cart-title">Tu pedido</h2>
            <span class="pos-cart-count" id="pos-item-count" aria-live="polite" aria-label="Cantidad de artículos en el carrito">0</span>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Cerrar carrito"></button>
    </div>

    {{-- Body (flex column = items scroll, footer anchored) --}}
    <div class="offcanvas-body pos-cart-body">

        {{-- Items list (grows + scrolls independently) --}}
        <ul class="pos-cart-items" id="pos-cart-items" aria-live="polite" aria-label="Productos en el pedido"></ul>

        {{-- Footer / Checkout (anchored to bottom) --}}
        <div class="pos-cart-footer">
            {{-- Totals --}}
            <div class="pos-cart-summary">
                <div class="pos-summary-row">
                    <span>Subtotal</span>
                    <strong id="pos-subtotal">S/ 0.00</strong>
                </div>
                <div class="pos-summary-row">
                    <span>Impuestos</span>
                    <strong>No aplica</strong>
                </div>
                <div class="pos-summary-row pos-summary-total">
                    <span>Total</span>
                    <strong id="pos-total">S/ 0.00</strong>
                </div>
            </div>

            {{-- Checkout form --}}
            <form id="pos-checkout-form">
                @csrf
                <div class="pos-customer-fields">
                    <div>
                        <label for="pos-client-name">
                            Cliente <span class="fw-normal text-secondary text-lowercase">(opcional)</span>
                        </label>
                        <input
                            id="pos-client-name"
                            class="form-control"
                            name="cliente_nombre"
                            maxlength="100"
                            autocomplete="name"
                            placeholder="Nombre del cliente"
                        >
                    </div>
                    <div>
                        <label for="pos-client-document">
                            DNI o RUC <span class="fw-normal text-secondary text-lowercase">(opcional)</span>
                        </label>
                        <input
                            id="pos-client-document"
                            class="form-control"
                            name="cliente_documento"
                            inputmode="numeric"
                            maxlength="11"
                            placeholder="8 u 11 dígitos"
                        >
                    </div>
                    <div>
                        <label for="pos-payment-method">Método de pago</label>
                        <select id="pos-payment-method" class="form-select" name="metodo_pago" required>
                            <option value="">Seleccionar método</option>
                            <option>Efectivo</option>
                            <option>Yape</option>
                            <option>Plin</option>
                            <option>Tarjeta</option>
                        </select>
                    </div>
                </div>

                <button type="submit" id="pos-checkout-button" class="pos-checkout-button" disabled>
                    <i class="bi bi-lock-fill me-2" aria-hidden="true"></i>Confirmar venta
                </button>
            </form>
        </div>{{-- /pos-cart-footer --}}

    </div>{{-- /offcanvas-body --}}
</div>{{-- /offcanvas --}}

{{-- ── Product Detail Modal ─────────────────────────────────────────── --}}
<div class="modal fade" id="pos-product-modal" tabindex="-1" aria-labelledby="pos-product-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header border-0 pb-0">
                <h2 class="modal-title fs-5 fw-bold" id="pos-product-title">Detalle del producto</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar detalle"></button>
            </div>
            <div class="modal-body">
                <img id="pos-detail-image"
                     class="pos-detail-image mb-3"
                     src="{{ asset('images/product-placeholder.svg') }}"
                     alt="">
                <p id="pos-detail-description" class="text-secondary mb-2"></p>
                <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
                    <span class="pos-detail-price" id="pos-detail-price"></span>
                    <span class="badge rounded-pill text-bg-light border" id="pos-detail-stock"></span>
                </div>
                <label for="pos-detail-quantity" class="form-label fw-semibold">Cantidad</label>
                <div class="input-group">
                    <button class="btn btn-outline-secondary" type="button" data-detail-quantity="-1" aria-label="Restar una unidad">
                        <i class="bi bi-dash-lg" aria-hidden="true"></i>
                    </button>
                    <input id="pos-detail-quantity"
                           class="form-control text-center"
                           type="number"
                           value="1"
                           min="1"
                           step="1"
                           inputmode="numeric">
                    <button class="btn btn-outline-secondary" type="button" data-detail-quantity="1" aria-label="Sumar una unidad">
                        <i class="bi bi-plus-lg" aria-hidden="true"></i>
                    </button>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Seguir viendo</button>
                <button type="button" class="btn btn-warning fw-bold px-4 rounded-pill" id="pos-detail-add">
                    <i class="bi bi-bag-plus me-2" aria-hidden="true"></i>Agregar
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ── Catalog data (JSON for pos.js) ──────────────────────────────── --}}
<script id="pos-catalog-data" type="application/json">{!! \Illuminate\Support\Js::encode($productos->map(fn ($producto): array => [
    'id'          => $producto->getKey(),
    'nombre'      => $producto->nombre_producto,
    'descripcion' => $producto->descripcion,
    'precio'      => (float) $producto->precio_venta,
    'stock'       => (int) $producto->stock_actual,
    'imagen'      => $producto->imagen_path
                        ? \Illuminate\Support\Facades\Storage::disk('public')->url($producto->imagen_path)
                        : asset('images/product-placeholder.svg'),
])->values()) !!}</script>
@endsection

@push('scripts')
<script>
/**
 * POS FAB badge sync — keeps the floating badge in step with the
 * item count already managed by pos.js (which writes #pos-mobile-count).
 *
 * We use a MutationObserver so we never need to modify pos.js at all.
 */
document.addEventListener('DOMContentLoaded', () => {
    const badge = document.getElementById('pos-mobile-count');
    if (!badge) return;

    const sync = () => {
        const n = parseInt(badge.textContent, 10) || 0;
        badge.dataset.empty = n === 0 ? 'true' : 'false';
    };

    // Initial sync after pos.js renders the cart
    sync();

    // Watch for text changes written by pos.js
    new MutationObserver(sync).observe(badge, { childList: true, characterData: true, subtree: true });
});
</script>
<script src="{{ asset('js/pos.js') }}?v={{ filemtime(public_path('js/pos.js')) }}" defer></script>
@endpush
