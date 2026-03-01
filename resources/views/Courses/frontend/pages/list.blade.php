@extends('Courses.frontend.layout.app')
@section('title')
    Đào tạo nội bộ
@endsection
@section('content')
    <div class="sidebar-filter d-block d-lg-none">
        <div class="position-fixed overlay-h" onclick="showSidebarFilter()"></div>
        <div class="filter-sidebar d-block d-lg-none position-fixed">
            <div class="h-100" style="overflow: auto;">
                <div class="d-flex flex-column">
                    <div class="filter-sidebar-bar">
                        <div class="filter-drawer--filter-panel-top d-flex justify-content-between px-3">
                            <span class="py-3 my-auto udlite-heading-md filter-panel--item my-auto color-label fw-700"><span class="filter-panel--item-count">85</span> khóa học Tin học văn phòng</span>

                            <button type="button" class="py-3 my-auto hide clear_filter udlite-btn udlite-btn-large udlite-btn-ghost udlite-heading-md udlite-link-neutral filter-button-container--filter-clear" onclick="clearFilter()">
                                <span class="fw-700">Xóa bộ lọc</span>
                            </button>
                        </div>
                    </div>
                    <div class="sort-sidebar px-3 pt-3">
                        <ul class="list-unstyled">
                            <li class="d-inline-block mb-2 mb-lg-0">
                                <button onclick="updateSort(this,'popularity')" class="btn btn-custom-category mr-2 py-1 px-2 f-14 active">Phổ biến nhất</button>
                            </li>
                            <li class="d-inline-block mb-2 mb-lg-0">
                                <button onclick="updateSort(this,'highest-rated')" class="btn btn-custom-category mr-2 py-1 px-2 f-14 ">Rating cao nhất</button>
                            </li>
                            <li class="d-inline-block mb-2 mb-lg-0">
                                <button onclick="updateSort(this,'newest')" class="btn btn-custom-category mr-2 py-1 px-2 f-14 ">Mới nhất</button>
                            </li>
                        </ul>
                    </div>
                    <div class="p-3 filter-content-sidebar">


                        <div class="panel--panel">
                            <span class="accordion-panel" data-type="checkbox" data-checked="checked" style="display: none;"></span>
                            <div class="udlite-btn d-flex py-2 justify-content-between udlite-accordion-panel-toggler pointer">
                                <p class="udlite-accordion-panel-heading mb-0 f-16 fw-700">
                                    <span class="udlite-accordion-panel-title">Danh mục</span>
                                </p>



                            </div>
                            <div class="panel--content-wrapper">
                                <div class="pt-1 pb-3 show-more--container">
                                    <div class="show-more--content" style="max-height: 145px;">
                                        <a class="nav-link  {{ 0 == $category_id ? 'active' : '' }}" href="/dao-tao-noi-bo" style="color:#212529;padding-left: 0">
                                            Tất cả
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <?php
                            $categories = \App\CRMDV\Models\Category::select('id', 'name')->where('status', 1)->where('type', 1);
                            if (\Auth::guard('admin')->user()->super_admin != 1) {
                                $categories = $categories->where('role_ids', 'LIKE', '%|'.@\App\Models\RoleAdmin::where('admin_id', \Auth::guard('admin')->user()->id)->first()->role_id.'|%');
                            }
                            $categories = $categories->where(function ($query) {
                                $query->orWhereNull('parent_id');
                                $query->orWhere('parent_id', 0);
                            })
                                ->orderBy('order_no', 'desc')->orderBy('name', 'asc')
                                ->get();
                            ?>
                            @foreach($categories as $cat)
                                    <?php
                                    $cat_childs = \App\CRMDV\Models\Category::select('id', 'name')->where('status', 1)->where('type', 1)
                                        ->where('parent_id', $cat->id);
                                    if (\Auth::guard('admin')->user()->super_admin != 1) {
                                        $cat_childs = $cat_childs->where('role_ids', 'LIKE', '%|'.@\App\Models\RoleAdmin::where('admin_id', \Auth::guard('admin')->user()->id)->first()->role_id.'|%');
                                    }

                                    $cat_childs = $cat_childs->orderBy('order_no', 'desc')->orderBy('name', 'asc')
                                        ->get();
                                    ?>
                                <div class="panel--content-wrapper">
                                    <div class="pt-1 pb-3 show-more--container">
                                        <div class="show-more--content" style="max-height: 145px;">
                                            <a class="nav-link {{ $cat->id == $category_id ? 'active' : '' }}" href="/dao-tao-noi-bo/{{ $cat->id }}" style="color:#212529;padding-left: 0">
                                                {{ $cat->name }}
                                            </a>
                                            @if(count($cat_childs) > 0)
                                                <ul class="dropdown-menu">
                                                    @foreach($cat_childs as $cat_child)
                                                            <?php
                                                            $cat3_childs = \App\CRMDV\Models\Category::select('id', 'name')->where('status', 1)->where('type', 1)
                                                                ->where('role_ids', 'LIKE', '%|'.@\App\Models\RoleAdmin::where('admin_id', \Auth::guard('admin')->user()->id)->first()->role_id.'|%')
                                                                ->where('parent_id', $cat_child->id)
                                                                ->orderBy('order_no', 'desc')->orderBy('name', 'asc')
                                                                ->get();
                                                            ?>
                                                        <li class="nav-item">
                                                            <a class="dropdown-item" href="/admin/course/view/{{ $cat_child->id }}">{{ $cat_child->name }}</a>
                                                            @if(count($cat3_childs) > 0)
                                                                <ul class="dropdown-menu">
                                                                    @foreach($cat3_childs as $cat_child)
                                                                        <li class="nav-item">
                                                                            <a class="dropdown-item" href="/admin/course/view/{{ $cat_child->id }}">{{ $cat_child->name }}</a>
                                                                        </li>
                                                                    @endforeach
                                                                </ul>
                                                            @endif
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            <button type="button" class="udlite-btn side-drawer--close-btn" onclick="showSidebarFilter()">
                <svg aria-label="Close side drawer" focusable="false" class="ml-0 udlite-icon udlite-icon-medium udlite-icon-color-neutral">
                    <use xlink:href="#icon-close"></use>
                </svg>
            </button>
        </div>
    </div>
    <div class="page-categories">
        <div class="container">

        </div>
        <div class="body-categories position-relative">
            <div class="container">


                <div class="row">
                    <div class="col-lg-3 d-none d-lg-block">
                        <div class="filter-content"></div>
                    </div>

                    <div class="col-lg-9">
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
                                                            <p class="color_text mt-n1 line-clamp-2-lines line-clamp mb-2"></p>

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