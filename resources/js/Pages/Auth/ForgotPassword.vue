<script>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import {Head, useForm} from '@inertiajs/vue3';

export default {
    props: {
        status: String,
    },
    data() {
        return {
            form: null,
        }
    },
    methods: {
        onSubmit() {
            this.form.post(route('password.email'));
            this.form.email = '';
        }
    },
    mounted() {
        this.form = useForm({
            email: '',
        });
    },
    components: {
        Head,
        InputError,
        GuestLayout,
    }
}

</script>

<template>
    <GuestLayout>
        <Head title="パスワード再発行" />

        <!-- begin app -->
        <div class="app">
            <!-- begin app-wrap -->
            <div class="app-wrap">
                <!-- begin pre-loader -->
                <div class="app-content">
                    <div class="bg-white">
                        <div class="container-fluid p-0">
                            <div class="row no-gutters">
                                <div class="col-sm-6 col-lg-3 col-xxl-3  align-self-center order-2 order-sm-1">
                                    <div class="d-flex align-items-center vh-100">
                                        <div class="register p-5">
                                            <h1 class="mb-3 text-xl text-center font-weight-bold">パスワードを再発行</h1>
                                            <p class="text-sm" style="line-height:1.6rem;opacity:.6;">再発行URLを登録メールアドレスへ送信します。メールアドレスを入力してリセットメールを送信してください.</p>
                                            <div class="alert alert-success  text-sm" role="alert" v-if="status">
                                                {{ status }}
                                            </div>
                                            <form class="mt-10 mt-sm-5" @submit.prevent="onSubmit" v-if="form">
                                                <div class="row">
                                                    <div class="col-12 col-sm-12">
                                                        <div class="form-group">
                                                            <input
                                                                id="email"
                                                                type="email"
                                                                class="form-control"
                                                                :class="{ 'is-invalid': form.errors.email }"
                                                                name="email"
                                                                v-model="form.email"
                                                                placeholder="メールアドレスを入力してください"
                                                                required
                                                            >
                                                            <InputError class="mt-2" :message="form.errors.email" />
                                                        </div><!-- form-group -->
                                                    </div>
                                                    <div class="col-12 col-sm-12">
                                                        <button type="submit" class="btn btn-info btn-sm btn-block" style="padding: 12px">
                                                            リセットメールを送信
                                                        </button>
                                                    </div>
                                                </div>
                                            </form>
                                            <div class="mt-5 text-right">
                                                <a :href="route('login')" class="btn btn-link text-xs">HOMEに戻る</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-xxl-9 col-lg-9 bg-gray o-hidden order-1 order-sm-2 d-none d-md-block">
                                    <div style="background-color: #ECEEF3;">
                                        <div class="col-7 mx-auto">
                                            <img class="img-fluid vh-100" src="/images/undraw_Email_campaign_re_m6k5.svg" alt="">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- end app-wrap -->
        </div>
        <!-- end app -->
    </GuestLayout>
</template>
