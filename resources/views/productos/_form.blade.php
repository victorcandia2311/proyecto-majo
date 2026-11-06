@php
    $formKey = $formKey ?? null;
    $formIdPrefix = $formIdPrefix ?? '';
    $useOldInput = $formKey === null || old('_form') === $formKey;
@endphp

<div class="row g-3">
    <div class="col-12"><label for="{{ $formIdPrefix }}nombre_producto" class="form-label">Nombre del producto</label>
    <input type="text" id="{{ $formIdPrefix }}nombre_producto" name="nombre_producto" class="form-control @if ($useOldInput && $errors->has('nombre_producto')) is-invalid @endif" value="{{ $useOldInput ? old('nombre_producto', $producto?->nombre_producto) : $producto?->nombre_producto }}" maxlength="150" required>
    @if ($useOldInput)
        @error('nombre_producto')<div class="invalid-feedback">{{ $message }}</div>@enderror
    @endif
</div>
    <div class="col-12">
        <label for="{{ $formIdPrefix }}descripcion" class="form-label">Descripción</label>
        <textarea id="{{ $formIdPrefix }}descripcion" name="descripcion" class="form-control @if ($useOldInput && $errors->has('descripcion')) is-invalid @endif" rows="3" maxlength="2000">{{ $useOldInput ? old('descripcion', $producto?->descripcion) : $producto?->descripcion }}</textarea>
        @if ($useOldInput)
            @error('descripcion')<div class="invalid-feedback">{{ $message }}</div>@enderror
        @endif
    </div>
    <div
        class="col-12"
        data-image-uploader
        data-existing-src="{{ $producto?->imagen_path ? Illuminate\Support\Facades\Storage::disk('public')->url($producto->imagen_path) : '' }}"
    >
        <label class="form-label" for="{{ $formIdPrefix }}imagen">Imagen del producto</label>
        <input
            type="file"
            id="{{ $formIdPrefix }}imagen"
            name="imagen"
            class="visually-hidden"
            accept="image/jpeg,image/png,image/webp"
            aria-describedby="{{ $formIdPrefix }}imagen-ayuda {{ $formIdPrefix }}imagen-error"
            data-image-input
        >
        <label
            for="{{ $formIdPrefix }}imagen"
            class="product-image-dropzone"
            tabindex="0"
            role="button"
            aria-describedby="{{ $formIdPrefix }}imagen-ayuda {{ $formIdPrefix }}imagen-error"
            data-image-dropzone
        >
            <i class="bi bi-cloud-arrow-up fs-2 text-primary" aria-hidden="true"></i>
            <span class="fw-semibold">Arrastra una imagen o haz clic para seleccionarla</span>
            <span class="small text-muted">Enfoca esta área y pega una imagen copiada con Ctrl + V</span>
        </label>
        <div class="form-text mt-2" id="{{ $formIdPrefix }}imagen-ayuda">JPG, PNG o WebP. Máximo 3 MB. También puedes elegir una imagen descargada.</div>
        <p class="small text-danger mt-2 mb-0" id="{{ $formIdPrefix }}imagen-error" role="alert" data-image-error hidden></p>
        @if ($useOldInput)
            @error('imagen')<p class="small text-danger mt-2 mb-0">{{ $message }}</p>@enderror
        @endif
        <div class="product-image-preview mt-3 {{ $producto?->imagen_path ? '' : 'd-none' }}" data-image-preview>
            <img
                src="{{ $producto?->imagen_path ? Illuminate\Support\Facades\Storage::disk('public')->url($producto->imagen_path) : '' }}"
                alt="{{ $producto?->imagen_path ? 'Imagen actual de '.$producto->nombre_producto : '' }}"
                data-image-preview-image
            >
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-2">
                <span class="small text-muted" data-image-file-name>{{ $producto?->imagen_path ? 'Imagen actual' : '' }}</span>
                <button type="button" class="btn btn-outline-danger btn-sm" data-image-clear hidden>Quitar selección</button>
            </div>
        </div>
    </div>
    @if ($producto)
        <div class="col-md-6"><label for="{{ $formIdPrefix }}stock_actual" class="form-label">Stock actual</label>
            <input id="{{ $formIdPrefix }}stock_actual" class="form-control" value="{{ $producto->stock_actual }}" disabled>
            <p class="form-text mb-0">El stock se modifica desde <a href="{{ route('inventario.index') }}">Inventario</a>.</p>
        </div>
    @else
        <div class="col-md-6"><label for="{{ $formIdPrefix }}stock_actual" class="form-label">Stock inicial</label>
        <input type="number" id="{{ $formIdPrefix }}stock_actual" name="stock_actual" class="form-control @if ($useOldInput && $errors->has('stock_actual')) is-invalid @endif" value="{{ $useOldInput ? old('stock_actual', 0) : 0 }}" min="0" max="2147483647" step="1" required>
        @if ($useOldInput)
            @error('stock_actual')<div class="invalid-feedback">{{ $message }}</div>@enderror
        @endif
</div>
        @endif
        <div class="col-md-6"><label for="{{ $formIdPrefix }}stock_minimo_alerta" class="form-label">Stock mínimo para alerta</label>
        <input type="number" id="{{ $formIdPrefix }}stock_minimo_alerta" name="stock_minimo_alerta" class="form-control @if ($useOldInput && $errors->has('stock_minimo_alerta')) is-invalid @endif" value="{{ $useOldInput ? old('stock_minimo_alerta', $producto?->stock_minimo_alerta ?? 10) : ($producto?->stock_minimo_alerta ?? 10) }}" min="0" max="2147483647" step="1" required>
        @if ($useOldInput)
            @error('stock_minimo_alerta')<div class="invalid-feedback">{{ $message }}</div>@enderror
        @endif
</div>
        <div class="col-md-6"><label for="{{ $formIdPrefix }}precio_venta" class="form-label">Precio de venta (S/)</label>
        <input type="number" id="{{ $formIdPrefix }}precio_venta" name="precio_venta" class="form-control @if ($useOldInput && $errors->has('precio_venta')) is-invalid @endif" value="{{ $useOldInput ? old('precio_venta', $producto?->precio_venta) : $producto?->precio_venta }}" min="0" max="99999999.99" step="0.01" required>
        @if ($useOldInput)
            @error('precio_venta')<div class="invalid-feedback">{{ $message }}</div>@enderror
        @endif
</div>
</div>
<div class="d-flex flex-wrap gap-2 mt-4">
    <button class="btn btn-success" type="submit">{{ $producto ? 'Guardar cambios' : 'Guardar producto' }}</button>
    @if ($formInModal ?? false)
        <button class="btn btn-light" type="button" data-bs-dismiss="modal">Cancelar</button>
    @else
        <a href="{{ route('productos.index') }}" class="btn btn-secondary">Cancelar</a>
    @endif
</div>

@pushOnce('styles', 'product-image-uploader-styles')
    <style>
        .product-image-dropzone {
            display: grid;
            justify-items: center;
            gap: .45rem;
            padding: 1.5rem 1rem;
            border: 1.5px dashed var(--bs-border-color);
            border-radius: 1rem;
            background: var(--bs-tertiary-bg);
            color: var(--bs-body-color);
            text-align: center;
            cursor: pointer;
            transition: border-color .2s ease, background-color .2s ease;
        }
        .product-image-dropzone:hover,
        .product-image-dropzone:focus-visible,
        .product-image-dropzone.is-dragging {
            border-color: var(--bs-primary);
            background: var(--bs-primary-bg-subtle);
            outline: none;
        }
        .product-image-preview img {
            display: block;
            width: 100%;
            max-height: 220px;
            border-radius: .75rem;
            object-fit: contain;
            background: var(--bs-tertiary-bg);
        }
    </style>
@endPushOnce

@pushOnce('scripts', 'product-image-uploader-script')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('[data-image-uploader]').forEach(function (uploader) {
                const input = uploader.querySelector('[data-image-input]');
                const dropzone = uploader.querySelector('[data-image-dropzone]');
                const preview = uploader.querySelector('[data-image-preview]');
                const previewImage = uploader.querySelector('[data-image-preview-image]');
                const fileName = uploader.querySelector('[data-image-file-name]');
                const clearButton = uploader.querySelector('[data-image-clear]');
                const errorMessage = uploader.querySelector('[data-image-error]');
                const existingSrc = uploader.dataset.existingSrc;
                let previewUrl = null;
                const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];

                function showError(message) {
                    errorMessage.textContent = message;
                    errorMessage.hidden = false;
                }

                function clearError() {
                    errorMessage.textContent = '';
                    errorMessage.hidden = true;
                }

                function validateImage(file) {
                    clearError();

                    if (!allowedTypes.includes(file.type)) {
                        showError('Elige una imagen JPG, PNG o WebP.');
                        return false;
                    }

                    if (file.size > 3 * 1024 * 1024) {
                        showError('La imagen no debe superar los 3 MB.');
                        return false;
                    }

                    return true;
                }

                function showPreview(file) {
                    if (previewUrl) {
                        URL.revokeObjectURL(previewUrl);
                    }

                    previewUrl = URL.createObjectURL(file);
                    previewImage.src = previewUrl;
                    previewImage.alt = 'Vista previa de ' + file.name;
                    fileName.textContent = file.name || 'Imagen pegada';
                    preview.classList.remove('d-none');
                    clearButton.hidden = false;
                }

                function selectImage(file) {
                    if (!validateImage(file)) {
                        return;
                    }

                    if (typeof DataTransfer === 'undefined') {
                        showError('Este navegador no permite pegar la imagen. Selecciónala desde tus archivos.');
                        return;
                    }

                    const transfer = new DataTransfer();
                    transfer.items.add(file);
                    input.files = transfer.files;
                    showPreview(file);
                }

                input.addEventListener('change', function () {
                    const file = input.files[0];
                    if (file && validateImage(file)) {
                        showPreview(file);
                    } else if (file) {
                        input.value = '';
                    }
                });

                dropzone.addEventListener('keydown', function (event) {
                    if (event.key === 'Enter' || event.key === ' ') {
                        event.preventDefault();
                        input.click();
                    }
                });

                ['dragenter', 'dragover'].forEach(function (eventName) {
                    dropzone.addEventListener(eventName, function (event) {
                        event.preventDefault();
                        dropzone.classList.add('is-dragging');
                    });
                });

                ['dragleave', 'drop'].forEach(function (eventName) {
                    dropzone.addEventListener(eventName, function (event) {
                        event.preventDefault();
                        dropzone.classList.remove('is-dragging');
                    });
                });

                dropzone.addEventListener('drop', function (event) {
                    const file = Array.from(event.dataTransfer.files).find(function (item) {
                        return allowedTypes.includes(item.type);
                    });

                    if (file) {
                        selectImage(file);
                    } else {
                        showError('Suelta una imagen JPG, PNG o WebP.');
                    }
                });

                uploader.addEventListener('paste', function (event) {
                    const clipboardItems = Array.from(event.clipboardData.items);
                    const imageItem = clipboardItems.find(function (item) {
                        return item.kind === 'file' && allowedTypes.includes(item.type);
                    });
                    const file = imageItem ? imageItem.getAsFile() : null;

                    if (file) {
                        event.preventDefault();
                        selectImage(file);
                    }
                });

                clearButton.addEventListener('click', function () {
                    input.value = '';
                    clearError();

                    if (previewUrl) {
                        URL.revokeObjectURL(previewUrl);
                        previewUrl = null;
                    }

                    previewImage.src = existingSrc;
                    previewImage.alt = existingSrc ? 'Imagen actual del producto' : '';
                    fileName.textContent = existingSrc ? 'Imagen actual' : '';
                    preview.classList.toggle('d-none', !existingSrc);
                    clearButton.hidden = true;
                });
            });
        });
    </script>
@endPushOnce
