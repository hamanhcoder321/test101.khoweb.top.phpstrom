@php
    // tránh lỗi nếu $permissions chưa được inject
    $permissions = $permissions ?? [];
    // lấy danh sách module đã bật
//    $enabled = json_decode(\App\Models\Setting::getValue('modules_active','[]'), true) ?: [];
    // nhóm "Hành chính - nhân sự" chỉ hiện khi có ít nhất 1 mục con bật
//    $hasHCNS = in_array('crm', $enabled, true) || in_array('timekeeper', $enabled, true);
@endphp

<style>
    @media (max-width: 768px) {
        #kt_aside_menu { overflow: auto !important; }
    }
</style>

<div class="kt-aside-menu-wrapper kt-grid__item kt-grid__item--fluid" id="kt_aside_menu_wrapper">
    <div id="kt_aside_menu" class="kt-aside-menu kt-scroll ps ps--active-y" data-ktmenu-vertical="1"
         data-ktmenu-scroll="1" data-ktmenu-dropdown-timeout="500" style="height: 191px; overflow: hidden;">
        <ul class="kt-menu__nav ">
            {!! Eventy::filter('aside_menu.dashboard_after', '') !!}

            <li class="kt-menu__section ">
                <h4 class="kt-menu__section-text">Nội bộ</h4>
                <i class="kt-menu__section-icon flaticon-more-v2"></i>
            </li>

            <li class="kt-menu__item kt-menu__item--submenu" aria-haspopup="true" data-ktmenu-submenu-toggle="hover">
                <a href="javascript:;" class="kt-menu__link kt-menu__toggle">
                        <span class="kt-menu__link-icon">
                            <!-- Folder svg giữ nguyên -->
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><g fill="none"><rect width="24" height="24"/><path d="M3.5,21 L20.5,21 C21.3284271,21 22,20.3284271 22,19.5 L22,8.5 C22,7.67157288 21.3284271,7 20.5,7 L10,7 L7.43933983,4.43933983 C7.15803526,4.15803526 6.77650439,4 6.37867966,4 L3.5,4 C2.67157288,4 2,4.67157288 2,5.5 L2,19.5 C2,20.3284271 2.67157288,21 3.5,21 Z" fill="#000" opacity=".3"/></g></svg>
                        </span>
                    <span class="kt-menu__link-text">Hành chính - nhân sự</span>
                    <i class="kt-menu__ver-arrow la la-angle-right"></i>
                </a>

                <div class="kt-menu__submenu" kt-hidden-height="80" style="display: none; overflow: hidden;">
                    <span class="kt-menu__arrow"></span>
                    <ul class="kt-menu__subnav">
                        <li class="kt-menu__item " aria-haspopup="true">
                            <a href="/admin/hradmin" class="kt-menu__link ">
                                <i class="kt-menu__link-bullet kt-menu__link-bullet--dot"><span></span></i>
                                <span class="kt-menu__link-text">Nhân sự</span>
                            </a>
                        </li>

                        <li class="kt-menu__item " aria-haspopup="true">
                            <a href="/admin/timekeeper/bao-cao" class="kt-menu__link ">
                                <i class="kt-menu__link-bullet kt-menu__link-bullet--dot"><span></span></i>
                                <span class="kt-menu__link-text">Chấm công</span>
                            </a>
                        </li>

                        <li class="kt-menu__item " aria-haspopup="true">
                            <a href="/admin/admin/thong-ke" class="kt-menu__link ">
                                <i class="kt-menu__link-bullet kt-menu__link-bullet--dot"><span></span></i>
                                <span class="kt-menu__link-text">Sinh nhật</span>
                            </a>
                        </li>

                        <li class="kt-menu__item " aria-haspopup="true">
                            <a href="/admin/penalty_ticket" class="kt-menu__link ">
                                <i class="kt-menu__link-bullet kt-menu__link-bullet--dot"><span></span></i>
                                <span class="kt-menu__link-text">Phiếu phạt</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            {{-- Khối "Tài khoản" giữ nguyên theo $permissions --}}
            <li class="kt-menu__item kt-menu__item--submenu" aria-haspopup="true" data-ktmenu-submenu-toggle="hover">
                <a href="javascript:;" class="kt-menu__link kt-menu__toggle">
                    <i class="kt-menu__link-icon flaticon2-avatar"></i>
                    <span class="kt-menu__link-text">Tài khoản</span>
                    <i class="kt-menu__ver-arrow la la-angle-right"></i>
                </a>
                <div class="kt-menu__submenu " kt-hidden-height="80" style="display: none; overflow: hidden;">
                    <span class="kt-menu__arrow"></span>
                    <ul class="kt-menu__subnav">
                        @if(in_array('admin_view', $permissions))
                            {!! Eventy::filter('aside_menu.admin', ' <li class="kt-menu__item " aria-haspopup="true"><a
                                    href="/admin/admin" class="kt-menu__link "><i
                                        class="kt-menu__link-bullet kt-menu__link-bullet--dot"><span></span></i><span
                                        class="kt-menu__link-text">Tài khoản</span></a></li>') !!}
                        @endif
                        @if(in_array('role_view', $permissions))
                            {!! Eventy::filter('aside_menu.admin', ' <li class="kt-menu__item " aria-haspopup="true"><a
                                    href="/admin/role" class="kt-menu__link "><i
                                        class="kt-menu__link-bullet kt-menu__link-bullet--dot"><span></span></i><span
                                        class="kt-menu__link-text">Phân quyền</span></a></li>') !!}
                        @endif
                    </ul>
                </div>
            </li>

            {{-- Khối "Cấu hình" giữ nguyên theo $permissions --}}
            @if(in_array('setting', $permissions))
                <li class="kt-menu__section ">
                    <h4 class="kt-menu__section-text">Cấu hình</h4>
                    <i class="kt-menu__section-icon flaticon-more-v2"></i>
                </li>

                <li class="kt-menu__item kt-menu__item--submenu" aria-haspopup="true" data-ktmenu-submenu-toggle="hover">
                    <a href="javascript:;" class="kt-menu__link kt-menu__toggle">
                        <span class="kt-menu__link-icon">
                            <!-- settings svg -->
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><g fill="none"><rect width="24" height="24"/><path d="M5,8.6862915 L5,5 L8.6862915,5 L11.5857864,2.10050506 L14.4852814,5 L19,5 L19,9.51471863 L21.4852814,12 L19,14.4852814 L19,19 L14.4852814,19 L11.5857864,21.8994949 L8.6862915,19 L5,19 L5,15.3137085 L1.6862915,12 L5,8.6862915 Z M12,15 C13.6568542,15 15,13.6568542 15,12 C15,10.3431458 13.6568542,9 12,9 C10.3431458,9 9,10.3431458 9,12 C9,13.6568542 10.3431458,15 12,15 Z" fill="#000"/></g></svg>
                        </span>
                        <span class="kt-menu__link-text">Cấu hình</span>
                        <i class="kt-menu__ver-arrow la la-angle-right"></i>
                    </a>
                    <div class="kt-menu__submenu " kt-hidden-height="80" style="display: none; overflow: hidden;">
                        <span class="kt-menu__arrow"></span>
                        <ul class="kt-menu__subnav">
                            <li class="kt-menu__item " aria-haspopup="true" title="Quản lý bộ nhớ đệm">
                                <a href="/admin/setting" class="kt-menu__link ">
                                    <i class="kt-menu__link-bullet kt-menu__link-bullet--dot"><span></span></i>
                                    <span class="kt-menu__link-text">Cấu hình chung</span>
                                </a>
                            </li>
                            <li class="kt-menu__item " aria-haspopup="true" title="Xem lịch sử ghi nhận lỗi của hệ thống">
                                <a href="/admin/service" class="kt-menu__link ">
                                    <i class="kt-menu__link-bullet kt-menu__link-bullet--dot"><span></span></i>
                                    <span class="kt-menu__link-text">Sản phẩm/ dịch vụ</span>
                                </a>
                            </li>
                            <li class="kt-menu__item " aria-haspopup="true" title="Sao lưu dữ liệu hệ thống">
                                <a href="/admin/tag" class="kt-menu__link ">
                                    <i class="kt-menu__link-bullet kt-menu__link-bullet--dot"><span></span></i>
                                    <span class="kt-menu__link-text">Trường dữ liệu</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>


                <li class="kt-menu__item kt-menu__item--submenu" aria-haspopup="true" data-ktmenu-submenu-toggle="hover">
                    <a href="javascript:;" class="kt-menu__link kt-menu__toggle">
                        <span class="kt-menu__link-icon">
                            <!-- tools svg -->
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><g fill="none"><rect width="24" height="24"/><path d="M15.9497475,3.80761184 L13.0246125,6.73274681 C12.2435639,7.51379539 12.2435639,8.78012535 13.0246125,9.56117394 L14.4388261,10.9753875 C15.2198746,11.7564361 16.4862046,11.7564361 17.2672532,10.9753875 L20.1923882,8.05025253 C20.7341101,10.0447871 20.2295941,12.2556873 18.674559,13.8107223 C16.8453326,15.6399488 14.1085592,16.0155296 11.8839934,14.9444337 L6.75735931,20.0710678 C5.97631073,20.8521164 4.70998077,20.8521164 3.92893219,20.0710678 C3.1478836,19.2900192 3.1478836,18.0236893 3.92893219,17.2426407 L9.05556629,12.1160066 C7.98447038,9.89144078 8.36005124,7.15466739 10.1892777,5.32544095 C11.7443127,3.77040588 13.9552129,3.26588995 15.9497475,3.80761184 Z" fill="#000"/><path d="M16.6568542,5.92893219 L18.0710678,7.34314575 C18.4615921,7.73367004 18.4615921,8.36683502 18.0710678,8.75735931 L16.6913928,10.1370344 C16.3008685,10.5275587 15.6677035,10.5275587 15.2771792,10.1370344 L13.8629656,8.7228208 C13.4724413,8.33229651 13.4724413,7.69913153 13.8629656,7.30860724 L15.2426407,5.92893219 C15.633165,5.5384079 16.26633,5.5384079 16.6568542,5.92893219 Z" fill="#000" opacity=".3"/></g></svg>
                        </span>
                        <span class="kt-menu__link-text">Hệ thống</span>
                        <i class="kt-menu__ver-arrow la la-angle-right"></i>
                    </a>
                    <div class="kt-menu__submenu " kt-hidden-height="80" style="display: none; overflow: hidden;">
                        <span class="kt-menu__arrow"></span>
                        <ul class="kt-menu__subnav">
                            <li class="kt-menu__item " aria-haspopup="true" title="Quản lý bộ nhớ đệm">
                                <a href="/admin/cache" class="kt-menu__link ">
                                    <i class="kt-menu__link-bullet kt-menu__link-bullet--dot"><span></span></i>
                                    <span class="kt-menu__link-text">{{trans('admin.cache')}}</span>
                                </a>
                            </li>
                            <li class="kt-menu__item " aria-haspopup="true" title="Xem lịch sử ghi nhận lỗi của hệ thống">
                                <a href="/admin/error" class="kt-menu__link ">
                                    <i class="kt-menu__link-bullet kt-menu__link-bullet--dot"><span></span></i>
                                    <span class="kt-menu__link-text">{{trans('admin.history_error')}}</span>
                                </a>
                            </li>
                            <li class="kt-menu__item " aria-haspopup="true" title="Sao lưu dữ liệu hệ thống">
                                <a href="/admin/backup" class="kt-menu__link ">
                                    <i class="kt-menu__link-bullet kt-menu__link-bullet--dot"><span></span></i>
                                    <span class="kt-menu__link-text">{{trans('admin.data_backup')}}</span>
                                </a>
                            </li>
                            <li class="kt-menu__item " aria-haspopup="true" title="Nhập dữ liệu vào hệ thống nhanh bằng file excel">
                                <a href="/admin/import" class="kt-menu__link ">
                                    <i class="kt-menu__link-bullet kt-menu__link-bullet--dot"><span></span></i>
                                    <span class="kt-menu__link-text">Import</span>
                                </a>
                            </li>
                            <li class="kt-menu__item " aria-haspopup="true" title="Lịch sử hoạt động của các tài khoản">
                                <a href="/admin/admin_logs" class="kt-menu__link ">
                                    <i class="kt-menu__link-bullet kt-menu__link-bullet--dot"><span></span></i>
                                    <span class="kt-menu__link-text">{{trans('admin.history_admin')}}</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
            @endif

            <li class="kt-menu__item " aria-haspopup="true">
                <a href="/admin/logout" class="kt-menu__link ">
                    <span class="kt-menu__link-icon">
                        <!-- power svg -->
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><g fill="none"><rect width="24" height="24"/><path d="M7.62302337,5.30262097 C8.08508802,5.000107 8.70490146,5.12944838 9.00741543,5.59151303 C9.3099294,6.05357769 9.18058801,6.67339112 8.71852336,6.97590509 C7.03468892,8.07831239 6,9.95030239 6,12 C6,15.3137085 8.6862915,18 12,18 C15.3137085,18 18,15.3137085 18,12 C18,9.99549229 17.0108275,8.15969002 15.3875704,7.04698597 C14.9320347,6.73472706 14.8158858,6.11230651 15.1281448,5.65677076 C15.4404037,5.20123501 16.0628242,5.08508618 16.51836,5.39734508 C18.6800181,6.87911023 20,9.32886071 20,12 C20,16.418278 16.418278,20 12,20 C7.581722,20 4,16.418278 4,12 C4,9.26852332 5.38056879,6.77075716 7.62302337,5.30262097 Z" fill="#000"/><rect fill="#000" opacity=".3" x="11" y="3" width="2" height="10" rx="1"/></g></svg>
                    </span>
                    <span class="kt-menu__link-text">{{ trans('admin.logout') }}</span>
                </a>
            </li>
        </ul>

        <div class="ps__rail-x" style="left:0; bottom:0;"><div class="ps__thumb-x" tabindex="0" style="left:0; width:0;"></div></div>
        <div class="ps__rail-y" style="top:0; height:191px; right:3px;"><div class="ps__thumb-y" tabindex="0" style="top:0; height:40px;"></div></div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        <?php $uri = "$_SERVER[REQUEST_URI]";?>
        $("#kt_aside_menu a[href='{{ $uri }}']").parents('li').addClass('kt-menu__item--active');
        $("#kt_aside_menu a[href='{{ $uri }}']").parents('li').parents('li')
            .addClass('kt-menu__item--here kt-menu__item--open')
            .removeClass('kt-menu__item--active');
        $("#kt_aside_menu a[href='{{ $uri }}']").parents('li').parents('li')
            .find('.kt-menu__submenu').attr('style', '');
    });
</script>
