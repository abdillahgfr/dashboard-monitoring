@extends('Backend.Layout.app') <!-- Extends the layout -->

@section('content')
    <!-- Defines the 'content' section -->
    <section role="main" class="content-body">
        <header class="page-header">
            <h2>Dashboard</h2>
        </header>

        <div class="inner-wrapper">

            <div class="alert alert-primary alert-dismissible fade show" role="alert">
                <strong>Welcome !</strong> Hi, {{ $user->nm_emp }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true" aria-label="Close"></button>
            </div>
            <div class="row mt-4">
					<!-- start: page -->
					<div class="row">
						<div class="col-lg-4 col-xl-3 mb-4 mb-xl-0">

							<section class="card">
								<div class="card-body">
									<div class="thumb-info mb-3">
										<img src="{{ url('img/Sample_User_Icon.png')}}" class="rounded img-fluid" alt="{{ $user->nm_emp }}">
										<div class="thumb-info-title">
											<span class="thumb-info-inner">{{ $user->nm_emp }}</span>
											<span class="thumb-info-type">{{ $user->status_emp }}</span>
										</div>
									</div>
								</div>
							</section>
						</div>
						<div class="col-lg-8 col-xl-9">

							<div class="tabs">
								<ul class="nav nav-tabs tabs-primary">
									<li class="nav-item active">
										<button class="nav-link" data-bs-target="#overview" data-bs-toggle="tab">Overview</button>
									</li>
								</ul>
								<div class="tab-content">
									<div id="overview" class="tab-pane active">
										<form class="p-3">
											<h4 class="mb-3 font-weight-semibold text-dark">Personal Information</h4>
											<div class="row row mb-4">
												<div class="form-group col">
													<label for="inputAddress">Nama</label>
													<input type="text" class="form-control" value="{{ $user->nm_emp }}" readonly>
												</div>
											</div>
											<div class="row mb-4">
												<div class="form-group col">
													<label for="inputAddress2">Status</label>
													<input type="text" class="form-control" value="{{ $user->idgroup }}" readonly>
												</div>
											</div>
                                            <div class="row mb-4">
												<div class="form-group col">
													<label for="inputAddress2">Unit</label>
													<input type="text" class="form-control" value="{{ $user->skpd }}" readonly>
												</div>
											</div>
											<div class="row mb-4">
												<div class="form-group col">
													<label for="inputAddress2">Akses</label>
													<input type="text" class="form-control" value="{{ $user->fitur_akses }}" readonly>
												</div>
											</div>
										</form>
									</div>
								</div>
							</div>
						</div>
					</div>
					<!-- end: page -->
            </div>
        </div>
    </section>
@endsection
