<template>
  <section class="body-sign">
    <div class="center-sign">
      <a :href="routeHome" class="logo float-start">
        <img src="/img/Bpad-persediaan.png" height="75" alt="Persediaan Admin" />
      </a>

      <div class="panel card-sign">
        <div class="card-title-sign mt-3 text-end">
          <h2 class="title text-uppercase font-weight-bold m-0">
            <i class="bx bx-user-circle me-1 text-6 position-relative top-5"></i> Sign In
          </h2>
        </div>

        <div class="card-body">
          <form @submit.prevent="submitForm">
            <div class="form-group mb-3">
              <label>Username</label>
              <div class="input-group">
                <input v-model="form.username" type="text" class="form-control form-control-lg" required />
                <span class="input-group-text">
                  <i class="bx bx-user text-4"></i>
                </span>
              </div>
            </div>

            <div class="form-group mb-3">
              <label>Password</label>
              <div class="input-group">
                <input v-model="form.password" type="password" class="form-control form-control-lg" required />
                <span class="input-group-text">
                  <i class="bx bx-lock text-4"></i>
                </span>
              </div>
            </div>

            <hr />

            <div class="mb-1 text-center">
              <button type="submit" class="btn btn-primary mb-3 ms-1 me-1">Sign In</button>
            </div>
          </form>
        </div>
      </div>

      <p class="text-center text-muted mt-3 mb-3">&copy; Persediaan 2025. All Rights Reserved.</p>
    </div>
  </section>
</template>

<script>
import axios from 'axios'
export default {
  props: {
    routeLogin: String,
    routeHome: String,
    errorMessage: String
  },
  data() {
    return {
      form: {
        username: '',
        password: ''
      }
    };
  },
  mounted() {
    if (this.errorMessage) {
      new PNotify({
        title: 'Login Failed',
        text: this.errorMessage,
        type: 'error',
        nonblock: {
          nonblock: true,
          nonblock_opacity: 0.2
        }
      });
    }
  },
  methods: {
    async submitForm() {
      try {
        await axios.post(this.routeLogin, this.form);
        window.location.href = this.routeHome;
      } catch (error) {
        new PNotify({
          title: 'Login Failed',
          text: error.response?.data?.message || 'Login gagal.',
          type: 'error',
          nonblock: {
            nonblock: true,
            nonblock_opacity: 0.2
          }
        });
      }
    }
  }
};
</script>
