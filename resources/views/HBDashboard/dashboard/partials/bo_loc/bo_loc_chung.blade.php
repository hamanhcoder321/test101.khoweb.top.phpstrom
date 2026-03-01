<!-- Filter Section -->
<head>
    <!-- Bootstrap 5 CSS -->
    <link
            href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css"
            rel="stylesheet"
    />
    <!-- Bootstrap Icons -->
    <link
            href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.0/font/bootstrap-icons.min.css"
            rel="stylesheet"
    />
    <!-- Google Fonts -->
    <link
            href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap"
            rel="stylesheet"
    />
</head>
<div class="custom-card p-4 mb-4">
    <form method="GET" action="" class="filter-form">
    <div class="row g-3">
        <!-- Select Nhân viên -->
        <div class="col-xl-3 col-md-6">
            @php
                $field = [
                    'name'          => 'admin_id',
                    'type'          => 'select2_ajax_model',
                    'class'         => 'form-control',
                    'label'         => 'Nhân viên',
                    'model'         => \App\Models\Admin::class,
                    'display_field' => 'name',
                    'object'        => 'admin',
                    'value'         => @$_GET['admin_id'],
                    'where'         => $whereCompany
                ];
            @endphp

            <label for="admin_id" class="form-label fw-semibold">Nhân viên</label>
            @include(config('core.admin_theme').".form.fields.".$field['type'], ['field' => $field])
        </div>

        <!-- Input Từ ngày -->
        <div class="col-xl-3 col-md-6">
            <label class="form-label fw-semibold">Từ ngày</label>
            <input type="date" name="from_date" class="form-control" value="{{ request('from_date', '2024-08-01') }}" />
        </div>

        <!-- Input Đến ngày -->
        <div class="col-xl-3 col-md-6">
            <label class="form-label fw-semibold">Đến ngày</label>
            <input type="date" name="to_date" class="form-control" value="{{ request('to_date', '2024-08-31') }}" />
        </div>

        <!-- Nút Filter và Clear -->
        <div class="col-xl-3 col-md-6 d-flex align-items-end gap-2">
            <button class="btn btn-primary btn-filter flex-fill">
                <i class="bi bi-funnel-fill me-2"></i>Lọc
            </button>
            <a href="/admin/dashboard" class="btn btn-clear">
                <i class="bi bi-x-circle me-2"></i>Xóa bộ lọc
            </a>
        </div>
    </div>
    </form>
</div>

<style>
    .custom-card {
        border: 1px solid #e2e8f0;
        border-radius: 1rem;
        box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1);
        transition: all 0.3s ease;
        background: white;
    }

    /* HOVER cho custom-card */
    .custom-card:hover {
        transform: scale(1.01);
        box-shadow: 0 10px 25px -3px rgb(0 0 0 / 0.1);
    }

    .btn-filter {
        background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
        border: none;
        font-weight: 600;
        padding: 0.75rem 1.5rem;
        border-radius: 0.5rem;
        transition: all 0.3s ease;
    }

    /* HOVER cho btn-filter - THIẾU ĐOẠN NÀY */
    .btn-filter:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px 0 rgb(59 130 246 / 0.4);
    }

    .btn-clear {
        border: 2px solid #dc2626;
        color: #dc2626;
        background: transparent;
        font-weight: 600;
        padding: 0.75rem 1.5rem;
        border-radius: 0.5rem;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-block;
        text-align: center;
    }

    /* HOVER cho btn-clear - THIẾU ĐOẠN NÀY */
    .btn-clear:hover {
        background: #dc2626;
        color: white;
        transform: translateY(-2px);
        text-decoration: none;
    }
</style>