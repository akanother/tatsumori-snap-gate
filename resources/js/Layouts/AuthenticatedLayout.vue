<script>
import { Link } from '@inertiajs/vue3';
import MainSidebar from '@/Components/MainSidebar.vue';

export default {
    data() {
        return {
            appName: this.$page.props.config.appName,
            user: this.$page.props.auth.user,
        }
    },
    components: {
        Link,
        MainSidebar,
    },
}
</script>

<template>
    <div>
        <div class="wrapper">
            <!-- Navbar -->
            <nav class="main-header navbar navbar-expand navbar-white navbar-light">
                <!-- Left navbar links -->
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
                    </li>
                    <li class="nav-item d-flex align-items-center">
                        <Link :href="route('dashboard')" class="nav-link text-sm" style="padding-top:9px;">ダッシュボード</Link>
                    </li>
                </ul>

                <!-- Right navbar links -->
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item">
                        <Link
                            :href="route('logout')"
                            method="post"
                            class="btn btn-dark btn-xs mr-1"
                            style="padding: 5px 12px !important;"
                            >ログアウト</Link
                        >
                    </li>
                </ul>
            </nav>
            <!-- /.navbar -->

            <!-- Main Sidebar Container -->
            <MainSidebar :appName="appName" />

            <!-- Content Wrapper. Contains page content -->
            <div class="content-wrapper px-3 py-4">
                <div class="content-header">
                    <div class="container-fluid">
                        <div class="d-flex align-items-center mb-4" style="gap:15px;">
                            <div class="font-weight-bold pl-2 pr-4 py-0" style="font-size:1.5rem;border-right:1px solid #ddd;">
                                <slot name="page-title"></slot>
                            </div>
                            <div class="text-sm pl-2" style="opacity: .8;">
                                <slot name="breadcrumb"></slot>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /.content-header -->

                <!-- Main content -->
                <section class="content">
                    <div class="container-fluid">

                        <slot></slot>
                    </div>
                </section>
            </div>
            <!-- /.content-wrapper -->

            <footer class="main-footer text-xs" style="max-height:10px;">
                <strong>&copy; Tatsumori inc.</strong>
                All rights reserved.
                <div class="float-right d-none d-sm-inline-block">
                TATSUMORI SYSTEM-G <i class="fa fa-users mx-2"></i> by TKKLNCGH
                </div>
            </footer>

            <!-- Control Sidebar -->
            <aside class="control-sidebar control-sidebar-dark">
                <!-- Control sidebar content goes here -->
            </aside>

        </div>
        <!-- ./wrapper -->
    </div>
</template>
