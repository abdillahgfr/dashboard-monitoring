@extends('Backend.Layout.login') <!-- Extends the layout -->

@section('content')
    <!-- start: page -->
    <section class="body-sign">
        <div class="center-sign">
            <a href="/notifikasi" class="logo float-start">
                <img src="img/Bpad-persediaan.png" height="75" alt="Persediaan Admin" />
            </a>

            <div class="panel card-sign">
                <div class="card-title-sign mt-3 text-end">
                    <h2 class="title text-uppercase font-weight-bold m-0"><i
                            class="bx bx-user-circle me-1 text-6 position-relative top-5"></i> Sign In</h2>
                </div>
                <div class="card-body">
                    <form action="index.html" method="post">
                        <div class="form-group mb-3">
                            <label>Username</label>
                            <div class="input-group">
                                <input name="username" type="text" class="form-control form-control-lg" />
                                <span class="input-group-text">
                                    <i class="bx bx-user text-4"></i>
                                </span>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <div class="input-group">
                                <input name="pwd" type="password" class="form-control form-control-lg" />
                                <span class="input-group-text">
                                    <i class="bx bx-lock text-4"></i>
                                </span>
                            </div>
                        </div>

                        <hr>

                        <div class="mb-1 text-center">
                            <a class="btn btn-facebook mb-3 ms-1 me-1" href="#">Sign In</a>
                        </div>

                    </form>
                </div>
            </div>

            <p class="text-center text-muted mt-3 mb-3">&copy; Copyright 2025. All Rights Reserved.</p>
        </div>
    </section>
    <!-- end: page -->
@endsection
