@extends('admin.layouts.master')

@section('page-title')
    قائمة المستخدمون
@stop



@section('css')
@stop

@section('content')
    @if (\Session::has('success'))
        <div class="alert alert-success">
            <ul>
                <li>{!! \Session::get('success') !!}</li>
            </ul>
        </div>
    @endif

    @if (\Session::has('error'))
        <div class="alert alert-danger">
            <ul>
                <li>{!! \Session::get('error') !!}</li>
            </ul>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif


    <!-- Start::app-content -->
    <div class="main-content app-content">
        <div class="container-fluid">

            {{-- <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">كافة المستخدمين</h5>

                </div>


            </div>
            <!-- Page Header Close --> --}}



            <!-- Start::row-1 -->
           <div class="col-xl-12 mt-3">
                        <div class="card custom-card">
                            <div class="card-header">
                                <div class="card-title">
                                  بيانات الطالب :  {{ $user->name }}
                                </div>
                            </div>
                            <div class="card-body">
                                <ul class="nav nav-pills justify-content-start nav-style-3 mb-3" role="tablist">
                                    <li class="nav-item">
                                        <a class="nav-link active" data-bs-toggle="tab" role="tab" aria-current="page"
                                        href="#home-right" aria-selected="true">البيانات الشخصية</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" data-bs-toggle="tab" role="tab" aria-current="page"
                                        href="#about-right" aria-selected="true">About</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" data-bs-toggle="tab" role="tab" aria-current="page"
                                        href="#services-right" aria-selected="true">Services</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" data-bs-toggle="tab" role="tab" aria-current="page"
                                        href="#contacts-right" aria-selected="true">Contacts</a>
                                    </li>
                                </ul>
                                <div class="tab-content">
                                    <div class="tab-pane text-muted" id="home-right" role="tabpanel">
                                        How travel coupons make you a better lover. Why cultural solutions are the new
                                        black. Why mom was right about travel insurances. How family trip ideas can help
                                        you predict the future. <b>How carnival cruises make you a better lover</b>. Why
                                        you'll never succeed at daily deals. 11 ways cheapest flights can find you the
                                        love of your life. The complete beginner's guide to mission trips. If you read
                                        one article about cultural notes read this one. Why you shouldn't eat vacation
                                        package in bed.
                                    </div>
                                    <div class="tab-pane show active text-muted" id="about-right" role="tabpanel">
                                        How hotel deals can help you live a better life. <b>How celebrity cruises</b>
                                        aren't as bad as you think. How cultural solutions can help you predict the
                                        future. How to cheat at dog friendly hotels and get away with it. 17 problems
                                        with summer activities. How to cheat at travel agents and get away with it. How
                                        not knowing family trip ideas makes you a rookie. What everyone is saying about
                                        daily deals. How twitter can teach you about carnival cruises. How to start
                                        using cultural solutions.
                                    </div>
                                    <div class="tab-pane text-muted" id="services-right" role="tabpanel">
                                        Unbelievable healthy snack success stories. 12 facts about safe food handling
                                        tips that will impress your friends. Restaurant weeks by the numbers. Will
                                        mexican food ever rule the world? The 10 best thai restaurant youtube videos.
                                        How restaurant weeks can make you sick. The complete beginner's guide to cooking
                                        healthy food. Unbelievable food stamp success stories. How whole foods markets
                                        are making the world a better place. 16 things that won't happen in dish
                                        reviews.
                                    </div>
                                    <div class="tab-pane text-muted" id="contacts-right" role="tabpanel">
                                        Why delicious magazines are killing you. Why our world would end if restaurants
                                        disappeared. Why restaurants are on crack about restaurants. How restaurants are
                                        making the world a better place. 8 great articles about minute meals. Why our
                                        world would end if healthy snacks disappeared. Why the world would end without
                                        mexican food. The evolution of chef uniforms. How not knowing food processors
                                        makes you a rookie. Why whole foods markets beat peanut butter on pancakes.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
            <!--End::row-1 -->


        </div>
    </div>
    <!-- End::app-content -->



@stop

