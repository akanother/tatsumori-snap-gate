<script>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PanelOrderSummary from '@/Pages/Dashboard/PanelOrderSummary.vue';
import PanelForecastSummary from "@/Pages/Dashboard/PanelForecastSummary.vue";
import LatestUnansweredOrdersPanel from '@/Pages/Dashboard/LatestUnansweredOrdersPanel.vue';
import OverdueOrdersPanel from "@/Pages/Dashboard/OverdueOrdersPanel.vue";
import UpcomingRequestedDeliveriesPanel from "@/Pages/Dashboard/UpcomingRequestedDeliveriesPanel.vue";

import { Head } from '@inertiajs/vue3';

export default {
    components: {
        AuthenticatedLayout,
        PanelOrderSummary,
        PanelForecastSummary,
        LatestUnansweredOrdersPanel,
        OverdueOrdersPanel,
        UpcomingRequestedDeliveriesPanel,
        Head
    },
    props: {
        auth: Object,
        errors: Object,
        flash: Object,
    },
}
</script>

<template>
    <Head title="ダッシュボード" />

    <AuthenticatedLayout>
        <template #page-title>
            ダッシュボード
        </template>
        <template #breadcrumb>
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item active">
                    <i class="fa fa-home mr-1"></i>
                    ダッシュボード
                </li>
            </ol>
        </template>

        <div>
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="row mb-3">
                    <LatestUnansweredOrdersPanel></LatestUnansweredOrdersPanel>
                    <OverdueOrdersPanel></OverdueOrdersPanel>
                    <div class="col-sm-3">
                        <div class="card widget-flat h-100">
                            <div class="card-header d-flex align-items-center justify-content-between">
                                <h6 class="card-title text-muted fw-normal mb-0" title="Number of Orders">本日の出荷予定 (M / D)</h6>
                            </div>

                            <div class="card-body">
                                <div class="float-end">
                                    <i class="mdi mdi-cart-plus widget-icon"></i>
                                </div>
                                <h3 class="mt-3 mb-3">準備中です・・・</h3>
                                <p class="mb-0 text-muted">
                                    <span class="text-danger me-2"><i class="mdi mdi-arrow-down-bold"></i> Panel Sub Title</span>
                                    <span class="text-nowrap">Panel Footer</span>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="card widget-flat h-100">
                            <div class="card-header d-flex align-items-center justify-content-between">
                                <h6 class="card-title text-muted fw-normal mb-0" title="Number of Orders">明日の出荷予定 (M / D)</h6>
                            </div>

                            <div class="card-body">
                                <div class="float-end">
                                    <i class="mdi mdi-cart-plus widget-icon"></i>
                                </div>
                                <h3 class="mt-3 mb-3">準備中です・・・</h3>
                                <p class="mb-0 text-muted">
                                    <span class="text-danger me-2"><i class="mdi mdi-arrow-down-bold"></i> Panel Sub Title</span>
                                    <span class="text-nowrap">Panel Footer</span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <UpcomingRequestedDeliveriesPanel></UpcomingRequestedDeliveriesPanel>

                    <div class="col-3">
                        <PanelOrderSummary></PanelOrderSummary>
                    </div>
                    <div class="col-3">
                        <PanelForecastSummary></PanelForecastSummary>
                    </div>
                </div>

                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-4 text-gray-900">

                        ここにダッシュボードのコンテンツを追加します。
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
