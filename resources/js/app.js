import { createApp } from 'vue'
import Dashboard from './components/Dashboard.vue'
import LoginForm from './components/LoginForm.vue'

const app = createApp({})
app.component('login-form', LoginForm)
app.component('dashboard', Dashboard)
app.mount('#app')
