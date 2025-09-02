<script>
import { Link } from '@inertiajs/vue3';

export default {
    data() {
        return {
            appName: this.$page.props.config.appName,
            user: this.$page.props.auth.user,
        }
    },
    methods: {
        initMainMenu() {
            $('.nav-link').on('click', (e) => {
                const $target = $(e.currentTarget);
                const $treeview = $target.next('.nav-treeview');
                if ($treeview.length) {
                    e.preventDefault();
                    const $parent = $target.parent();
                    if ($parent.hasClass('menu-open')) {
                        $treeview.stop(true, true).slideUp(300, function() {
                            $parent.removeClass('menu-open');
                        });
                    } else {
                        $treeview.stop(true, true).slideDown(300, function() {
                            $parent.addClass('menu-open');
                        });
                    }
                }
            });

            // 選択中のメニューをハイライト
            const currentPath = window.location.pathname;
            const treeviewNavLinks = document.querySelectorAll('.nav-item');
            [].forEach.call(treeviewNavLinks, (link) => {
                const path = link.getAttribute('data-path');
                if (path === currentPath) {
                    link.style.backgroundColor = 'rgba(255,255,255,.1)';
                    const parent = link.closest('.nav-treeview');
                    if (parent) {
                        parent.style.display = 'block';
                        parent.parentElement.classList.add('menu-open');
                    }
                }
            });

            // ページ表示時にでメニューをcollapse状態にしたいパス
            const collapsiblePaths = [
                '/order/register/index',

                // ここに他のパスを追加してください
            ];
            const bodyElement = document.querySelector('body');
            if( collapsiblePaths.includes(currentPath)) {
                bodyElement.classList.add('sidebar-collapse');
            } else {
                bodyElement.classList.remove('sidebar-collapse');
            }
        },
        logout() {
            this.$inertia.post(this.route('logout'), {}, {
                preserveScroll: true,
                preserveState: true,
            });
        },
    },
    computed: {
        menuPermissions() {
            const { org_id, post_id } = this.user;

            return {
                orderRegister: [1].includes(org_id) && [5,6].includes(post_id),
                orderList: [1].includes(org_id) && [5, 6].includes(post_id),
                orderListFactory: [2, 7].includes(org_id) && [5].includes(post_id),
                // orderListFactory: org_id === 4 && post_id === 8,
            };
        }
    },
    mounted() {
        this.initMainMenu();
    },
    components: {
        Link,
    },
}
</script>

<template>
    <!-- Main Sidebar Container -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <!-- Brand Logo -->
        <Link :href="route('dashboard')" class="brand-link">
            <img src="/images/logo.svg" alt="Logo" class="brand-image elevation-3" style="border-radius:10%;background-color: #EBECEC;padding: 6px;margin-top: 5px;margin-right:0.7rem;">
            <div class="brand-text text-md">
                <div class="text-md font-weight-bold" style="letter-spacing:0.015rem;">{{ appName }}</div>
                <div style="opacity:.8;font-size:11px;">受注管理システム</div>
            </div>
        </Link>

        <!-- Sidebar -->
        <div class="sidebar">
        <!-- Sidebar user panel (optional) -->
        <div class="user-panel mt-3 pb-3 mb-4 d-flex align-items-center">
            <div class="image">
                <img src="/images/auth.svg" alt="Logo" class="brand-image elevation-3" style="border-radius:10%;background-color: #EBECEC;padding: 6px;margin-top: 5px;margin-right:0.7rem;opacity:.85;">
            </div>
            <div style="padding-top:7px;">
                <a href="#" class="d-block text-xs">{{ user.name }} <span style="opacity:.8">さん</span></a>
            </div>
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            {{user.org_id}}
            <ul class="nav nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                <li class="nav-header">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>メイン</div>
                        <div class="text-xs" style="opacity: 0.3;">―― Main</div>
                    </div>
                </li>
                <li class="nav-item" data-path="/dashboard">
                    <Link :href="route('dashboard')" class="nav-link">
                    <i class="nav-icon fa fa-gauge"></i>
                    <p>ダッシュボード</p>
                    </Link>
                </li>
                <li class="nav-item mb-3">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fa fa-rectangle-list"></i>
                        <p>受注管理<i class="fas fa-angle-left right"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item" data-path="/order/register/index" v-if="menuPermissions.orderRegister">
                            <Link :href="route('order.register.index')" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>受注情報登録</p>
                            </Link>
                        </li>
                        <li class="nav-item" data-path="/order/list/index" v-if="menuPermissions.orderList">
                            <Link :href="route('order.list.index')" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>受注一覧</p>
                            </Link>
                        </li>
                        <li class="nav-item" data-path="/order/list/index/factory" v-if="menuPermissions.orderListFactory">
                            <Link :href="route('order.list.index.factory')" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>受注一覧（工場）</p>
                            </Link>
                        </li>
                    </ul>
                </li>
                <li class="nav-header">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>その他</div>
                        <div class="text-xs" style="opacity: 0.3;">―― Others</div>
                    </div>
                </li>
                <li class="nav-item">
                    <Link href="#" class="nav-link" @click.prevent="logout">
                        <i class="nav-icon fa fa-arrow-right-from-bracket"></i>
                        <p>ログアウト</p>
                    </Link>
                    <form id="logout-form" method="post" :action="route('logout')" class="d-none">
                        @csrf
                    </form>
                </li>
            </ul>
        </nav>
        <!-- /.sidebar-menu -->
        </div>
        <!-- /.sidebar -->
        <div class="position-absolute text-muted text-xs" style="left:18px;bottom:8px;">
            System version 1.0.0
        </div>
    </aside>
</template>

<style lang="css" scoped>

</style>
