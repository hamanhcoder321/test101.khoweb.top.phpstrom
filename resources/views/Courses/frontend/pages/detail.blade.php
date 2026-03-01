@extends('Courses.frontend.layout.app')
@section('title')
    {{$course->name}}
@endsection
<style>
    .selected-lecture {
        color: blue; /* Màu nền được chọn */
        font-weight: bold; /* Hoặc các thuộc tính khác để làm nổi bật bài học đã chọn */
    }
    .include-video iframe{
        width: 900px;
        height: 500px;
        margin: auto;
        display: block;
    }
</style>
@section('content')


    <div class="position-relative">
        <div class="banner-preview2 p-3">
            <div class="container">
                <div class="banner--container mx-auto">
                    <div class="main-content breadcrumb-v2 breadcrumb-course pl-0 position-relative bg-transparent mb-4">




                    </div>
                    <div class="show-content--1080">
                        <div class="try-content mb-4">
                            <div class="position-relative d-flex" >
                                <img class="lazyload mx-auto" src="" width="100%" style="max-width: 600px;" data-src="" alt="Ebook Tuyệt đỉnh Excel - Khai phá 10 kỹ thuật ứng dụng Excel mà đại học không dạy bạn">
                            </div>

                        </div>
                    </div>
                    <div class="main-content">
                        <h1 class="text-white-change f-26 fw-600">{{$course->name}}</h1>
                        <div>
                        </div>
                    </div>



                    <ul class="detail-cou mt-4 main-content">
                        <li>
                            <img alt="Giảng viên" src="{{ \URL::asset('backend/image_dao_tao_noi_bo/icon-custom-1.svg')}}" width="36px" height="36px" class="lazyload my-auto" data-src="{{ \URL::asset('backend/image_dao_tao_noi_bo/icon-custom-1.svg')}}">
                            <div class="detail-cou-label">
                                <span class="text-white">{{$course->admin->name}}</span><br>
                                <span class="text-white"><a href="#teacher" class="text-white" style="color:#2997ff!important;"><u>HOBA-SOFT</u></a></span>
                            </div>
                        </li>
                        <li>
                            <img alt="Ngày update" src="{{ \URL::asset('backend/image_dao_tao_noi_bo/icon-custom-2.svg')}}" width="36px" height="36px" class="lazyload my-auto" data-src="{{ \URL::asset('backend/image_dao_tao_noi_bo/icon-custom-2.svg')}}">
                            <div class="detail-cou-label">
                                <span class="text-white">Ngày update</span><br>
                                <span class="text-white">{{$course->updated_at->format('d/m/Y')}}</span>
                            </div>
                        </li>

                    </ul>
                    <div class="show-content--1080">
                        <div class="py-3 " style="display: none">
                            <div class="d-flex justify-content-between">
                                <div class="d-flex">
                                    <p class="f-20 text-white mb-0 fw-700 sale-price-display-js" style="color: #d91c5c!important;" data-price-old="69000">69,000đ</p>
                                    <span class="ml-3 color_text2 line-through my-auto">99,000đ</span>
                                </div>

                                <div class="text-right my-auto text-white">
                                    Tiết kiệm <b class="text-white percent-display" data-percent-old="30%">30%</b>
                                </div>

                            </div>


                            <div class="mt-3">
                                <p class="text-white mb-2">
                                    <i class="far fa-alarm-clock mr-2"></i> Giá ưu đãi chỉ còn <b>1 ngày</b>
                                </p>

                                <div class="coupon-input-render">
                                    <div>
                                    </div>

                                </div>

                            </div>

                            <div class="button-cou-register">
                                <button class="btn btn-custom-second text-uppercase w-100 btn-block text-bold" style="padding: 0.375rem 0.75rem;" id="Preview_Button_Dangkyhocngay1" onclick="registerCourse(this,'white')">Đăng ký ngay</button>

                                <p class="text-center my-2 text-muted">Hoặc</p>
                                <a href="https://gitiho.com/hoi-vien?utm_source=Gitiho&amp;utm_campaign=Button_Course_Sub" target="_blank" rel="noreferrer nofollow noopener">
                                    <button class="btn btn-custom-white3 text-bold w-100 f-14 round-4" style="border: 1px solid #fff">
{{--                                        <img alt="" data-src="/frontend/img/gitiho_v2/icon/fire_iris.gif" src="/frontend/img/gitiho_v2/icon/fire_iris.gif" width="16px" class="lazyload mr-1" style="margin-bottom: 9px">--}}
                                        Gói thành viên chỉ 199,000đ/tháng
                                    </button>
                                </a>
                                <div class="form-registed-user text-center mt-3">
                                    <div class="change-register hide" style="transform: translateY(50px);">
                                        <i class="fas fa-users-medical text-white"></i>
                                        <span class="name_register text-white"> Võ An Phước Thiện </span>
                                        <span class="text-white">vừa đăng ký</span>
                                    </div>
                                    <div class="pending-register"></div>
                                </div>
                            </div>

                        </div>

                        <div class="py-3" style="border-top: 1px solid #fff">
                            <p class="fw-700 text-white mb-1">Đăng ký cho doanh nghiệp</p>
                            <p class="mb-3 text-white">Giúp nhân viên của bạn truy cập không giới hạn 450 khoá học, mọi lúc, mọi nơi</p>

                            <a href="https://business.gitiho.com/?utm_source=Gitiho&amp;utm_campaign=Button_Course_Gbiz" target="_blank" rel="noreferrer nofollow noopener">
                                <button class="btn btn-custom-white text-white w-100 round-4" style="border: 1px solid #fff">Tư vấn cho doanh nghiệp</button>
                            </a>

                        </div>

                        <div class="d-flex justify-content-center mt-2 pointer" onclick="openCodeDiscount(this)">
                            <u class="mb-0 pointer fw-700" style="color:#6CB1E5!important;">Áp dụng mã giảm giá</u>
                        </div>
                        <div class="form-code box-voucher voucher-item my-3 border-0" style="border-radius: 6px;">
                            <div class="d-lg-flex justify-content-between">
                                <div class="mt-0 d-flex justify-content-between w-100 bg-white" style="border: 1px solid #90A0B7;border-radius: 6px">
                                    <div class="my-auto mx-2">
                                        <i class="far fa-badge-percent"></i>
                                    </div>
                                    <input type="text" class="form-control coupon-input f-14 border-0 p-0 fw-600" style="height:41px;outline: none;box-shadow: none;border-radius: 6px;border-color: #B8B8B8!important;" name="coupon-input" placeholder="Mã giảm giá">

                                    <button type="button" class="fw-600 btn btn-gitiho-default ml-2 round-4 button-apply-coupon text-nowrap my-2" data-course="" style="outline: none;box-shadow: none;border-left: 1px solid #90A0B7;padding: 0 0.7rem;background:white;color: #90A0B7;font-size: 14px;border-radius: 0 4px 4px 0!important;" onclick="applyCoupon(this)">
                                        Áp dụng
                                    </button>
                                </div>
                            </div>

                        </div>

                    </div>
                </div>



            </div>
        </div>

        <div class="body-content">
            <div class="my-5">
                <div class="container">

                </div>
            </div>

            <div class="my-5">
                <div class="container">

                </div>
            </div>

            <div class="my-5">
                <div class="container">
                    <div class="main-content">
                        <p class="f-32 fw-500 mb-3">Mô tả khoá học</p>
                        <div class="show-more--container">
                            <span id="show-more-check" data-type="checkbox" data-checked="" style="display: none;"></span>
                            <div class="cou-description ck-content show-more--with-gradient show-more--content  f-16">
                                <p><span style="background-color:transparent;color:#000000;">{!!$course->content!!}</span></p>
                            </div>

                            <button class="btn btn-none f-14 p-0 color_label fw-700" onclick="toggleDescription('show-more-check')" style="box-shadow: none;outline: none">
                                <span class="show-more">Xem tất cả </span>
                                <span class="show-less">Ẩn bớt </span>
                            </button>
                        </div>



                    </div>
                </div>
            </div>

            <div class="my-5">
                <div class="container">
                    <div class="main-content">
                        <p class="f-32 fw-500 mb-3">Khoá học này sẽ có:</p>

                        <div>
                            <div class="row mx-n2">
                                <div class="col-lg-4 col-xl-3 col-md-6 px-2">
                                    <div class="border px-3 py-2">
                                        <div class="d-flex mb-2">
                                            <i class="fas fa-play-circle f-28 mr-2"></i>
                                            <p class="my-auto fw-700">Video</p>
                                        </div>
                                        @php
                                            $total_video =0;
                                            $total_lesson=0;
                                            $total_house = 0;
                                            $total_link = 0;
                                        @endphp
                                        @foreach($chapter as $c)
                                            @foreach($c->lesson as $ls)

                                                @php
                                                    if($ls->link_docs){
                                                        $total_link++;
                                                    }if($ls->iframe){
                                                        $total_video++;
                                                    }
                                                    $total_house += $ls->time;
                                                    $total_lesson++;
                                                @endphp
                                            @endforeach
                                        @endforeach

                                        @php
                                            $hours = floor($total_house / 3600);
                                            $minutes = floor(($total_house % 3600) / 60);
                                            $time_formatted = sprintf("%02d giờ %02d phút", $hours, $minutes);
                                         @endphp
                                        <p class="mb-0 fw-700">{{$total_video}}  video</p>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-xl-3 col-md-6 mt-3 mt-md-0 px-2">
                                    <div class="border px-3 py-2">
                                        <div class="d-flex mb-2">
                                            <i class="far fa-newspaper f-28 mr-2"></i>
                                            <p class="my-auto fw-700">Article</p>
                                        </div>
                                        <p class="mb-0 fw-700">{{$total_link}} link tài liệu</p>
                                    </div>
                                </div>
{{--                                <div class="col-lg-4 col-xl-3 col-md-6 mt-3 mt-lg-0 px-2">--}}
{{--                                    <div class="border px-3 py-2">--}}
{{--                                        <div class="d-flex mb-2">--}}
{{--                                            <i class="far fa-file-archive f-28 mr-2"></i>--}}
{{--                                            <p class="my-auto fw-700">Material</p>--}}
{{--                                        </div>--}}
{{--                                        <p class="mb-0 fw-700">24 tài liệu đính kèm</p>--}}
{{--                                    </div>--}}
{{--                                </div>--}}
{{--                                <div class="col-lg-4 col-xl-3 col-md-6 mt-3 mt-xl-0 px-2">--}}
{{--                                    <div class="border px-3 py-2">--}}
{{--                                        <div class="d-flex mb-2">--}}
{{--                                            <i class="far fa-file-signature f-28 mr-2"></i>--}}
{{--                                            <p class="my-auto fw-700">Exam questions</p>--}}
{{--                                        </div>--}}
{{--                                        <p class="mb-0 fw-700">1 đề thi ghi nhớ kiến thức</p>--}}
{{--                                    </div>--}}
{{--                                </div>--}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="my-5">
                <div class="container">
                    <div class="main-content include-video">
                        @if($lesson)
                            @if($lesson->iframe)
{{--                                <div id="iframe-container"></div>--}}
                                {!! $lesson->iframe !!}
                            @elseif($lesson->link_docs)
                                <a href="{{$lesson->link_docs}}">Link docs</a>
                            @else
                                Bài giảng chưa có tài liệu
                            @endif
                        @endif

                    </div>
                </div>
            </div>
            <div class="my-5">
                <div class="container">
                    <div class="main-content">
                        <p class="f-32 fw-500 mb-3">Nội dung khoá học</p>
                        <div class="d-lg-flex justify-content-between">
                            <p class="mb-2">{{count($chapter)}} Chương . {{$total_lesson}} bài giảng </p>
                            <a class="fw-700" style="color: #0077c8;text-decoration: underline!important;" onclick="showToggleAllSection(this)" href="javascript:void(0)">Mở rộng tất cả các phần</a>
                        </div>

                        <div class="list-lectures">




                            <div class="list-lecture-item accordion w-100 my-3" id="accordionLecture">
                                @foreach($chapter as $c)
                                    <div class="content-list " style="margin-bottom: -1px">

                                        <div class="item-group item-section pointer Preview_Button_Chuong " data-toggle="collapse" data-target="#collapse_0" id="heading_0">
                                            <svg aria-hidden="true" focusable="false" class="mr-3 icon-updown" style="width: 24px;height: 24px;fill: #344d6e">
                                                <use xlink:href="#icon-expand"></use>
                                            </svg>
                                            <h3 class="fw-500 section-title color_label fw-600">{{$c->name}}</h3>
                                            <div class="text-nowrap d-none d-lg-block ml-4">
                                                {{count($c->lesson)}} bài giảng
{{--                                                •  4 phút--}}

                                            </div>

                                        </div>

                                        <div id="collapse_0" class="collapse show" aria-labelledby="heading_0">
                                            <div class="list-section-body">
                                                <ul class="list-unstyled mb-0">
                                                    @foreach($c->lesson as $ls)
                                                            <li id="item_lecture_15107" class="item-lecture d-flex justify-content-between">
                                                                <a href="{{route("dao-tao-noi-bo.detailLesson", ['id' => $course->id, 'lesson_id' => $ls->id])}}"   style="width: 100%"  onclick="toggleSelected(this)">

                                                                <div class="lecture-link  w-100">
{{--                                                                    <img alt="" class="mr-3 lazyload" width="14px" height="14px" src="/frontend/img/gitiho_v2/icon_play_page/icon-video.svg" data-src="/frontend/img/gitiho_v2/icon_play_page/icon-video.svg" style="margin-top: 3px">--}}
                                                                    @if($ls->iframe)
                                                                        <i class="fas fa-play-circle f-28 mr-2"></i>
                                                                    @else
                                                                        <i class="far fa-newspaper f-28 mr-2"></i>
                                                                    @endif
                                                                    <div class="lecture-title my-auto">
                                                                <span style="">
                                                                    <span style="white-space: nowrap;"
                                                                          @if($lesson)
                                                                              class="{{$ls->id == $lesson->id ? 'selected-lecture' : ''}}"
                                                                          @endif
                                                                    >{{$loop->iteration}}. {{$ls->name}}</span>
                                                                </span>
                                                                    </div>
                                                                    {{--                                                                <span class="duration ml-4 d-none d-lg-block">04:58</span>--}}
                                                                </div>
                                                                </a>
                                                            </li>

                                                    @endforeach



                                                </ul>
                                            </div>
                                        </div>
                                    </div>

                                @endforeach
{{--


                            </div>
                            <button type="button" class="button--show-more btn btn-custom-white w-100 rounded-0 fw-700" onclick="showMoreCourseContent(this)" style="border: 1px solid #333333" aria-label="8 phần nữa">
                                <span class="show-more-module--show-more">8 phần nữa</span>
                            </button>

                        </div>

                        <svg aria-hidden="true" style="position: absolute; width: 0; height: 0; overflow: hidden;" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                            <defs>
                                <symbol id="icon-expand" viewBox="0 0 24 24">
                                    <path d="M16.59 8.59L12 13.17 7.41 8.59 6 10l6 6 6-6-1.41-1.41z"></path>
                                </symbol>
                            </defs>
                        </svg>

                    </div>
                </div>
            </div>
            <div class="my-5" id="reviews">
                <div class="container">
                    <div class="main-content">
                        {{--                            <div>BÌNH LUẬN</div>--}}

                        {{--                            <div class="fb-comments" data-href="https://developers.facebook.com/docs/plugins/comments#configurator" data-width="" data-numposts="5"></div>--}}

                    </div>
                </div>
            </div>
                    <div class="my-5">
                        <?php
// Lấy URL hiện tại
                        $current_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
                        ?>

                        <div class="mt-5">BÌNH LUẬN</div>
                        <div class="fb-comments" data-href="<?php echo $current_url; ?>" data-width="" data-numposts="5"></div>


                        <div class="m-auto btn--more-review" style="max-width: 200px">
                        </div>
                    </div>
            <div class="my-5" id="teacher">
                <div class="container">
                    <div class="main-content">
                        <p class="f-32 fw-500 mb-3">Người tạo:</p>

                        <div>
                            <div class="info-text mb-auto mt-4 mt-md-auto">
                                <p class="color-label fw-600 mb-2 fw-700">
                                    <a href="https://gitiho.com/teacher/82540-g-learning" class="color_label"><u>{{$course->admin->name}}</u></a>
                                    {{--                                        <img alt="Giảng viên" data-src="{{ \URL::asset('backend/image_dao_tao_noi_bo/icon-profile-police.svg')}}" width="16px" class="lazyload" src="/frontend/img/gitiho_v2/icon/icon-profile-police.svg">--}}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="my-5">
                <div class="container">
                    <div class="main-content">
                        <p class="f-32 fw-500 mb-3">Bài học liên quan</p>
                        <div>
                            <div class="course-comparison--show-more">
                                <span id="show-more" data-type="checkbox" class="d-none" data-checked=""></span>
                                <div class="show-more-module--content" style="max-height: 30rem;">
                                    <div class="course-comparison--container">
                                        @foreach($related_courses as $related_course)
                                            <div>
                                                <div class="course-comparison--course-container">
                                                    <div class="course-comparison--main-content">
                                                        <a href="{{route("dao-tao-noi-bo.detail", $related_course->id)}}" class="course-comparison--course-title color_label fw-700 f-16">
                                                            {{$related_course->name}}
                                                        </a>
                                                    </div>
                                                    <div class="course-comparison--image-wrapper">
                                                        <a href="{{route("dao-tao-noi-bo.detail", $related_course->id)}}" class="course-comparison--course-title color_label fw-700 f-16">

                                                            <img class="lazyload" src="{{$related_course->image? \URL::asset('filemanager/userfiles/'.$related_course->image):\URL::asset('backend/image_dao_tao_noi_bo/course-default.jpg') }}" width="64px" height="64px" data-src="{{$related_course->image? \URL::asset('filemanager/userfiles/'.$related_course->image):\URL::asset('backend/image_dao_tao_noi_bo/course-default.jpg') }}" alt="EXG02 - Thủ thuật Excel cập nhật hàng tuần cho dân văn phòng">
                                                        </a>

                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                </div>
                            </div>


                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="slider-menu show-content--1080">
            <div class="container">
                <div class="slider-menu--container justify-content-between">
                    <div class="w-100 justify-content-end slider-menu--show-transactional">
                        <div class="fw-700 f-18 my-auto mr-3 slider-menu--price">
                            69,000đ
                        </div>
                        <div class="slider-menu--btn">
                            <button class="btn btn-gitiho round-4 fw-700 fw-16 w-100 text-nowrap d-flex justify-content-center" onclick="registerCourse(this,'black')">
                                Đăng ký ngay
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div class="container">
            <div class="paid-course">
                <div class="bg-white round-10 paid-course--container">
                    <div>
                        <div class="try-content">
                            <div class="position-relative" id="buttonVideoTry"
{{--                                 onclick="showVideoTryFirst(4310,this)"--}}
                            >
                                <img class="lazyload" src="{{$course->image? \URL::asset('filemanager/userfiles/'.$course->image):\URL::asset('backend/image_dao_tao_noi_bo/course-default.jpg') }}" width="340px" style="max-width: 100%;border-radius: 10px 10px 0 0" data-src="{{$course->image? \URL::asset('filemanager/userfiles/'.$course->image):\URL::asset('backend/image_dao_tao_noi_bo/course-default.jpg') }}" alt="{{$course->name}}">
                            </div>

                        </div>

                        <div class="py-3 px-3">



                            {{--                                <p class="color_primary mb-2">--}}
                            {{--                                    <i class="far fa-alarm-clock mr-2"></i> Giá ưu đãi chỉ còn <b>1 ngày</b>--}}
                            {{--                                </p>--}}

                            {{--                                <div class="d-flex justify-content-between">--}}

                            {{--                                    <div class="d-flex">--}}
                            {{--                                        <p class="f-20 color_label mb-0 fw-700 sale-price-display-js" data-price-old="69,000đ">69,000đ</p>--}}
                            {{--                                        <span class="ml-3 color_text2 line-through my-auto">99,000đ</span>--}}
                            {{--                                    </div>--}}

                            {{--                                    <div class="text-right my-auto">--}}
                            {{--                                        Tiết kiệm <b class="color_primary percent-display" data-percent-old="30%">30%</b>--}}
                            {{--                                    </div>--}}

                            {{--                                </div>--}}



                            {{--                                <div class="button-cou-register" data-role="user">--}}
                            {{--                                    <button class="btn btn-custom-second text-uppercase btn-block text-bold" style="padding: 0.375rem 0.75rem;" id="Preview_Button_Dangkyhocngay2" onclick="registerCourse(this,'white')">--}}
                            {{--                                        Đăng ký ngay--}}
                            {{--                                    </button>--}}
                            {{--                                    <p class="text-center my-2 text-muted">Hoặc</p>--}}
                            {{--                                    <a class="btn btn-custom-white3 w-100 f-14 round-4 text-bold" href="https://gitiho.com/hoi-vien?utm_source=Gitiho&amp;utm_campaign=Button_Course_Sub" target="_blank" rel="noreferrer nofollow noopener">--}}
                            {{--                                        <img alt="" data-src="/frontend/img/gitiho_v2/icon/fire_iris.gif" src="/frontend/img/gitiho_v2/icon/fire_iris.gif" width="16px" class="lazyload mr-1" style="margin-bottom: 9px">--}}
                            {{--                                        Gói thành viên chỉ 199,000đ/tháng--}}
                            {{--                                    </a>--}}

                            {{--                                    <div class="form-registed-user text-center mt-3">--}}
                            {{--                                        <div class="change-register hide" style="transform: translateY(50px);">--}}
                            {{--                                            <i class="fas fa-users-medical color_label"></i>--}}
                            {{--                                            <span class="name_register color_label"> Võ An Phước Thiện </span>--}}
                            {{--                                            <span class="color_label">vừa đăng ký</span>--}}
                            {{--                                        </div>--}}
                            {{--                                        <div class="pending-register"></div>--}}
                            {{--                                    </div>--}}
                            {{--                                </div>--}}

                            {{--                                <div class="list-about-course my-3 ">--}}
                            {{--                                    <div class="about-course-item d-flex pb-3" style="border-bottom: 1px solid rgba(193, 209, 231, 0.47)">--}}
                            {{--                                        <div class="mx-4 d-flex">--}}
                            {{--                                            <img src="/frontend/img/gitiho_v2/icon/icon-loan.svg" width="24px" height="24px" class="lazyload my-auto mr-2" data-src="/frontend/img/gitiho_v2/icon/icon-loan.svg">--}}
                            {{--                                            <span> Hoàn tiền nếu không hài lòng</span>--}}
                            {{--                                        </div>--}}
                            {{--                                    </div>--}}
                            {{--                                    <div class="about-course-item d-flex py-3" style="border-bottom: 1px solid rgba(193, 209, 231, 0.47)">--}}
                            {{--                                        <div class="mx-4 d-flex">--}}
                            {{--                                            <img src="/frontend/img/gitiho_v2/icon/icon-pc-play-media.svg" width="24px" height="24px" class="lazyload my-auto mr-2" data-src="/frontend/img/gitiho_v2/icon/icon-pc-play-media.svg">--}}
                            {{--                                            <span> 1 bài giảng, 2+ giờ học</span>--}}
                            {{--                                        </div>--}}
                            {{--                                    </div>--}}
                            {{--                                    <div class="about-course-item d-flex py-3" style="border-bottom: 1px solid rgba(193, 209, 231, 0.47)">--}}
                            {{--                                        <div class="mx-4 d-flex">--}}
                            {{--                                            <img src="/frontend/img/gitiho_v2/icon/icon-i-sync.svg" width="24px" height="24px" class="lazyload my-auto mr-2" data-src="/frontend/img/gitiho_v2/icon/icon-i-sync.svg">--}}
                            {{--                                            <span> Học online, mọi lúc mọi nơi</span>--}}
                            {{--                                        </div>--}}
                            {{--                                    </div>--}}
                            {{--                                    <div class="about-course-item d-flex py-3" style="border-bottom: 1px solid rgba(193, 209, 231, 0.47)">--}}
                            {{--                                        <div class="mx-4 d-flex">--}}
                            {{--                                            <img src="/frontend/img/gitiho_v2/icon/icon-pc-mb.svg" width="24px" height="24px" class="lazyload my-auto mr-2" data-src="/frontend/img/gitiho_v2/icon/icon-pc-mb.svg">--}}
                            {{--                                            <span> Học trên máy tính, điện thoại</span>--}}
                            {{--                                        </div>--}}
                            {{--                                    </div>--}}
                            {{--                                    <div class="about-course-item d-flex pt-3">--}}
                            {{--                                        <div class="mx-4 d-flex">--}}
                            {{--                                            <img src="/frontend/img/gitiho_v2/icon/icon-book-bookmark.svg" width="24px" height="24px" class="lazyload my-auto mr-2" data-src="/frontend/img/gitiho_v2/icon/icon-book-bookmark.svg">--}}
                            {{--                                            <span> Sở hữu khóa học trọn đời</span>--}}
                            {{--                                        </div>--}}
                            {{--                                    </div>--}}
                            {{--                                </div>--}}
                        </div>

                        <div class="p-3" style="border-top: 1px solid rgba(193, 209, 231, 0.47)">
                            @if($course->link)
                                <p class="fw-700 color_label mb-1">
                                    <a href="{{$course->link}}"  target="_blank">Link tài liệu</a>
                                </p>
                            @else
                                <p class="fw-700 color_label mb-1">
                                    <a href="">Chưa có link</a>
                                </p>
                            @endif
                            <p class="fw-700 color_label mb-1">
                                <a href="{{$course->link}}"  target="_blank">{{$count_lesson}} bài giảng, {{$count_chapter}} chương</a>
                            </p>
                            {{--                                <p class="mb-3 color_label">Giúp nhân viên của bạn truy cập không giới hạn 500+ khoá học, mọi lúc, mọi nơi</p>--}}

                            {{--                                <a href="https://business.gitiho.com/?utm_source=Gitiho&amp;utm_campaign=Button_Course_Gbiz" target="_blank" rel="noreferrer nofollow noopener">--}}
                            {{--                                    <button class="btn btn-custom-white w-100 round-4" style="border: 1px solid #333333">Tư vấn cho doanh nghiệp</button>--}}
                            {{--                                </a>--}}
                        </div>

                        {{--                            <div class="form-code-discount p-3 d-flex flex-column" style="border-top: 1px solid rgba(193, 209, 231, 0.47)">--}}
                        {{--                                <div class="d-flex justify-content-center pointer" onclick="openCodeDiscount(this)">--}}
                        {{--                                    <u class="mb-0 pointer fw-700" style="color:#6CB1E5!important;">Áp dụng mã giảm giá</u>--}}
                        {{--                                </div>--}}
                        {{--                                <div class="form-code box-voucher voucher-item my-3 border-0" style="border-radius: 6px;">--}}
                        {{--                                    <div class="d-lg-flex justify-content-between">--}}
                        {{--                                        <div class="mt-0 d-flex justify-content-between w-100 bg-white" style="border: 1px solid #90A0B7;border-radius: 6px">--}}
                        {{--                                            <div class="my-auto mx-2">--}}
                        {{--                                                <i class="far fa-badge-percent"></i>--}}
                        {{--                                            </div>--}}
                        {{--                                            <input type="text" class="form-control coupon-input f-14 border-0 p-0 fw-600" style="height:41px;outline: none;box-shadow: none;border-radius: 6px;border-color: #B8B8B8!important;" name="coupon-input" placeholder="Mã giảm giá">--}}

                        {{--                                            <button type="button" class="fw-600 btn btn-gitiho-default ml-2 round-4 button-apply-coupon text-nowrap my-2" data-course="" style="outline: none;box-shadow: none;border-left: 1px solid #90A0B7;padding: 0 0.7rem;background:white;color: #90A0B7;font-size: 14px;border-radius: 0 4px 4px 0!important;" onclick="applyCoupon(this)">--}}
                        {{--                                                Áp dụng--}}
                        {{--                                            </button>--}}
                        {{--                                        </div>--}}
                        {{--                                    </div>--}}
                        {{--                                </div>--}}
                        {{--                            </div>--}}

                    </div>
                </div>
            </div>
        </div>
    </div>

{{--{!! $chapter[0]->lesson[0]->iframe !!}--}}
    {{--modal--}}
{{--    <div class="modal fade bd-show-video-try-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" style="display: none;" aria-hidden="true">--}}
{{--        <div class="modal-dialog modal-lg" style="max-width: 600px;">--}}
{{--            <div class="modal-content px-4 pt-4" style="background: #1e1e1c">--}}
{{--                <i class="fal fa-times pointer" data-dismiss="modal" style="position: absolute;color: white;right: 1.5rem;top: 2rem;font-size: 22px;"></i>--}}
{{--                <div class="text-white">--}}
{{--                    <p class="f-18 my-1">Nội dung bài học</p>--}}
{{--                    <p class="font-weight-bold text-content-try mb-4">1. Giới thiệu những đặc điểm của file Excel có chứa VBA</p>--}}
{{--                </div>--}}
{{--                <div class="player-relative rounded-0 position-relative">--}}
{{--                    <div class="lecture_content" style="position: relative"><div class="embed-responsive embed-responsive-16by9">--}}
{{--                            <div class="embed-responsive-item">--}}
{{--                                <div tabindex="0" class="plyr plyr--full-ui plyr--video plyr--html5 plyr--fullscreen-enabled plyr--pip-supported plyr__poster-enabled plyr--paused" style="--plyr-color-main: #d91b5c;">--}}
{{--                                    <div class="plyr__controls">--}}
{{--                                        <button class="plyr__controls__item plyr__control" type="button" data-plyr="play" aria-label="Play">--}}
{{--                                            <svg class="icon--pressed" aria-hidden="true" focusable="false">--}}
{{--                                                <use xlink:href="#plyr-pause"></use>--}}
{{--                                            </svg>--}}
{{--                                            <svg class="icon--not-pressed" aria-hidden="true" focusable="false">--}}
{{--                                                <use xlink:href="#plyr-play"></use>--}}
{{--                                            </svg>--}}
{{--                                            <span class="label--pressed plyr__sr-only">Pause</span>--}}
{{--                                            <span class="label--not-pressed plyr__sr-only">Play</span>--}}
{{--                                        </button>--}}
{{--                                        <div class="plyr__controls__item plyr__progress__container">--}}
{{--                                            <div class="plyr__progress">--}}
{{--                                                <input data-plyr="seek" type="range" min="0" max="100" step="0.01" value="0" autocomplete="off" role="slider" aria-label="Seek" aria-valuemin="0" aria-valuemax="161.522666" aria-valuenow="13.64064" id="plyr-seek-261" aria-valuetext="00:13 of 02:41" style="--value: 8.45%;" seek-value="65.59046841576429">--}}
{{--                                                <progress class="plyr__progress__buffer" min="0" max="100" value="100" role="progressbar" aria-hidden="true">% buffered</progress>--}}
{{--                                                <span class="plyr__tooltip" style="left: 65.5905%;">01:45</span>--}}
{{--                                            </div>--}}
{{--                                        </div>--}}
{{--                                        <div class="plyr__controls__item plyr__time--current plyr__time" aria-label="Current time">-02:27</div>--}}
{{--                                        <div class="plyr__controls__item plyr__volume">--}}
{{--                                            <button type="button" class="plyr__control" data-plyr="mute">--}}
{{--                                                <svg class="icon--pressed" aria-hidden="true" focusable="false"><use xlink:href="#plyr-muted"></use></svg>--}}
{{--                                                <svg class="icon--not-pressed" aria-hidden="true" focusable="false"><use xlink:href="#plyr-volume"></use></svg>--}}
{{--                                                <span class="label--pressed plyr__sr-only">Unmute</span>--}}
{{--                                                <span class="label--not-pressed plyr__sr-only">Mute</span>--}}
{{--                                            </button>--}}
{{--                                            <input data-plyr="volume" type="range" min="0" max="1" step="0.05" value="1" autocomplete="off" role="slider" aria-label="Volume" aria-valuemin="0" aria-valuemax="100" aria-valuenow="100" id="plyr-volume-261" aria-valuetext="100.0%" style="--value: 100%;">--}}
{{--                                        </div>--}}
{{--                                        <button class="plyr__controls__item plyr__control" type="button" data-plyr="captions">--}}
{{--                                            <svg class="icon--pressed" aria-hidden="true" focusable="false"><use xlink:href="#plyr-captions-on"></use></svg>--}}
{{--                                            <svg class="icon--not-pressed" aria-hidden="true" focusable="false"><use xlink:href="#plyr-captions-off"></use></svg>--}}
{{--                                            <span class="label--pressed plyr__sr-only">Disable captions</span>--}}
{{--                                            <span class="label--not-pressed plyr__sr-only">Enable captions</span>--}}
{{--                                        </button>--}}
{{--                                        <div class="plyr__controls__item plyr__menu">--}}
{{--                                            <button aria-haspopup="true" aria-controls="plyr-settings-261" aria-expanded="false" type="button" class="plyr__control" data-plyr="settings">--}}
{{--                                                <svg aria-hidden="true" focusable="false"><use xlink:href="#plyr-settings"></use></svg>--}}
{{--                                                <span class="plyr__sr-only">Settings</span>--}}
{{--                                            </button>--}}
{{--                                            <div class="plyr__menu__container" id="plyr-settings-261" hidden="">--}}
{{--                                                <div>--}}
{{--                                                    <div id="plyr-settings-261-home">--}}
{{--                                                        <div role="menu">--}}
{{--                                                            <button data-plyr="settings" type="button" class="plyr__control plyr__control--forward" role="menuitem" aria-haspopup="true" hidden="">--}}
{{--                                                                <span>Captions<span class="plyr__menu__value">Disabled</span></span>--}}
{{--                                                            </button>--}}
{{--                                                            <button data-plyr="settings" type="button" class="plyr__control plyr__control--forward" role="menuitem" aria-haspopup="true">--}}
{{--                                                                <span>Quality<span class="plyr__menu__value">Auto</span></span>--}}
{{--                                                            </button><button data-plyr="settings" type="button" class="plyr__control plyr__control--forward" role="menuitem" aria-haspopup="true">--}}
{{--                                                                <span>Speed<span class="plyr__menu__value">Normal</span></span>--}}
{{--                                                            </button></div></div><div id="plyr-settings-261-captions" hidden="">--}}
{{--                                                        <button type="button" class="plyr__control plyr__control--back">--}}
{{--                                                            <span aria-hidden="true">Captions</span>--}}
{{--                                                            <span class="plyr__sr-only">Go back to previous menu</span>--}}
{{--                                                        </button>--}}
{{--                                                        <div role="menu"></div>--}}
{{--                                                    </div>--}}
{{--                                                    <div id="plyr-settings-261-quality" hidden="">--}}
{{--                                                        <button type="button" class="plyr__control plyr__control--back">--}}
{{--                                                            <span aria-hidden="true">Quality</span>--}}
{{--                                                            <span class="plyr__sr-only">Go back to previous menu</span>--}}
{{--                                                        </button>--}}
{{--                                                        <div role="menu">--}}
{{--                                                            <button data-plyr="quality" type="button" role="menuitemradio" class="plyr__control" aria-checked="true" value="0">--}}
{{--                                                                <span>AUTO (720p)</span>--}}
{{--                                                            </button>--}}
{{--                                                            <button data-plyr="quality" type="button" role="menuitemradio" class="plyr__control" aria-checked="false" value="480">--}}
{{--                                                                <span>480p<span class="plyr__menu__value"><span class="plyr__badge">SD</span></span></span>--}}
{{--                                                            </button>--}}
{{--                                                            <button data-plyr="quality" type="button" role="menuitemradio" class="plyr__control" aria-checked="false" value="720">--}}
{{--                                                                <span>720p<span class="plyr__menu__value"><span class="plyr__badge">HD</span></span></span>--}}
{{--                                                            </button>--}}
{{--                                                        </div>--}}
{{--                                                    </div>--}}
{{--                                                    <div id="plyr-settings-261-speed" hidden="">--}}
{{--                                                        <button type="button" class="plyr__control plyr__control--back">--}}
{{--                                                            <span aria-hidden="true">Speed</span>--}}
{{--                                                            <span class="plyr__sr-only">Go back to previous menu</span>--}}
{{--                                                        </button>--}}
{{--                                                        <div role="menu">--}}
{{--                                                            <button data-plyr="speed" type="button" role="menuitemradio" class="plyr__control" aria-checked="false" value="0.5">--}}
{{--                                                                <span>0.5×</span>--}}
{{--                                                            </button>--}}
{{--                                                            <button data-plyr="speed" type="button" role="menuitemradio" class="plyr__control" aria-checked="true" value="1">--}}
{{--                                                                <span>Normal</span>--}}
{{--                                                            </button>--}}
{{--                                                            <button data-plyr="speed" type="button" role="menuitemradio" class="plyr__control" aria-checked="false" value="1.5">--}}
{{--                                                                <span>1.5×</span>--}}
{{--                                                            </button>--}}
{{--                                                            <button data-plyr="speed" type="button" role="menuitemradio" class="plyr__control" aria-checked="false" value="2">--}}
{{--                                                                <span>2×</span>--}}
{{--                                                            </button>--}}
{{--                                                        </div>--}}
{{--                                                    </div>--}}
{{--                                                </div>--}}
{{--                                            </div>--}}
{{--                                        </div>--}}
{{--                                        <button class="plyr__controls__item plyr__control" type="button" data-plyr="pip">--}}
{{--                                            <svg aria-hidden="true" focusable="false"><use xlink:href="#plyr-pip"></use></svg>--}}
{{--                                            <span class="plyr__sr-only">PIP</span>--}}
{{--                                        </button>--}}
{{--                                        <button class="plyr__controls__item plyr__control" type="button" data-plyr="fullscreen">--}}
{{--                                            <svg class="icon--pressed" aria-hidden="true" focusable="false"><use xlink:href="#plyr-exit-fullscreen"></use></svg>--}}
{{--                                            <svg class="icon--not-pressed" aria-hidden="true" focusable="false"><use xlink:href="#plyr-enter-fullscreen"></use></svg>--}}
{{--                                            <span class="label--pressed plyr__sr-only">Exit fullscreen</span><span class="label--not-pressed plyr__sr-only">Enter fullscreen</span>--}}
{{--                                        </button>--}}
{{--                                    </div>--}}
{{--                                    <div class="plyr__video-wrapper">--}}
{{--                                        <video id="preview_my_video_4462" class="main-video-player embed-responsive-item" webkit-playsinline="" playsinline="" autoplay="" preload="none" poster="https://gitiho.com/caches/cc_video_cover/cou_avatar/2022/03_16/1543404fe8a20cd52c3bcc28584de24e.png" style="" src="blob:https://gitiho.com/8a3c995d-991a-4326-88be-cfc0750599d0" data-poster="https://gitiho.com/caches/cc_video_cover/cou_avatar/2022/03_16/1543404fe8a20cd52c3bcc28584de24e.png">--}}
{{--                                            <source type="application/x-mpegURL" src="https://video.gitiho.com/Ecu2o14uHld_pfLEuvcYuQ/1714731435/gitihomedia/video_transcoder/2020/09_03/4632ace6f9dc55c9ca010101d69b6032/playlist.m3u8" data-url="" data-type="other">--}}
{{--                                            {!! $chapter[0]->lesson[0]->iframe !!}--}}

{{--                                        </video>--}}
{{--                                        <div class="plyr__poster" style="background-image: url(&quot;https://gitiho.com/caches/cc_video_cover/cou_avatar/2022/03_16/1543404fe8a20cd52c3bcc28584de24e.png&quot;);">--}}
{{--                                        </div>--}}

{{--                                    </div>--}}
{{--                                    {!! $chapter[0]->lesson[0]->iframe !!}--}}
{{--                                    <div class="plyr__captions"></div>--}}
{{--                                    <button type="button" class="plyr__control plyr__control--overlaid" data-plyr="play" aria-label="Play">--}}
{{--                                        <svg aria-hidden="true" focusable="false"><use xlink:href="#plyr-play"></use></svg>--}}
{{--                                        <span class="plyr__sr-only">Play</span>--}}
{{--                                    </button>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                        </div>--}}

{{--                        <script>--}}
{{--                            var currentVideoDuration = '161'--}}
{{--                        </script>--}}
{{--                    </div>--}}
{{--                    <div class="bg-over-load-video position-absolute d-flex justify-content-center hide" style="top:0;bottom:0;width: 100%;left:0;background: rgba(0,0,0,0.6);">--}}
{{--                        <div class="lds-facebook big my-auto"><div></div><div></div><div></div></div>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--                {!! $chapter[0]->lesson[0]->iframe !!}--}}

{{--                <div>--}}
{{--                    <p class="f-18 mt-1 text-white mt-4 my-3">Danh sách bài học</p>--}}

{{--                    <div class="mx-n4">--}}
{{--                        <ul class="list-unstyled mb-0 text-white">--}}
{{--                            @foreach($chapter as $c)--}}
{{--                                <li>--}}
{{--                                    <div class="d-flex justify-content-between px-4 py-3 content-try-item content-try-list" style="background: #333333">--}}
{{--                                        <div class="lecture-title my-auto d-flex">--}}
{{--                                            <span class="font-weight-bold my-auto line-clamp">Chương {{$loop->iteration}}: {{$c->name}}</span>--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
{{--                                </li>--}}
{{--                                @foreach($c->lesson as $ls)--}}
{{--                                    <li>--}}
{{--                                        <div class="d-flex justify-content-between pointer px-4 py-3 content-try-item" id="content_try_4310" onclick="showVideoTry(4310,this)">--}}
{{--                                            <div class="lecture-title my-auto d-flex">--}}
{{--                                                <img class="mr-3" alt="" src="/frontend/img/gitiho_v2/icon_play_page/icon-video.svg">--}}
{{--                                                <span class="font-weight-bold">Bài {{$loop->iteration}}. {{$ls->name}}</span>--}}
{{--                                            </div>--}}
{{--                                            <span class="duration font-weight-bold mr-3">{{ floor($ls->time / 60) }}:{{ str_pad($ls->time % 60, 2, '0', STR_PAD_LEFT) }} phút</span>--}}
{{--                                        </div>--}}
{{--                                    </li>--}}

{{--                                @endforeach--}}

{{--                            @endforeach--}}


{{--                        </ul>--}}
{{--                    </div>--}}
{{--                </div>--}}

{{--            </div>--}}
{{--        </div>--}}
{{--    </div>--}}

@endsection



