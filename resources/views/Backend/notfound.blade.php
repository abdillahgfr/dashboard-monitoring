@extends('Backend.Layout.login')

@section('content')


<!-- start: page -->
<section class="body-error error-outside">
    <div class="center-error">

        <div class="row">
            <div class="col-md-8">
                <div class="main-error mb-3">
                    <h2 class="error-code text-dark text-center font-weight-semibold m-0">404 <i class="fas fa-file"></i></h2>
                    <p class="error-explanation text-center">We're sorry, page is not found.</p>
                </div>
            </div>
            <div class="col-md-4">
                <h4 class="text">Here are some useful links</h4>
                <ul class="nav nav-list flex-column primary">
                    <li class="nav-item">
                        <a class="nav-link" href="{{route('index')}}"><i class="fas fa-caret-right text-dark"></i> Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{route('login')}}"><i class="fas fa-caret-right text-dark"></i> Login</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</section>
<!-- end: page -->

@endsection