<script>
import { Head, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';

export default {
  props: {
    canLogin: {
      type: Boolean,
    },
    canRegister: {
      type: Boolean,
    },
    laravelVersion: {
      type: String,
      required: true,
    },
    phpVersion: {
      type: String,
      required: true,
    },
  },
  data() {
    return {
      appName: this.$page.props.config.appName || 'Laravel',
      form: null,
    };
  },
  methods: {
    handleImageError() {
      document.getElementById('screenshot-container')?.classList.add('!hidden');
      document.getElementById('docs-card')?.classList.add('!row-span-1');
      document.getElementById('docs-card-content')?.classList.add('!flex-row');
      document.getElementById('background')?.classList.add('!hidden');
    },
    onSubmit() {
      this.form.post(route('login'), {
        onFinish: () => this.form.reset('password'),
      });
    }
  },
  mounted() {
    this.form = useForm({
      email: '',
      password: '',
      remember: false,
    });
  },
  components: {
    Head,
    InputError,
    GuestLayout,
  },
};
</script>

<template>
  <Head title="ログイン" />
  <GuestLayout>
    <div class="bg-gray-50 text-black/50 dark:bg-black dark:text-white/50">
    <div class="login-box">
      <div class="login-logo mb-3" style="letter-spacing: 0.06rem;opacity:0.9;">
        <b>{{ appName }}</b>
      </div>
      <!-- /.login-logo -->
      <div class="card" v-if="form">
        <div class="card-body login-card-body p-4">
          <p class="login-box-msg text-xs">以下からログインしてください</p>
          <form @submit.prevent="onSubmit">
            <div class="input-group">
              <input type="email" class="form-control" placeholder="メールアドレス" v-model="form.email" autofocus />
              <div class="input-group-append">
                <div class="input-group-text">
                  <span class="fas fa-envelope"></span>
                </div>
              </div>
            </div>
            <div class="mb-3">
              <InputError :message="form.errors.email" />
            </div>
            <div class="input-group">
              <input type="password" class="form-control" placeholder="パスワード" v-model="form.password">
              <div class="input-group-append">
                <div class="input-group-text">
                  <span class="fas fa-lock"></span>
                </div>
              </div>
            </div>
            <div class="mb-3">
              <InputError :message="form.errors.password" />
            </div>
            <div class="row d-flex align-items-center pt-3">
              <div class="col-8">
                <div class="icheck-primary">
                  <input type="checkbox" id="remember" v-model="form.remember">
                  <label for="remember" class="text-xs icheck-sm">
                    次回から省略
                  </label>
                </div>
              </div>
              <!-- /.col -->
              <div class="col-4">
                <button type="submit" class="btn btn-primary btn-block btn-sm">ログイン</button>
              </div>
              <!-- /.col -->
            </div>
          </form>
          <!--
          <p class="mb-1 pt-1">
            <a href="forgot-password.html" class="text-xs">パスワードを忘れましたか？</a>
          </p>
          -->
        </div>
        <!-- /.login-card-body -->
      </div>
    </div>
    <!-- /.login-box -->
  </div>
  </GuestLayout>
</template>
