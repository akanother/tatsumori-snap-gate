<script>
import {Head, useForm} from "@inertiajs/vue3";
import InputError from "@/Components/InputError.vue";
import GuestLayout from "@/Layouts/GuestLayout.vue";

export default {
    data() {
        return {
            appName: this.$page.props.config.appName || '',
            isChecked: false,
            form: null,
            autoLoginUsers: this.$page.props.autoLoginUsers,
        }
    },
    methods: {
        onClick() {
            this.isChecked = !this.isChecked;
        },
        onSubmit() {
            this.form.post(route('login'), {
                onFinish: () => this.form.reset('password'),
            });
        },
        // Only for TEST
        autoLogin(user) {
            this.form.email = user.email || '';
            this.form.password = 'xxxxxxxx';
            this.onSubmit();
        },
    },
    computed: {
        inputType() {
            return this.isChecked ? "text" : "password";
        },
        iconType() {
            return this.isChecked ? "fa fa-eye" : "fa fa-eye-slash";
        },
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
}
</script>

<template>
    <GuestLayout>
        <div id="loginForm" class="app-content">
            <Head title="ログイン" />
            <div class="bg-white">
                <div class="container-fluid p-0">
                    <div class="row no-gutters vh-100">
                        <div class="col-sm-6 col-lg-4 col-xxl-4 align-self-center order-2 order-sm-1">
                            <div class="d-flex align-items-center justify-center">
                                <div class="login p-5" v-if="form">
                                    <h1 class="logoFont text-center text-xl mb-4 mt-5 font-weight-bold" style="letter-spacing: 0.1rem;opacity:.9;">{{ appName }}</h1>
                                    <div class="mt-5 mb-5 border border-info rounded text-xs p-3">
                                        <div role="alert">
                                            **Order One（オーダーワン）**は、受注業務の効率化と情報の一元管理を目的として開発された、社内専用の受注管理システムです。
                                            本システムでは、受注登録・検索・進捗の可視化・関連資料の管理など、日々の業務をサポートする多彩な機能を提供しています。
                                            また、部門を超えた情報共有を可能にし、確認漏れや連携ミスの防止にも貢献します。
                                            以下のログインフォームよりアクセスし、ご自身の権限に応じた機能をご利用ください。
                                        </div>
                                    </div>

                                    <form id="form" @submit.prevent="onSubmit">
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="form-group">
                                                    <input
                                                        id="email"
                                                        type="email"
                                                        class="form-control"
                                                        :class="{ 'is-invalid': form.errors.email }"
                                                        name="email"
                                                        placeholder="メールアドレスを入力してください"
                                                        autofocus
                                                        required
                                                        autocomplete="email"
                                                        v-model="form.email"
                                                    >
                                                    <InputError :message="form.errors.email" />
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="input-group">
                                                    <input
                                                        :type="inputType"
                                                        id="password"
                                                        class="form-control"
                                                        :class="{ 'is-invalid': form.errors.password }"
                                                        name="password"
                                                        placeholder="パスワードを入力してください"
                                                        autocomplete="current-password"
                                                        required
                                                        v-model="form.password"
                                                    >
                                                    <div class="input-group-prepend">
                                                        <button type="button" class="btn btn-info text-xs" style="width:40px;" @click="onClick">
                                                            <i :class="iconType"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                                <InputError :message="form.errors.password" />
                                            </div>
                                            <div class="col-12 mt-3">
                                                <button type="submit" class="btn btn-info btn-xl btn-block text-sm font-weight-bold logoFont" style="padding: 12px" @click="onSubmit">
                                                    ログイン
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-8 col-xxl-8 order-1 order-sm-2 d-none d-md-block bg-hover-mono">
                            <div class="row align-items-center">
                                <div class="col-7 mx-auto"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="position-absolute d-flex justify-content-center align-items-center" style="bottom:10px;left:10px;gap:5px;opacity:.7;" v-if="autoLoginUsers.length > 0">
            <div class="pr-2">AutoLogin(Only dev)</div>
            <div v-for="user in autoLoginUsers">
                <a href="#" class="btn btn-xs btn-outline-secondary" :data-email="user.email" @click.prevent="autoLogin(user)">{{ user.name }}({{ user.org_id }}-{{ user.post_id }})</a>
            </div>
        </div>
    </GuestLayout>
</template>
