@extends('layouts.master3')
@section('css')
    <!-- Sidemenu-responsive-tabs css -->
    <link href="{{URL::asset('assets/plugins/sidemenu-responsive-tabs/css/sidemenu-responsive-tabs.css')}}"
        rel="stylesheet">
    <!-- RTL CSS -->
@endsection
@section('content')


    <div class="container-fluid">
        <div class="row no-gutter" dir="RTL">



            <!-- The image half -->
            <div class="col-md-6 col-lg-6 col-xl-7 d-none d-md-flex bg-primary-transparent order-2 order-md-1">
                <div class="row wd-100p mx-auto text-center">
                    <div class="col-md-12 col-lg-12 col-xl-12 my-auto mx-auto wd-100p">
                        <img src="{{ asset('images/general_pic/register.png') }}" class="sign-favicon ht-40" alt="logo"
                            style="width: 100% ; height:100%">
                    </div>
                </div>
            </div>
            <!-- The content half -->
            <div class="col-md-6 col-lg-6 col-xl-5 bg-white order-1 order-md-2">
                <div class="login d-flex align-items-center py-2">
                    <!-- Demo content -->
                    <div class="container p-0">
                        <div class="row">
                            <div class="col-md-10 col-lg-10 col-xl-9 mx-auto">
                                <div class="card-sigin text-right">
                                    <div class="mb-5 d-flex justify-content-between">
                                        <h1 class="main-logo1 ml-1 mr-0 my-auto tx-28">Va<span>le</span>x</h1>
                                        <a href="{{ url('/' . $page = 'index') }}">
                                            <img src="{{URL::asset('assets/img/brand/favicon.png')}}"
                                                class="sign-favicon ht-40" alt="logo">
                                        </a>
                                    </div>
                                    <div class="main-signup-header">
                                        @if(session('success'))
                                            <div class="alert alert-success">
                                                {{ session('success') }}
                                            </div>
                                        @endif
                                        <h2 class="text-primary">ابدأ الآن</h2>
                                        <h5 class="font-weight-normal mb-4">التسجيل مجاني ويستغرق دقيقة واحدة فقط.</h5>
                                        <form action="{{ route('register') }}" method="POST" id="registerForm"
                                            enctype="multipart/form-data">
                                            @csrf
                                            <div class="form-group">
                                                <label>الاسم</label>
                                                <input class="form-control text-right" name="user_name"
                                                    placeholder="أدخل اسمك الأول واسم العائلة" type="text">
                                                <span id="user_name_error" class="text-danger"></span>
                                            </div>
                                            <div class="form-group">
                                                <label>الإيميل</label>
                                                <input class="form-control text-right" name="user_email"
                                                    placeholder="أدخل بريدك الإلكتروني" type="email">
                                                <span id="user_email_error" class="text-danger"></span>
                                            </div>
                                            <div class="form-group">
                                                <label>الموبايل</label>
                                                <input class="form-control text-right" name="user_phone"
                                                    placeholder="أدخل الموبايل" type="text">
                                                <span id="user_phone_error" class="text-danger"></span>
                                            </div>
                                            <div class="form-group">
                                                <label>تاريخ الميلاد</label>
                                                <input class="form-control text-right" name="user_birthdate"
                                                    placeholder="تاريخ الميلاد" type="date">
                                                <span id="user_birthdate_error" class="text-danger"></span>
                                            </div>
                                            <div class="form-group">
                                                <label>الجنس</label>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="user_gender"
                                                        id="genderMale" value="male">
                                                    <label class="form-check-label" for="genderMale">ذكر</label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="user_gender"
                                                        id="genderFemale" value="female">
                                                    <label class="form-check-label" for="genderFemale">أنثى</label>
                                                </div>
                                                <span id="user_gender_error" class="text-danger"></span>
                                            </div>
                                            <div class="form-group">
                                                <label>كلمة المرور</label>
                                                <input class="form-control text-right" name="password"
                                                    placeholder="أدخل كلمة المرور" type="password">
                                                <span id="password_error" class="text-danger"></span>
                                            </div>
                                            <div class="form-group">
                                                <label>تأكيد كلمة المرور</label>
                                                <input class="form-control text-right" name="password_confirmation"
                                                    placeholder="تأكيد كلمة المرور" type="password">
                                                <span id="password_confirmation_error" class="text-danger"></span>
                                            </div>
                                            <div class="form-group">
                                                <label>صورة الملف الشخصي</label>
                                                <input class="form-control-file text-right" name="profile_picture"
                                                    type="file">
                                                <span id="profile_picture_error" class="text-danger"></span>
                                            </div>
                                            <button class="btn btn-primary btn-block" type="submit">إنشاء حساب</button>
                                        </form>
                                        <div class="main-signup-footer mt-5">
                                            <p>لديك حساب بالفعل؟ <a href="{{ url('/' . $page = 'auth.login') }}">تسجيل
                                                    الدخول</a></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div><!-- End -->
                </div>
            </div>
            <!-- End -->
        </div>
    </div>
@endsection
@section('js')

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const form = document.getElementById("registerForm");

            if (!form) return; // Stop execution if form does not exist

            form.addEventListener("submit", function (event) {
                let isValid = true;

                // Name validation (allow Arabic, English letters, numbers, spaces)
                const nameRegex = /^[a-zA-Z0-9\s\u0600-\u06FF]+$/;
                const userName = document.getElementsByName("user_name")[0]?.value.trim();
                if (!userName || !nameRegex.test(userName)) {
                    isValid = false;
                    document.getElementById("user_name_error")?.innerText =
                        "الاسم يجب أن يحتوي على حروف عربية أو إنجليزية أو أرقام أو مسافات.";
                } else {
                    document.getElementById("user_name_error")?.innerText = "";
                }

                // Email validation
                const email = document.getElementsByName("user_email")[0]?.value.trim();
                const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
                if (!email || !emailRegex.test(email)) {
                    isValid = false;
                    document.getElementById("user_email_error")?.innerText =
                        "يرجى إدخال بريد إلكتروني صحيح.";
                } else {
                    document.getElementById("user_email_error")?.innerText = "";
                }

                // Mobile validation (start with 01 and length of 11)
                const mobile = document.getElementsByName("user_phone")[0]?.value.trim();
                const mobileRegex = /^01\d{9}$/;
                if (!mobile || !mobileRegex.test(mobile)) {
                    isValid = false;
                    document.getElementById("user_phone_error")?.innerText =
                        "الموبايل يجب أن يبدأ بـ 01 ويكون طوله 11 رقم.";
                } else {
                    document.getElementById("user_phone_error")?.innerText = "";
                }

                // Birthdate validation (required)
                const birthdate = document.getElementsByName("user_birthdate")[0]?.value;
                if (!birthdate) {
                    isValid = false;
                    document.getElementById("user_birthdate_error")?.innerText =
                        "يرجى إدخال تاريخ الميلاد.";
                } else {
                    document.getElementById("user_birthdate_error")?.innerText = "";
                }

                // Gender validation (required)
                const gender = document.querySelector('input[name="user_gender"]:checked');
                if (!gender) {
                    isValid = false;
                    document.getElementById("user_gender_error")?.innerText =
                        "يرجى اختيار الجنس.";
                } else {
                    document.getElementById("user_gender_error")?.innerText = "";
                }

                // Password validation (8 characters minimum, only letters, numbers, spaces, _ and -)
                const password = document.getElementsByName("password")[0]?.value;
                const passwordRegex = /^[a-zA-Z\d_\-\s]{8,}$/;

                if (!password || !passwordRegex.test(password)) {
                    isValid = false;
                    document.getElementById("password_error")?.innerText =
                        "كلمة المرور يجب أن تحتوي على 8 أحرف على الأقل، ويمكن أن تحتوي على أرقام أو حروف أو مسافات أو (_) أو (-).";
                } else {
                    document.getElementById("password_error")?.innerText = "";
                }

                // Password confirmation validation (must match password)
                const passwordConfirmation =
                    document.getElementsByName("password_confirmation")[0]?.value;
                if (password !== passwordConfirmation) {
                    isValid = false;
                    document.getElementById("password_confirmation_error")?.innerText =
                        "تأكيد كلمة المرور لا يتطابق مع كلمة المرور.";
                } else {
                    document.getElementById("password_confirmation_error")?.innerText = "";
                }

                // Prevent form submission if validation fails
                if (!isValid) {
                    event.preventDefault();
                }
            });
        });

    </script>

@endsection