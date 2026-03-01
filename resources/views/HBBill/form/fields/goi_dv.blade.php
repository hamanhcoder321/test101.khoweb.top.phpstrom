<style>
    /* === Auto expand table khi có service === */
    #serviceTable {
        width: 100%;
    }

    .table-responsive {
        overflow-x: visible;
    }

    .input-select {
        font-size: 1.25rem;
        width: 180px;
    }

    .input-xs {
        font-size: 1.25rem;
        width: 60px;
    }

    .input-dsc {
        font-size: 1.25rem;
        width: 120px;
    }
    .input-prc {
        font-size: 1.25rem;
        width: 140px;
    }

    /* select VAT gọn lại */
    #serviceTable select.vat {
        width: 60px;
        padding: 3px 4px;
        font-size: 14px;
    }

    /* canh giữa */
    #serviceTable td {
        vertical-align: middle;
        white-space: nowrap;
    }

    /*.note {*/
    /*    font-size: 0.9rem;*/
    /*    min-width: 160px;*/
    /*}*/

    .note-cell {
        min-width: 180px;
        cursor: pointer;
    }

    .note-preview {
        font-size: 0.9rem;
        color: #333;
        padding: 4px 6px;
        border-radius: 4px;
    }

    .note-preview.empty {
        color: #999;
        font-style: italic;
    }

    .note-preview:hover {
        background: #f1f3f5;
    }

    .note-textarea {
        font-size: 0.9rem;
    }


</style>
<div class="kt-portlet">
    <div class="kt-portlet__head">
        <div class="kt-portlet__head-label">
            <h3 class="kt-portlet__head-title">
                Danh sách sản phẩm
            </h3>
        </div>
        <div class="kt-portlet__head-toolbar">
            <button type="button" class="btn btn-sm btn-brand" onclick="addServiceRow()">
                <i class="la la-plus"></i> Thêm dịch vụ
            </button>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table " id="serviceTable">
            <thead>
            <tr>
                <th>Hạng mục</th>
                <th scope="col" class="text-center">Đơn giá</th>
                <th scope="col" class="text-center">SL</th>
                <th scope="col" class="text-center">Thành tiền</th>
                <th scope="col" class="text-center">Giảm giá</th>
                <th scope="col" class="text-center">Giá trị sau giảm</th>
                <th scope="col" class="text-center">VAT (%)</th>
                <th scope="col" class="text-center">Tiền VAT</th>
                <th scope="col" class="text-center">Tổng Thanh Toán</th>
                <th scope="col" class="text-center">Ghi chú</th>
                <th width="40"></th>
            </tr>
            </thead>


            <tbody>
            @if(isset($bill_items))
                @foreach($bill_items as $index => $item)
                    <tr class="service-row" data-index="{{ $index }}">
                        <td>
                            <select class="form-control service-select input-select">
                                @foreach($service_list as $sv)
                                    <option value="{{ $sv->id }}"
                                            data-price="{{ $sv->price }}"
                                            {{ $sv->id == $item->service_id ? 'selected' : '' }}>
                                        {{ $sv->name_vi }}
                                    </option>
                                @endforeach
                            </select>
                            <input type="hidden" name="services[{{ $index }}][service_id]"
                                   class="service_id" value="{{ $item->service_id }}">
                        </td>

                        <td>
                            <input type="text" class="form-control text-right price input-prc money"
                                   value="{{ $item->unit_price }}" >
                            <input type="hidden" name="services[{{ $index }}][unit_price]"
                                   class="unit_price" value="{{ $item->unit_price }}">
                        </td>

                        <td>
                            <input type="number" class="form-control text-center qty input-xs"
                                   value="{{ $item->quantity }}" min="1">
                            <input type="hidden" name="services[{{ $index }}][quantity]"
                                   class="quantity" value="{{ $item->quantity }}">
                        </td>

                        <td class="text-right amount">{{ number_format($item->subtotal) }}</td>

                        <td>
                            <input type="text" class="form-control text-right discount input-dsc money"
                                   value="{{ $item->discount_price }}">
                            <input type="hidden" name="services[{{ $index }}][discount_price]"
                                   class="discount_price" value="{{ $item->discount_price }}">
                        </td>

                        <td class="text-right after_discount">
                            {{ number_format($item->total_price) }}
                        </td>

                        <td>
{{--                            <select class="form-control text-center vat">--}}
{{--                                <option value="0" {{ $item->vat == 0 ? 'selected' : '' }}>0%</option>--}}
{{--                                <option value="8" {{ $item->vat == 8 ? 'selected' : '' }}>8%</option>--}}
{{--                                <option value="10" {{ $item->vat == 10 ? 'selected' : '' }}>10%</option>--}}
{{--                            </select>--}}
                            <select class="form-control text-center vat">
                                <option value="0" {{ $item->vat == 0 ? 'selected' : '' }}>Không chịu thuế</option>
                                <option value="0" {{ $item->vat == 0 ? 'selected' : '' }}>0%</option>
                                <option value="5" {{ $item->vat == 5 ? 'selected' : '' }}>5%</option>
                                <option value="8" {{ $item->vat == 8 ? 'selected' : '' }}>8%</option>
                                <option value="10" {{ $item->vat == 10 ? 'selected' : '' }}>10%</option>
                            </select>
                            <input type="hidden" name="services[{{ $index }}][vat]"
                                   class="vat_value" value="{{ $item->vat }}">
                        </td>

                        <td class="text-right vat_money">
                            {{ number_format($item->vat_price) }}
                        </td>

                        <td class="text-right total">
                            {{ number_format($item->final_price) }}
                        </td>

                        <td class="note-cell">
                            <div class="note-preview {{ empty($item->note) ? 'empty' : '' }}">
                                {{ $item->note ?: '+ Ghi chú' }}
                            </div>

                            <textarea class="form-control note-textarea d-none"
                                      rows="2"
                                      placeholder="Nhập ghi chú...">{{ $item->note ?? '' }}</textarea>

                            <input type="hidden"
                                   name="services[{{ $index }}][note]"
                                   class="note_value"
                                   value="{{ $item->note ?? '' }}">
                        </td>



                        <td>
                            <button type="button" class="btn btn-danger"
                                    onclick="this.closest('tr').remove(); recalcGrandTotal();">×</button>
                        </td>
                    </tr>
                @endforeach
            @endif
            </tbody>


            <tfoot>
                <tr class="font-weight-bold bg-light">
                    <td colspan="8" class="text-right">TỔNG CỘNG</td>
                    <td class="text-right" id="grandTotal">0</td>
                    <td></td>
                </tr>
            </tfoot>

        </table>
    </div>
</div>

<script>
    let serviceIndex = document.querySelectorAll('.service-row').length;
    function addServiceRow() {
        let index = serviceIndex++;
        let row = `
<tr class="service-row" data-index="${index}">
    <td>
        <select class="form-control service-select">
            <option value="">-- Chọn dịch vụ --</option>
            @foreach($service_list as $sv)
        <option value="{{ $sv->id }}" data-price="{{ $sv->price }}">
                    {{ $sv->name_vi }}
        </option>
@endforeach
        </select>
        <input type="hidden" name="services[${index}][service_id]" class="service_id">
    </td>

    <td>
       <input type="text" class="form-control price money" readonly>
        <input type="hidden" name="services[${index}][unit_price]" class="unit_price">
    </td>

    <td>
        <input type="number" class="form-control qty" value="1">
        <input type="hidden" name="services[${index}][quantity]" class="quantity">
    </td>

    <td class="amount">0</td>

    <td>
        <input type="text" class="form-control discount money" value="0">
        <input type="hidden" name="services[${index}][discount_price]" class="discount_price">
    </td>

    <td class="after_discount">0</td>

    <td>
       <select class="form-control vat">
            <option value="0">Không chịu thuế</option>
            <option value="0">0%</option>
            <option value="5">5%</option>
            <option value="8">8%</option>
            <option value="10" selected>10%</option>
        </select>

        <input type="hidden" name="services[${index}][vat]" class="vat_value">
    </td>

    <td class="vat_money">0</td>
    <td class="total">0</td>

   <td class="note-cell">
    <div class="note-preview empty">+ Ghi chú</div>

    <textarea class="form-control note-textarea d-none"
              rows="2"
              placeholder="Nhập ghi chú..."></textarea>

    <input type="hidden"
           name="services[${index}][note]"
           class="note_value"
           value="">
</td>




    <td>
        <button type="button" class="btn btn-danger"
            onclick="this.closest('tr').remove(); recalcGrandTotal();">×</button>
    </td>
</tr>`;

        document.querySelector('#serviceTable tbody')
            .insertAdjacentHTML('beforeend', row);
    }

</script>
<script>
    function recalcGrandTotal() {
        let sum = 0;

        document.querySelectorAll('#serviceTable tbody tr').forEach(row => {
            let totalText = row.querySelector('.total')?.innerText || '0';
            let total = parseFloat(totalText.replace(/\./g, '').replace(',', '.')) || 0;
            sum += total;
        });

        document.getElementById('grandTotal').innerText =
            sum.toLocaleString('vi-VN');
    }
</script>

<script>
    function recalcRow(row) {
        let index = row.dataset.index;

        let price = parseMoney(row.querySelector('.price')?.value || '0');
        let qty   = parseInt(row.querySelector('.qty')?.value || 0);
        let discount = parseMoney(row.querySelector('.discount')?.value || '0');

        let amount = price * qty;
        if (discount > amount) discount = amount;

        let afterDiscount = amount - discount;
        let vatPercent = parseFloat(row.querySelector('.vat')?.value || 0);
        let vatMoney = afterDiscount * vatPercent / 100;
        let total = afterDiscount + vatMoney;

        row.querySelector('.amount').innerText = amount.toLocaleString('vi-VN');
        row.querySelector('.after_discount').innerText = afterDiscount.toLocaleString('vi-VN');
        row.querySelector('.vat_money').innerText = vatMoney.toLocaleString('vi-VN');
        row.querySelector('.total').innerText = total.toLocaleString('vi-VN');

        row.querySelector('.unit_price').value = price;
        row.querySelector('.quantity').value = qty;
        row.querySelector('.discount_price').value = discount;
        row.querySelector('.vat_value').value = vatPercent;

        recalcGrandTotal();
    }

    // Chọn service → set price
    document.addEventListener('change', function (e) {
        if (e.target.classList.contains('service-select')) {
            let row = e.target.closest('tr');
            let price = e.target.selectedOptions[0].dataset.price || 0;

            let priceInput = row.querySelector('.price');
            priceInput.value = formatMoney(price.toString());

            row.querySelector('.unit_price').value = price;
            row.querySelector('.service_id').value = e.target.value;
            recalcRow(row);
        }
    });

    document.addEventListener('change', function (e) {
        if (e.target.classList.contains('vat')) {
            recalcRow(e.target.closest('tr'));
        }
    });


    // Thay đổi số lượng / VAT / giảm giá
    document.addEventListener('input', function (e) {
        if (
            e.target.classList.contains('qty') ||
            e.target.classList.contains('vat') ||
            e.target.classList.contains('discount') ||
            e.target.classList.contains('price')
        ) {
            recalcRow(e.target.closest('tr'));
        }
    });

    // Load trang
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('#serviceTable tbody tr').forEach(row => {
            let price = row.querySelector('.unit_price')?.value || 0;
            let discount = row.querySelector('.discount_price')?.value || 0;

            let priceInput = row.querySelector('.price');
            let discountInput = row.querySelector('.discount');

            if (priceInput) priceInput.value = formatMoney(price.toString());
            if (discountInput) discountInput.value = formatMoney(discount.toString());

            recalcRow(row);
        });

        autoExpandServiceTable();
    });

</script>

<script>
    function formatVND(number) {
        if (!number) return '0';
        return Number(number).toLocaleString('vi-VN');
    }

    function syncPriceFormat(row) {
        let priceInput = row.querySelector('.price');
        let hiddenPrice = row.querySelector('.unit_price');

        if (!priceInput || !hiddenPrice) return;

        let rawValue = hiddenPrice.value || 0;
        priceInput.value = formatVND(rawValue);
    }
</script>

{{--<script>--}}
{{--    document.addEventListener('input', function (e) {--}}
{{--        if (e.target.classList.contains('note')) {--}}
{{--            let row = e.target.closest('tr');--}}
{{--            row.querySelector('.note_value').value = e.target.value;--}}
{{--        }--}}
{{--    });--}}
{{--</script>--}}
<script>
    document.addEventListener('click', function (e) {
        // Click vào preview → mở textarea
        if (e.target.classList.contains('note-preview')) {
            let cell = e.target.closest('.note-cell');
            let textarea = cell.querySelector('.note-textarea');

            e.target.classList.add('d-none');
            textarea.classList.remove('d-none');
            textarea.focus();
        }
    });

    // Khi rời textarea → lưu & ẩn
    document.addEventListener('blur', function (e) {
        if (e.target.classList.contains('note-textarea')) {
            let cell = e.target.closest('.note-cell');
            let preview = cell.querySelector('.note-preview');
            let hidden = cell.querySelector('.note_value');

            let value = e.target.value.trim();

            hidden.value = value;
            preview.textContent = value || '+ Ghi chú';
            preview.classList.toggle('empty', !value);

            e.target.classList.add('d-none');
            preview.classList.remove('d-none');
        }
    }, true);
</script>
<script>
    function autoExpandServiceTable() {
        let hasService = false;

        document.querySelectorAll('#serviceTable .service-select').forEach(select => {
            if (select.value) {
                hasService = true;
            }
        });

        document
            .getElementById('serviceTable')
            .classList.toggle('expand', hasService);
    }

    // Khi chọn service
    document.addEventListener('change', function (e) {
        if (e.target.classList.contains('service-select')) {
            autoExpandServiceTable();
        }
    });

    // Khi load trang (edit case)
    document.addEventListener('DOMContentLoaded', autoExpandServiceTable);
</script>
<script>
    function formatMoney(val) {
        return val
            .replace(/\D/g, '')
            .replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    function parseMoney(val) {
        return parseInt(val.replace(/\./g, '')) || 0;
    }

    // Format realtime khi gõ
    document.addEventListener('input', function (e) {
        if (!e.target.classList.contains('money')) return;

        let input = e.target;
        let row = input.closest('tr');

        let cursor = input.selectionStart;
        let raw = parseMoney(input.value).toString();
        let formatted = formatMoney(raw);

        input.value = formatted;

        // sync hidden
        if (input.classList.contains('price')) {
            row.querySelector('.unit_price').value = raw;
        }

        if (input.classList.contains('discount')) {
            row.querySelector('.discount_price').value = raw;
        }

        // giữ con trỏ
        let diff = formatted.length - raw.length;
        input.setSelectionRange(cursor + diff, cursor + diff);

        recalcRow(row);
    });
</script>

