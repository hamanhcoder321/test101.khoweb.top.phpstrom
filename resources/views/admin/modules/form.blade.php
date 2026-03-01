@extends(config('core.admin_theme').'.template')

@section('main')
    @if(session('ok'))
        <div class="alert alert-success mx-3 mt-3" role="alert" style="max-width:980px">
            {{ session('ok') }}
        </div>
    @endif

    <div class="kt-container kt-container--fluid kt-grid__item kt-grid__item--fluid">
        <div class="kt-portlet">

            <div class="kt-portlet__head">
                <div class="kt-portlet__head-label">
                    <h3 class="kt-portlet__head-title">
                        Bật / tắt modules
                        <small class="text-muted d-block">Quản lý các module khả dụng trong hệ thống</small>
                    </h3>
                </div>
            </div>

            <div class="kt-portlet__body">
                <form id="form-modules" method="post" action="{{ route('admin.modules.save') }}">
                

                    {{-- Thanh công cụ --}}
                    <div class="d-flex flex-wrap align-items-center mb-4 gap-2" style="row-gap:10px;column-gap:10px">
                        <div class="input-group" style="max-width: 340px;">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="la la-search"></i></span>
                            </div>
                            <input id="module-search" type="text" class="form-control" placeholder="Tìm module (vd: crm, document, ...)" autocomplete="off">
                        </div>

                        <div class="btn-group" role="group" aria-label="Bulk actions">
                            <button type="button" class="btn btn-outline-secondary" id="btn-check-all">
                                Bật tất cả
                            </button>
                            <button type="button" class="btn btn-outline-secondary" id="btn-uncheck-all">
                                Tắt tất cả
                            </button>
                        </div>

                        <span class="ml-auto text-muted" id="module-counter">
                            Hiển thị: <strong>{{ count($valid ?? []) }}</strong> / <strong>{{ count($valid ?? []) }}</strong> modules
                        </span>
                    </div>

                    {{-- Danh sách modules --}}
                    <div class="row" id="module-list">
                        @foreach($valid as $m)
                            @php
                                $id = 'mod-'.preg_replace('/[^a-z0-9\-_]/i','-', $m);
                                $checked = in_array($m, $current ?? []) ? 'checked' : '';
                            @endphp
                            <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12 mb-3 module-item" data-name="{{ strtolower($m) }}">
                                <div class="card h-100 shadow-sm border-0">
                                    <div class="card-body d-flex align-items-center justify-content-between">
                                        <div>
                                            <div class="font-weight-bold text-uppercase mb-1" style="letter-spacing:.5px">{{ $m }}</div>
                                            <div class="text-muted small">Module: {{ strtolower($m) }}</div>
                                        </div>

                                        {{-- Metronic switch style --}}
                                        <span class="kt-switch kt-switch--sm kt-switch--icon">
                                            <label class="m-0">
                                                <input type="checkbox" name="modules[]" value="{{ $m }}" id="{{ $id }}" {{ $checked }}>
                                                <span></span>
                                            </label>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Footer hành động --}}
                    <div class="d-flex justify-content-end pt-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="la la-save"></i> Lưu thay đổi
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>

    {{-- Script nhỏ cho tìm kiếm & bulk actions --}}
    <script>
        (function () {
            var searchInput = document.getElementById('module-search');
            var list = document.getElementById('module-list');
            var items = list ? list.querySelectorAll('.module-item') : [];
            var counter = document.getElementById('module-counter');
            var btnCheckAll = document.getElementById('btn-check-all');
            var btnUncheckAll = document.getElementById('btn-uncheck-all');

            function updateCounter() {
                var total = items.length;
                var visible = 0;
                items.forEach(function (el) {
                    if (el.style.display !== 'none') visible++;
                });
                if (counter) {
                    counter.innerHTML = 'Hiển thị: <strong>'+visible+'</strong> / <strong>'+total+'</strong> modules';
                }
            }

            function filter() {
                var q = (searchInput.value || '').trim().toLowerCase();
                items.forEach(function (el) {
                    var name = el.getAttribute('data-name') || '';
                    el.style.display = (q === '' || name.indexOf(q) > -1) ? '' : 'none';
                });
                updateCounter();
            }

            if (searchInput) {
                searchInput.addEventListener('input', filter);
            }

            if (btnCheckAll) {
                btnCheckAll.addEventListener('click', function () {
                    items.forEach(function (el) {
                        if (el.style.display === 'none') return;
                        var input = el.querySelector('input[type="checkbox"]');
                        if (input) input.checked = true;
                    });
                });
            }

            if (btnUncheckAll) {
                btnUncheckAll.addEventListener('click', function () {
                    items.forEach(function (el) {
                        if (el.style.display === 'none') return;
                        var input = el.querySelector('input[type="checkbox"]');
                        if (input) input.checked = false;
                    });
                });
            }

            updateCounter();
        })();
    </script>
@endsection
