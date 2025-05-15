<template>
  <section role="main" class="content-body">
    <header class="page-header">
      <h2>Dashboard</h2>
    </header>

    <div class="inner-wrapper">
      <div class="alert alert-primary alert-dismissible fade show" role="alert">
        <strong>Welcome!</strong> Hi, {{ user.nm_emp }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>

      <div class="row mt-4">
        <div class="col-xl-12">
          <section class="card">
            <div class="col-lg-12">
              <section class="card">
                <div class="card-body">
                  <div class="widget-toggle-expand mb-3">
                    <div class="widget-header">
                      <h5 class="mb-2 font-weight-semibold text-dark">Profile</h5>
                      <div class="widget-toggle">+</div>
                    </div>
                    <div class="widget-content-expanded">
                      <ul class="simple-todo-list mt-3">
                        <li class="completed">Nama: {{ user.nm_emp }}</li>
                        <li class="completed">Email: {{ user.email_emp }}</li>
                        <li class="completed">Telp: {{ user.tlp_emp }}</li>
                        <li class="completed">ID: {{ user.idunit }}</li>
                        <li class="completed">Unit: {{ user.nmunit }}</li>
                      </ul>
                    </div>
                  </div>

                  <hr class="dotted short" />
                </div>
              </section>
            </div>
          </section>
        </div>
      </div>
    </div>
  </section>
</template>

<script>
export default {
  props: {
    user: Object
  }
};
</script>
