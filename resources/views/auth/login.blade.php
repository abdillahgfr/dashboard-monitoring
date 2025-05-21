@extends('Backend.Layout.login') <!-- Extends the layout -->

@section('content')
<!-- start: page -->
<section class="body-error error-outside">
    <div class="center-error">
        <div class="row">
            <div class="col-md-6">
                <section class="body-sign">
                    <div class="center-sign">
                        <div class="panel card-sign">
                            <h2 class="fw-bold mb-3">Selamat Datang !</h2>
                            <p class="text-muted">
                                Di aplikasi Persediaan Pusdatin BPAD DKI Jakarta.<br>
                                Silakan login untuk melanjutkan ke sistem.
                            </p>
                        </div>
                    </div>
                </section>
            </div>
            <div class="col-md-6">
                <section class="body-sign">
                    <div class="center-sign">
                        <a href="{{ route('home') }}" class="logo float-start">
                            <img src="{{ asset('img/header-persediaan.png') }}" width="290" height="80fgICEDlCTI" alt="Persediaan Admin" />
                        </a>
                        <div class="panel card-sign">
                            <div class="card-title-sign mt-3 text-end">
                                <h2 class="title text-uppercase font-weight-bold m-0">
                                    <i class="bx bx-user-circle me-1 text-6 position-relative top-5"></i> Sign In
                                </h2>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('login.submit') }}" method="POST">
                                    @csrf
                                    <div class="form-group mb-3">
                                        <label>Username</label>
                                        <div class="input-group">
                                            <input name="username" type="text" class="form-control form-control-md" required />
                                            <span class="input-group-text">
                                                <i class="bx bx-user text-4"></i>
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group mb-3">
                                        <label>Password</label>
                                        <div class="input-group">
                                            <input name="password" type="password" class="form-control form-control-md" id="password-input" required />
                                            <span class="input-group-text" style="cursor:pointer;" onclick="togglePassword()">
                                                <i class="bx bx-hide text-4" id="eye-closed-icon"></i>
                                                <i class="bx bx-show text-4 d-none" id="eye-open-icon"></i>
                                            </span>
                                        </div>
                                    </div>

                                    <script>
                                        function togglePassword() {
                                            const input = document.getElementById('password-input');
                                            const eyeOpen = document.getElementById('eye-open-icon');
                                            const eyeClosed = document.getElementById('eye-closed-icon');

                                            if (input.type === 'password') {
                                                input.type = 'text';
                                                eyeClosed.classList.add('d-none');
                                                eyeOpen.classList.remove('d-none');
                                            } else {
                                                input.type = 'password';
                                                eyeClosed.classList.remove('d-none');
                                                eyeOpen.classList.add('d-none');
                                            }
                                        }
                                    </script>


                                    <div class="form-group mb-3">
                                        <label>Tahun</label>
                                        <div class="input-group">
                                            <select name="tahun" class="form-control form-control-md" required>
                                                @php
                                                    $currentYear = date('Y');
                                                @endphp
                                                @for ($year = $currentYear; $year >= $currentYear - 5; $year--)
                                                    <option value="{{ $year }}">{{ $year }}</option>
                                                @endfor
                                            </select>
                                            <span class="input-group-text">
                                                <i class="bx bx-calendar text-4"></i>
                                            </span>
                                        </div>
                                    </div>

                                    <hr>

                                    <div class="mb-1 text-center">
                                        <button type="submit" class="btn btn-primary mb-3 ms-1 me-1">Sign In</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <p class="text-center text-muted mt-3 mb-3">&copy; Persediaan 2025. All Rights Reserved.</p>
                    </div>
                </section>
            </div>
        </div>
    </div>
</section>
<!-- end: page -->
@endsection
@if ($errors->has('login_error'))
<script>
    document.addEventListener('DOMContentLoaded', function () {
        new PNotify({
            title: 'Login Failed',
            text: '{{ $errors->first('login_error') }}',
            type: 'error',
            nonblock: {
                nonblock: true,
                nonblock_opacity: .2
            }
        });
    });
</script>
@endif


