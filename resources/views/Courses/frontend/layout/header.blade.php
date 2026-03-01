<div id="header-nav" style="position: relative" class="">
    <nav id="app-main-navbar" class="navbar navbar-expand-lg navbar-light custom-navbar custom-navbar-sd" style="box-shadow: 0 1px 8px rgba(89, 120, 150, 0.2)">
        <div class="container px-sm-3 px-0">
            <div class="nav-left" style="flex: auto 1">

                <div class="d-flex nav-left-b">

                    <a  class="navbar-brand logo-nav ml-lg-0 Header_Image_Logomobile mr-2" href="/dao-tao-noi-bo">
                        {{--                                <img src="{{ \URL::asset('backend/image_dao_tao_noi_bo/logo.jpg')}}" data-src="{{ \URL::asset('backend/image_dao_tao_noi_bo/logo.jpg')}}" class="lazyload d-none d-md-block" alt="Gitiho" width="100px" height="34px">--}}
                        {!! Eventy::filter('aside.logo', '<img alt="'.@$settings['name'].'" class="lazy"
            style="width:60px"
            src = "'.@\App\Http\Helpers\CommonHelper::getUrlImageThumb(@$settings['logo'],100,null).'"
          data-src="'.@\App\Http\Helpers\CommonHelper::getUrlImageThumb(@$settings['logo'],100,null).'"/>') !!}
                        <img src="{{ \URL::asset('backend/image_dao_tao_noi_bo/placehover_logo.png')}}" data-src="/android-icon-96x96.png" class="lazyload d-md-none" alt="Gitiho" style="width: 40px" width="40px" height="40px">
                    </a>



                    <div class="form-search my-auto mr-3 d-lg-block d-none w-100">

                        <form method="GET" action="{{ route('dao-tao-noi-bo') }}" accept-charset="UTF-8" style="height: 43px; margin-bottom: 7px;" id="navSearchForm">
                            <div class="d-flex search-f" style="max-width: 453px; height: 43px">
                                        <span class="twitter-typeahead" style="position: relative; display: inline-block;">
{{--                                            <input class="search-typeahead color_label w-100 tt-hint" type="text" readonly="" autocomplete="off" spellcheck="false" tabindex="-1" dir="ltr" style="position: absolute; top: 0px; left: 0px; border-color: transparent; box-shadow: none; opacity: 1; background: none 0% 0% / auto repeat scroll padding-box border-box rgb(236, 236, 236);">--}}
                                            <input class="search-typeahead color_label w-100 tt-input" placeholder="Tìm kiếm theo tiêu đề" name="keyword" type="text" autocomplete="off" spellcheck="false" dir="auto" style="position: relative; vertical-align: top; background-color: transparent;">
{{--                                            <pre aria-hidden="true" style="position: absolute; visibility: hidden; white-space: pre; font-family: Muli, sans-serif; font-size: 14px; font-style: normal; font-variant: normal; font-weight: 400; word-spacing: 0px; letter-spacing: 0px; text-indent: 0px; text-rendering: auto; text-transform: none;"></pre>--}}
                                            {{--                                            <div class="tt-menu" style="position: absolute; top: 100%; left: 0px; z-index: 100; display: none;">--}}
                                            {{--                                                <div class="tt-dataset tt-dataset-states"></div>--}}
                                            {{--                                            </div>--}}
                                        </span>
                                <div class="d-flex group-btn-search">
                                    <div class="border-search my-auto mx-1"></div>
                                    <button class="button-search" type="submit">
                                        <img data-src="{{ \URL::asset('backend/image_dao_tao_noi_bo/search.svg')}}" src="{{ \URL::asset('backend/image_dao_tao_noi_bo/search.svg')}}" class="lazyload" alt="Tìm kiếm khóa học giảng viên" width="18px" height="18px">
                                    </button>
                                </div>

                            </div>
                            <input name="level" type="hidden">
                            <input name="price" type="hidden">
                            <input name="order" type="hidden">
                        </form>
                    </div>
                </div>
            </div>

            <div class="nav-button">
                <ul class="navbar-nav mr-auto pull-right">

                    <li class="nav-item item-active d-lg-none mr-3 my-auto">
                        <div class="position-relative dropdown">
                            <p class="pointer mb-0" onclick="showSearchMb()">
                                <img data-src="{{ \URL::asset('backend/image_dao_tao_noi_bo/search.svg')}}" src="{{ \URL::asset('backend/image_dao_tao_noi_bo/search.svg')}}" class="lazyload" alt="Tìm kiếm khóa học giảng viên" width="18px" height="18px">
                            </p>
                        </div>
                    </li>
                    <li class="nav-item item-active ml-3 show-lg">
                        <div class="position-relative dropdown user-logined no-after">

                            <div class="btn-group no-user-select no-after dropdown-toggle pointer" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <img class="img-circle lazyload" id="Header_Image_Avatar" src="{{ \URL::asset('filemanager/userfiles/'.$admin->image)}}" data-src="{{ \URL::asset('filemanager/userfiles/'.$admin->image)}}" alt="Khôi Nguyễn Bá">






                            </div>
                            <div class="dropdown-menu dropdown-menu-right dropdown-menu-custom animate slideIn" style="cursor: default">
                                <div class="dropdown-item d-flex align-items-center" style="pointer-events: none; user-select: none; cursor: default">
                                    <div class="mr-3">
                                        <img class="img-circle" src="{{ \URL::asset('filemanager/userfiles/'.$admin->image)}}" alt="{{$admin->name}}">
                                    </div>
                                    <div class="">
                                        <strong>{{$admin->name}}</strong>
                                        <br>
                                        <span>
											{{$admin->email}}
										</span>
                                    </div>
                                </div>
                                <a class="dropdown-item font-weight-bold" rel="nofollow noreferrer" href="{{route('admin.profile')}}">Thông tin tài khoản
                                    {{--                                            <span class="round-4 text-white fw-700 px-1 bg_vip f-16">PRO</span>--}}
                                </a>





                                <a class="dropdown-item font-weight-bold" rel="nofollow noreferrer" href="/admin/dashboard" id="Header_Textlink_Logout">Về trang hệ thống</a>
                                <a class="dropdown-item font-weight-bold" rel="nofollow noreferrer" href="/admin/logout" id="Header_Textlink_Logout">Đăng xuất</a>

                            </div>
                        </div>
                    </li>

                    <li class="nav-item item-active ml-3 my-auto hide-lg">
                        <div class="bar-mobile" onclick="GitihoV2.mobileSidebarOpen()" id="Header_Button_Barmobile">
                            <div class="d-flex flex-column">
                                <img src="{{ \URL::asset('backend/image_dao_tao_noi_bo/icon_menu_black.png')}}" class="lazyload" data-src="{{ \URL::asset('backend/image_dao_tao_noi_bo/icon_menu_black.png')}}" alt="" width="26px" height="26px">
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="link-bar">
        <div class="nav-after navbar-expand-lg px-3 position-relative" style="min-height: 44px;height: auto">
            <div class="show-search-mb navbar-expand-lg d-lg-none p-3">
                <div class="container px-sm-3 px-0">
                    <form method="GET" action="{{ route('dao-tao-noi-bo') }}" accept-charset="UTF-8">
                        <div class="d-flex search-f search-f-mb">
                            <input class="color_label w-100" placeholder="Tìm kiếm khóa học giảng viên" name="keyword" type="text">
                            {{--                                    <button type="submit" class="btn btn-primary">Tìm kiếm</button>--}}
                            <div class="d-flex group-btn-search">
                                <button class="button-search" type="submit">
                                    <img alt="Tìm kiếm khóa học giảng viên" data-src="{{ \URL::asset('backend/image_dao_tao_noi_bo/search.svg')}}" class="lazyload" src="{{ \URL::asset('backend/image_dao_tao_noi_bo/search.svg')}}" width="18px" height="18px">
                                </button>
                            </div>

                        </div>
                    </form>
                </div>
            </div>

            <div class="container px-sm-3 px-0">
                <div class="d-md-block d-none" style="margin: 0 -0.7rem;">
                    <ul class="link-bar--categories list-unstyled justify-content-start">
                        <li class="link-bar--category text-nowrap">
                            <div class="link-bar--triangle position-relative w-100 h-100">
                                <a class="link-bar--nav-button fw-600" href="{{route('dao-tao-noi-bo')}}">
                                    Trang chủ
                                </a>
                            </div>
                        </li>
                        <li class="link-bar--category text-nowrap">
                            <div class="link-bar--triangle position-relative w-100 h-100">
                                <a class="link-bar--nav-button fw-600" href="{{route('dao-tao-noi-bo.lo-trinh-hoc')}}">
                                    Lộ trình học
                                </a>
                            </div>
                        </li>
                        <li class="link-bar--category text-nowrap">
                            <div class="link-bar--triangle position-relative w-100 h-100">
                                <a class="link-bar--nav-button fw-600" href="#">
                                    Bài cần học
                                </a>
                            </div>
                        </li>
                        <li class="link-bar--category text-nowrap">
                            <div class="link-bar--triangle position-relative w-100 h-100">
                                <a class="link-bar--nav-button fw-600" href="{{route('dao-tao-noi-bo.bai-can-thi')}}">
                                    Bài cần thi
                                </a>
                            </div>
                        </li>


                    </ul>
                </div>
                <div class="d-md-none">
                    <div class="d-flex justify-content-between" style="height: 44px">
                        <div class="my-auto">
                            <div class="btn-group">
                                <a title="Đăng nhập" class="text-white" id="LinkBar_Button_Login" rel="nofollow" href="https://gitiho.com/auth/login">Đăng ký/ Đăng nhập</a>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>
    <div class="nav-menu--show-course d-none">
        <div style="max-width: 273px;min-width:273px;width:273px;overflow-y:auto;max-height: calc(75vh - 1rem);" class="px-4">
            <ul class="list-unstyled nav-level-two">
                <li>
                    <div class="nav-level-item py-2">
                        <div style="height: 21px;background: #d5d5d5"></div>
                    </div>
                </li>
                <li>
                    <div class="nav-level-item py-2">
                        <div style="height: 21px;background: #d5d5d5"></div>
                    </div>
                </li>
                <li>
                    <div class="nav-level-item py-2">
                        <div style="height: 21px;background: #d5d5d5"></div>
                    </div>
                </li>
                <li>
                    <div class="nav-level-item py-2">
                        <div style="height: 21px;background: #d5d5d5"></div>
                    </div>
                </li>
                <li>
                    <div class="nav-level-item py-2">
                        <div style="height: 21px;background: #d5d5d5"></div>
                    </div>
                </li>
                <li>
                    <div class="nav-level-item py-2">
                        <div style="height: 21px;background: #d5d5d5"></div>
                    </div>
                </li>
                <li>
                    <div class="nav-level-item py-2">
                        <div style="height: 21px;background: #d5d5d5"></div>
                    </div>
                </li>
                <li>
                    <div class="nav-level-item py-2">
                        <div style="height: 21px;background: #d5d5d5"></div>
                    </div>
                </li>
                <li>
                    <div class="nav-level-item py-2">
                        <div style="height: 21px;background: #d5d5d5"></div>
                    </div>
                </li>
            </ul>
        </div>

        <div style="border-right: 1px solid #DCE2EE;"></div>

        <div style="overflow-y:auto;max-height: calc(75vh - 1rem);" class="px-4 w-100">
            <div class="nav-level-three">
                <div class="row">
                    <div class="col-xl-4">
                        <div style="max-width: 225px">
                            <div class="nav-level-item py-2">
                                <div style="height: 21px;background: #d5d5d5"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4">
                        <div style="max-width: 225px">
                            <div class="nav-level-item py-2">
                                <div style="height: 21px;background: #d5d5d5"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4">
                        <div style="max-width: 225px">
                            <div class="nav-level-item py-2">
                                <div style="height: 21px;background: #d5d5d5"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4">
                        <div style="max-width: 225px">
                            <div class="nav-level-item py-2">
                                <div style="height: 21px;background: #d5d5d5"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4">
                        <div style="max-width: 225px">
                            <div class="nav-level-item py-2">
                                <div style="height: 21px;background: #d5d5d5"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4">
                        <div style="max-width: 225px">
                            <div class="nav-level-item py-2">
                                <div style="height: 21px;background: #d5d5d5"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4">
                        <div style="max-width: 225px">
                            <div class="nav-level-item py-2">
                                <div style="height: 21px;background: #d5d5d5"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4">
                        <div style="max-width: 225px">
                            <div class="nav-level-item py-2">
                                <div style="height: 21px;background: #d5d5d5"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4">
                        <div style="max-width: 225px">
                            <div class="nav-level-item py-2">
                                <div style="height: 21px;background: #d5d5d5"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="nav-menu--show-store d-none">
        <div style="max-width: 273px;min-width:273px;width:273px;overflow-y:auto;max-height: 481px;" class="px-4">
            <ul class="list-unstyled nav-level-two">
                <li>
                    <div class="nav-level-item py-2">
                        <div style="height: 21px;background: #d5d5d5"></div>
                    </div>
                </li>
                <li>
                    <div class="nav-level-item py-2">
                        <div style="height: 21px;background: #d5d5d5"></div>
                    </div>
                </li>
                <li>
                    <div class="nav-level-item py-2">
                        <div style="height: 21px;background: #d5d5d5"></div>
                    </div>
                </li>
                <li>
                    <div class="nav-level-item py-2">
                        <div style="height: 21px;background: #d5d5d5"></div>
                    </div>
                </li>
                <li>
                    <div class="nav-level-item py-2">
                        <div style="height: 21px;background: #d5d5d5"></div>
                    </div>
                </li>
                <li>
                    <div class="nav-level-item py-2">
                        <div style="height: 21px;background: #d5d5d5"></div>
                    </div>
                </li>
                <li>
                    <div class="nav-level-item py-2">
                        <div style="height: 21px;background: #d5d5d5"></div>
                    </div>
                </li>
                <li>
                    <div class="nav-level-item py-2">
                        <div style="height: 21px;background: #d5d5d5"></div>
                    </div>
                </li>
                <li>
                    <div class="nav-level-item py-2">
                        <div style="height: 21px;background: #d5d5d5"></div>
                    </div>
                </li>
            </ul>
        </div>

        <div style="border-right: 1px solid #DCE2EE;"></div>

        <div style="overflow-y:auto;max-height: 481px;" class="px-4 w-100">
            <div class="nav-level-three">
                <div class="row">
                    <div class="col-xl-4">
                        <div style="max-width: 225px">
                            <div class="nav-level-item py-2">
                                <div style="height: 21px;background: #d5d5d5"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4">
                        <div style="max-width: 225px">
                            <div class="nav-level-item py-2">
                                <div style="height: 21px;background: #d5d5d5"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4">
                        <div style="max-width: 225px">
                            <div class="nav-level-item py-2">
                                <div style="height: 21px;background: #d5d5d5"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4">
                        <div style="max-width: 225px">
                            <div class="nav-level-item py-2">
                                <div style="height: 21px;background: #d5d5d5"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4">
                        <div style="max-width: 225px">
                            <div class="nav-level-item py-2">
                                <div style="height: 21px;background: #d5d5d5"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4">
                        <div style="max-width: 225px">
                            <div class="nav-level-item py-2">
                                <div style="height: 21px;background: #d5d5d5"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4">
                        <div style="max-width: 225px">
                            <div class="nav-level-item py-2">
                                <div style="height: 21px;background: #d5d5d5"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4">
                        <div style="max-width: 225px">
                            <div class="nav-level-item py-2">
                                <div style="height: 21px;background: #d5d5d5"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4">
                        <div style="max-width: 225px">
                            <div class="nav-level-item py-2">
                                <div style="height: 21px;background: #d5d5d5"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="nav-menu--show-contest d-none">
        <div class="px-4 py-2">
            <div class="px-xl-5">
                <p class="f-24 color_label">Gitiho contest</p>
                <p class="color_label f-16">
                    Cùng gitiho contest chinh phục kiến thức, khẳng định bản thân với hệ thống bài test phong phú, đầy đủ các chủ đề để bạn khám phá.
                </p>

                <div class="d-flex justify-content-between mt-4">
                    <div>
                        <a href="https://gitiho.com/contest" class="btn btn-gitiho round-4 f-14 text-nowrap">
                            Khám phá ngay
                        </a>
                    </div>
                    <div style="max-width: 389px;">
                        <img width="100%" class="lazyload" data-src="/frontend/img/gitiho_v2/icon/cartoon_testbank.svg" alt="">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="nav-menu--show-subscription d-none">
        <div class="mx-5 py-2 nav-menu--subscription w-100">
            <div class="px-xl-5 nav-menu--subscription-name">
                <p class="f-24 color_label">Chương trình hội viên Gitiho</p>
            </div>
            <div class="position-relative pb-5" style="z-index: 1;">
                <div class="mx-xl-5 nav-menu--subscription-content p-4 my-3">
                    <h4 class="nav-menu--subscription-header">
                        Gói Hội viên Gitiho: Trọn bộ khoá học theo Khung năng lực
                    </h4>
                    <div class="mt-2 nav-menu--subscription-block">
                        <div class="d-flex justify-content-between align-items-center nav-menu--subscription-menu">
                            <div class="nav-menu--subscription-list">
                                <p class="color_label f-16 nav-menu--subscription-item" style="max-width: 515px">
                                    <i class="far fa-check-circle nav-menu--subscription-item-icon"></i>
                                    <span class="nav-menu--subscription-item-text">480+ khóa học</span>
                                </p>
                                <p class="color_label f-16 nav-menu--subscription-item">
                                    <i class="far fa-check-circle nav-menu--subscription-item-icon"></i>
                                    <span class="nav-menu--subscription-item-text">15 Danh mục theo kỹ năng, ngành nghề</span>

                                </p>
                                <p class="color_label f-16 nav-menu--subscription-item">
                                    <i class="far fa-check-circle nav-menu--subscription-item-icon"></i>
                                    <span class="nav-menu--subscription-item-text">Năng lực Lõi, Năng lực Chuyên môn, Năng lực Bổ trợ, Năng lực Quản lý</span>
                                </p>
                            </div>
                            <div class="nav-menu--subscription-button">
                                <a href="https://gitiho.com/hoi-vien" class="btn btn-gitiho round-4 f-14 text-nowrap">
                                    Khám phá ngay
                                </a>
                                <p class="nav-menu--subscription-button-text mb-0">2000+ đã đăng ký</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mx-xl-5 nav-menu--subscription-content p-4 my-3">
                    <h4 class="nav-menu--subscription-header">
                        Gói Hội viên G-Skills: 03 kỹ năng bổ trợ thiết yếu
                    </h4>
                    <div class="mt-2 nav-menu--subscription-block">
                        <div class="d-flex justify-content-between align-items-center nav-menu--subscription-menu">
                            <div class="nav-menu--subscription-list">
                                <p class="color_label f-16 nav-menu--subscription-item" style="max-width: 515px">
                                    <i class="far fa-check-circle nav-menu--subscription-item-icon"></i>
                                    <span class="nav-menu--subscription-item-text">83 khóa học Tin học văn phòng</span>
                                </p>
                                <p class="color_label f-16 nav-menu--subscription-item">
                                    <i class="far fa-check-circle nav-menu--subscription-item-icon"></i>
                                    <span class="nav-menu--subscription-item-text">85 khóa học Kỹ năng mềm</span>

                                </p>
                                <p class="color_label f-16 nav-menu--subscription-item">
                                    <i class="far fa-check-circle nav-menu--subscription-item-icon"></i>
                                    <span class="nav-menu--subscription-item-text">29 khóa học Ngoại ngữ</span>

                                </p>
                            </div>
                            <div class="nav-menu--subscription-button">
                                <a href="https://gitiho.com/hoi-vien/gskill" class="btn btn-gitiho round-4 f-14 text-nowrap">
                                    Khám phá ngay
                                </a>
                                <p class="nav-menu--subscription-button-text mb-0">500+ đã đăng ký</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div style="max-width: 204px; bottom: -32px; right: -80px; z-index: -1;" class="position-absolute">
                    <img width="100%" class="lazyload" data-src="/frontend/img/gitiho_v2/banner_vip_user.png" alt="">
                </div>
            </div>


        </div>
    </div>
    <div class="nav-menu--show-biz d-none">
        <div class="px-4 py-2 w-100">
            <div class="px-xl-5">
                <p class="f-24 color_label">Gitiho for leading business</p>
                <p class="color_label f-16" style="max-width: 600px">
                    Tối ưu và đơn giản hóa hoạt động đào tạo tại doanh nghiệp. Sẵn sàng nền tảng, nội dung đào tạo cho tất cả các vị trí, bộ phận. Ứng dụng MIỄN PHÍ ngay vào doanh nghiệp chỉ với MỘT click.
                </p>

                <div>
                    <p class="color_label f-14 mb-1">Đã được tin tưởng sử dụng bởi các doanh nghiệp</p>
                    <div>
                        <img width="51px" src="{{ \URL::asset('backend/image_dao_tao_noi_bo/logo_avery.png')}}" alt="">
                        <img width="51px" src="{{ \URL::asset('backend/image_dao_tao_noi_bo/logo_vp.png')}}" alt="">
                        <img width="51px" src="{{ \URL::asset('backend/image_dao_tao_noi_bo/logo_topcv.png')}}" alt="">
                        <img width="51px" src="{{ \URL::asset('backend/image_dao_tao_noi_bo/logo_vcb.png')}}" alt="">
                        <img width="51px" src="{{ \URL::asset('backend/image_dao_tao_noi_bo/logo_vietin.png')}}" alt="">
                        <img width="51px" src="{{ \URL::asset('backend/image_dao_tao_noi_bo/logo_th.png')}}" alt="">
                    </div>
                </div>
                <div class="d-flex justify-content-between mt-4">
                    <div>
                        <a href="https://gitiho.com/landing-business" class="btn btn-gitiho round-4 f-14 text-nowrap">
                            Đăng ký miễn phí
                        </a>
                    </div>
                    <div style="max-width: 452px;">
                        <img width="100%" class="lazyload" data-src="{{ \URL::asset('backend/image_dao_tao_noi_bo/bg_header_body.png')}}" alt="">
                    </div>
                </div>
            </div>
        </div>
    </div></div>