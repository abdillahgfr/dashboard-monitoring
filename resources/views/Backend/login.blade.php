@extends('Backend.Layout.app') <!-- Extends the layout -->

@section('content')

<!-- start: page -->
<section class="body-sign">
    <div class="center-sign">
        <a href="/" class="logo float-start">
            <img src="img/logo.png" height="70" alt="Porto Admin" />
        </a>

        <div class="panel card-sign">
            <div class="card-title-sign mt-3 text-end">
                <h2 class="title text-uppercase font-weight-bold m-0"><i class="bx bx-user-circle me-1 text-6 position-relative top-5"></i> Sign In</h2>
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
                        <div class="clearfix">
                            <label class="float-start">Password</label>
                            <a href="pages-recover-password.html" class="float-end">Lost Password?</a>
                        </div>
                        <div class="input-group">
                            <input name="pwd" type="password" class="form-control form-control-lg" />
                            <span class="input-group-text">
                                <i class="bx bx-lock text-4"></i>
                            </span>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-8">
                            <div class="checkbox-custom checkbox-default">
                                <input id="RememberMe" name="rememberme" type="checkbox"/>
                                <label for="RememberMe">Remember Me</label>
                            </div>
                        </div>
                        <div class="col-sm-4 text-end">
                            <button type="submit" class="btn btn-primary mt-2">Sign In</button>
                        </div>
                    </div>

                    <span class="mt-3 mb-3 line-thru text-center text-uppercase">
                        <span>or</span>
                    </span>

                    <div class="mb-1 text-center">
                        <a class="btn btn-facebook mb-3 ms-1 me-1" href="#">Connect with <i class="fab fa-facebook-f"></i></a>
                    </div>

                    <p class="text-center">Don't have an account yet? <a href="pages-signup.html">Sign Up!</a></p>

                </form>
            </div>
        </div>

        <p class="text-center text-muted mt-3 mb-3">&copy; Copyright 2023. All Rights Reserved.</p>
    </div>
</section>
<!-- end: page -->


@endsection