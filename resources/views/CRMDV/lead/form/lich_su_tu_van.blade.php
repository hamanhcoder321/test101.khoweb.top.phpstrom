<style type="text/css">
    .log_action .kt-radio {
        padding-left: 25px;
    }
    .log_action .kt-radio-list {
        display: inline-block;
        margin-right: 20px;
    }
</style>
<div class="kt-portlet">
    <div class="kt-form">
        <div class="kt-portlet__body">

            <div class="log_action">
                <label>Lưu lịch sử tư vấn</label>
                <div class="dat-lich-hen" style="float: right;">
                    <?php
                    $field = ['name' => 'dating', 'type' => 'custom', 'field' => 'CRMDV.lead.form.dating', 'class' => 'required', 'label' => 'Đặt hẹn ngày tương tác', 'value' => @$result->dating];

                    ?>
                    @include($field['field'], ['field' => $field])
                </div>
                <div class="form-group-div quick_note form-group col-md-12" style="margin-bottom: 10px;" id="form-group-name">
                    <div class="kt-radio-list mt-3">
                        <label class="kt-radio">
                            <input type="radio" name="quick_note" data-value="Sai số"
                                   value=""> Sai số
                            <span></span>
                        </label>
                    </div>
                    <div class="kt-radio-list mt-3">
                        <label class="kt-radio">
                            <input type="radio" name="quick_note" data-value="Thuê bao"
                                   value=""> Thuê bao
                            <span></span>
                        </label>
                    </div>
                    <div class="kt-radio-list mt-3">
                        <label class="kt-radio">
                            <input type="radio" name="quick_note" data-value="Không nghe"
                                   value=""> Không nghe
                            <span></span>
                        </label>
                    </div>
                    <div class="kt-radio-list mt-3">
                        <label class="kt-radio">
                            <input type="radio" name="quick_note" data-value="Cúp ngang"
                                   value=""> Cúp ngang
                            <span></span>
                        </label>
                    </div>
                    <div class="kt-radio-list mt-3">
                        <label class="kt-radio">
                            <input type="radio" name="quick_note" data-value="Ko có nhu cầu"
                                   value=""> Ko có nhu cầu
                            <span></span>
                        </label>
                    </div>
                    <div class="kt-radio-list mt-3">
                        <label class="kt-radio">
                            <input type="radio" name="quick_note" data-value="Bận"
                                   value=""> Bận
                            <span></span>
                        </label>
                    </div>
                    <div class="kt-radio-list mt-3">
                        <label class="kt-radio">
                            <input type="radio" name="quick_note" data-value="Bận & nhắn sau"
                                   value=""> Bận & nhắn sau
                            <span></span>
                        </label>
                    </div>
                    <div class="kt-radio-list mt-3">
                        <label class="kt-radio">
                            <input type="radio" name="quick_note" data-value="Quan tâm"
                                   value=""> Quan tâm
                            <span></span>
                        </label>
                    </div>
                </div>
                <div class="form-group-div form-group col-md-12" style="margin-bottom: 10px;" id="form-group-name">
                    <div class="col-xs-12">
                        <input type="text" name="log_name" placeholder="Chủ đề" class="form-control required" >
                    </div>
                </div>
                <div class="form-group-div form-group col-md-12" id="form-group-name">
                    <div class="col-xs-12">
                        <textarea type="text" placeholder="Nội dung tư vấn" name="log_note" class="form-control required" ></textarea>
                    </div>
                </div>
                <div class="form-group-div form-group col-md-12" id="form-group-name">
                    @if(@$result->status == 'Thả nổi')
                        <p style="color: red; font-weight: bold;">Nhớ chuyển trạng thái sang "Đang chăm sóc"</p>
                    @endif

                    @if (\Auth::guard('admin')->user()->super_admin != 1 && in_array(@$result->status, ['Đang chăm sóc', 'Tạm dừng', 'Đã ký HĐ']) && strpos(@$result->saler_ids, '|'.\Auth::guard('admin')->user()->id.'|') === false)
                        @include('CRMDV.lead.form.partials.nut_submit_ls_tu_van')
                    @else

                    @endif

                </div>
                <script type="text/javascript">
                    $('.quick_note label.kt-radio').click(function() {

                        var input_val = $(this).find('input[type=radio]').data('value');
                        console.log(input_val);
                        $('textarea[name=log_note]').val(input_val);
                    });
                </script>

            </div>

            @if(isset($result))
                <div class="log_logs">
                        <?php
                        $logs = \App\CRMDV\Models\LeadContactedLog::where('type', 'lead')->where('lead_id', @$result->id)->orderBy('id', 'desc')->get();
                        ?>
                    @foreach($logs as $log)
                        <hr>

                            <?php
                            // [SỬA] Kiểm tra nếu là log hệ thống thì tô màu nền cho dễ nhìn
                            $is_system_log = in_array($log->title, ['Đổi trạng thái', 'Đổi đánh giá', 'Đổi sale phụ trách', 'Quan tâm lại']);
                            $bg_style = $is_system_log ? 'background-color: #fff8dd; border-left: 3px solid #ffb822; padding: 10px;' : '';
                            $title_color = $is_system_log ? '#ffb822' : '#000';
                            ?>

                        <div class="log-item" data-id="{{ $log->id }}" style="color: #000; {{ $bg_style }}">
                            <i></i>
                            <div class="log-content">
                                <span style="color: {{ $title_color }};"><strong>{{ $log->title }}</strong></span>
                                <p style="font-size: 13px; margin: 0;">{!! $log->note !!}</p>
                            </div>
                            <i style="font-size: 11px;">{{ date('H:i d/m/Y', strtotime($log->created_at)) }}   - Bởi: {{ @$log->admin->name }}</i>
                        </div>
                    @endforeach
                    <hr>
                    <div class="log-item" data-id="" style="color: #000;">
                        <i></i>
                        <div class="log-content">
                            <p style="font-size: 13px; margin: 0;">Tạo mới</p>
                        </div>
                        <i style="font-size: 11px;">{{ date('H:i d/m/Y', strtotime(@$result->created_at)) }}   - Bởi: {{ @$result->admin->name }}</i>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>