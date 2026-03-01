@extends('Courses.frontend.layout.app')
@section('title')
    Bài cần thi
@endsection
@section('content')
<div class="page-categories">
    <div class="container">

    </div>
    <div class="body-categories position-relative">
        <div class="container">


            <div class="row">
{{--                <div class="col-lg-3 d-none d-lg-block">--}}
{{--                    <div class="filter-content"></div>--}}
{{--                </div>--}}

                <div class="col-lg-12">
                    <div class="category-list-courses">
                        <div class="row category-list-course">
                            @foreach($list_course as $item)
                                <div class="col-md-6 col-xl-4 my-3">
                                    <a href="{{route("dao-tao-noi-bo.detail", $item->id)}}" style="margin: 15px auto;">
                                        <div class="list-item" style="min-width: 250px;margin: 0;height: 100%">
                                            <div class="courses">
                                                <img class="lazyload" src="{{$item->image? \URL::asset('filemanager/userfiles/'.$item->image):\URL::asset('backend/image_dao_tao_noi_bo/course-default.jpg') }}" data-src="{{$item->image? \URL::asset('filemanager/userfiles/'.$item->image):\URL::asset('backend/image_dao_tao_noi_bo/course-default.jpg') }}" alt="Ebook Tuyệt đỉnh Excel - Khai phá 10 kỹ thuật ứng dụng Excel mà đại học không dạy bạn">

                                                <div class="cou-info" style="border-bottom: none">
                                                    <h3 class="cou-title f-16 fw-700" style="height: auto">{{$item->name}}</h3>

                                                    <div>
                                                        <p class="color_text mt-n1 line-clamp-2-lines line-clamp mb-2">Hạn thực hiện: @include('Courses.frontend.list.td.han_thuc_hien') </p>

                                                        <div class="my-1">
                                                            <p class="f-12 color_text2 mb-0">
                                                                {{$item->author_name}}
                                                            </p>
                                                        </div>


                                                    </div>



                                                </div>

                                            </div>
                                        </div>
                                    </a>
                                </div>
                            @endforeach


                        </div>
                        <div>
                            {{ $list_course->appends(request()->all())->links() }}
                        </div>
                    </div>


                </div>
            </div>
        </div>
        <div class="loading-category hide position-absolute w-100 h-100" style="z-index: 8;background: rgba(255,255,255,0.6);top: 0">
            <div class="container">
                <div class="row">
                    <div class="col-lg-3">

                    </div>

                    <div class="col-lg-9">
                        <div class="text-center mt-5">
                            <div class="lds-ring"><div></div><div></div><div></div><div></div></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection