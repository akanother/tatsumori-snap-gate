<?php

namespace App\Services;

use App\Models\Order\TmsOrder;
use App\Models\Order\TmsOrderDetail;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardService
{
    /**
     * 受注（今月）集計：得意先×製品ごと数量（kg）
     */
    public function getOrderMonthSummary()
    {
        $orders = TmsOrder::with('orderItems')
            ->where('indication', '受注')
            ->whereBetween('order_date', [
                Carbon::now()->startOfMonth(),
                Carbon::now()->endOfMonth()
            ])
            ->get();

        $productIds = $orders->flatMap->orderItems->pluck('product_id')->filter()->map(fn($id) => (int)$id)->unique();
        $products = \App\Models\Product::whereIn('id', $productIds)->get()->keyBy(fn($p) => (int)$p->id);

        $raw = [];
        $summary = [];

        foreach ($orders as $order) {
            foreach ($order->orderItems as $item) {
                $customer = $order->customer_name;
                $productId = (int)$item->product_id;
                $product = $products->get($productId);
                $productName = $product?->product_name ?? '不明';

                $prev = $raw[$customer][$productName] ?? 0;
                $total = $prev + (int)$item->quantity;

                $raw[$customer][$productName] = $total;
                $summary[$customer][$productName] = number_format($total / 1000, 2) . ' kg';
            }
        }

        return $summary;
    }

    /**
     * 内示（今月〜+3か月）集計：年月×得意先×製品ごと数量（kg）
     */
    public function getForecastQuarterSummaryWithCustomer()
    {
        $start = Carbon::now()->startOfMonth();
        $end = Carbon::now()->copy()->addMonths(3)->endOfMonth();

        $orders = TmsOrder::with('orderItems')
            ->where('indication', '内示')
            ->whereBetween('order_date', [$start, $end])
            ->get();

        $productIds = $orders->flatMap->orderItems->pluck('product_id')->filter()->map(fn($id) => (int)$id)->unique();
        $products = \App\Models\Product::whereIn('id', $productIds)->get()->keyBy(fn($p) => (int)$p->id);

        $summary = [];

        foreach ($orders as $order) {
            $customer = $order->customer_name;
            $yearMonth = Carbon::parse($order->order_date)->format('Y-m');

            foreach ($order->orderItems as $item) {
                $productId = (int)$item->product_id;
                $product = $products->get($productId);
                $productName = $product?->product_name ?? '不明';

                $prev = $summary[$yearMonth][$customer][$productName] ?? 0;
                $total = $prev + (int)$item->quantity;

                $summary[$yearMonth][$customer][$productName] = number_format($total / 1000, 2) . ' kg';
            }
        }

        return $summary;
    }

    /* ===================== ここからダッシュボード未回答メトリクス ===================== */

    /**
     * ウィジェット用 未回答メトリクス
     * - backlog_total: 全期間の未回答累計（バックログ）
     * - new_this_month: 今月受注で未回答
     * - new_prev_month: 先月受注で未回答
     * - new_today: 今日受注で未回答
     * - new_7days: 直近7日受注で未回答
     */
    public function getUnansweredDashboardMetrics(): array
    {
        $today = Carbon::today();

        $startThisMonth = $today->copy()->startOfMonth();
        $endThisMonth   = $today->copy()->endOfMonth();

        $startPrevMonth = $today->copy()->subMonthNoOverflow()->startOfMonth();
        $endPrevMonth   = $today->copy()->subMonthNoOverflow()->endOfMonth();

        return [
            'backlog_total'  => $this->countUnansweredAllTime(),
            'new_this_month' => $this->countUnansweredBetween($startThisMonth, $endThisMonth),
            'new_prev_month' => $this->countUnansweredBetween($startPrevMonth, $endPrevMonth),
            'new_today'      => $this->countUnansweredBetween($today->copy()->startOfDay(), $today->copy()->endOfDay()),
            'new_7days'      => $this->countUnansweredBetween($today->copy()->subDays(6)->startOfDay(), $today->copy()->endOfDay()),
        ];
    }

    /**
     * 全期間の未回答件数（バックログ合計）
     * 条件：
     * - delivery_responses.confirmed_delivery_date が NULL
     * - 分納スケジュールが0件（キャンセル済みは除外）
     */
    public function countUnansweredAllTime(): int
    {
        $scheduleCounts = DB::table('tms_delivery_schedules as ds')
            ->select('ds.order_detail_id', DB::raw('COUNT(ds.id) as schedule_count'))
            ->whereNull('ds.deleted_at')
            ->groupBy('ds.order_detail_id');

        return (int) DB::table('tms_orders as o')
            ->join('tms_order_details as od', 'od.order_id', '=', 'o.id')
            ->leftJoin('tms_delivery_responses as dr', 'dr.order_detail_id', '=', 'od.id')
            ->leftJoinSub($scheduleCounts, 'sc', function ($join) {
                $join->on('sc.order_detail_id', '=', 'od.id');
            })
            ->where(function ($q) {
                $q->whereNull('dr.confirmed_delivery_date'); // 単納/分納どちらでも未確定
            })
            ->where(function ($q) {
                $q->whereNull('sc.schedule_count')
                    ->orWhere('sc.schedule_count', '=', 0);
            })
            ->count('od.id');
    }

    /**
     * 期間内の未回答件数
     * （order_date が期間内、かつ未回答・分納スケジュール0件）
     */
    public function countUnansweredBetween(Carbon $start, Carbon $end): int
    {
        $scheduleCounts = DB::table('tms_delivery_schedules as ds')
            ->select('ds.order_detail_id', DB::raw('COUNT(ds.id) as schedule_count'))
            ->whereNull('ds.deleted_at')
            ->groupBy('ds.order_detail_id');

        return (int) DB::table('tms_orders as o')
            ->join('tms_order_details as od', 'od.order_id', '=', 'o.id')
            ->leftJoin('tms_delivery_responses as dr', 'dr.order_detail_id', '=', 'od.id')
            ->leftJoinSub($scheduleCounts, 'sc', function ($join) {
                $join->on('sc.order_detail_id', '=', 'od.id');
            })
            ->whereBetween('o.order_date', [$start->toDateString(), $end->toDateString()])
            ->where(function ($q) {
                $q->whereNull('dr.confirmed_delivery_date');
            })
            ->where(function ($q) {
                $q->whereNull('sc.schedule_count')
                    ->orWhere('sc.schedule_count', '=', 0);
            })
            ->count('od.id');
    }

    /**
     * 納期超過（受注のみ/未消化）の超過日数バケット件数
     * 1〜7日 / 8〜14日 / 15〜30日
     */
    public function getOverdueDeliveryBuckets(): array
    {
        $today = Carbon::today()->toDateString();

        $row = TmsOrderDetail::query()
            ->join('tms_orders as o', 'o.id', '=', 'tms_order_details.order_id')
            ->where('o.indication', '受注')
            ->whereDate('tms_order_details.requested_delivery_date', '<', $today)
            ->where('tms_order_details.remaining_quantity', '>', 0)
            ->selectRaw(
            // MySQL: DATEDIFF(基準日, 比較日) で「何日超過か」を算出
                "SUM(CASE WHEN DATEDIFF(?, tms_order_details.requested_delivery_date) BETWEEN 1 AND 7  THEN 1 ELSE 0 END) as w1,
                 SUM(CASE WHEN DATEDIFF(?, tms_order_details.requested_delivery_date) BETWEEN 8 AND 14 THEN 1 ELSE 0 END) as w2,
                 SUM(CASE WHEN DATEDIFF(?, tms_order_details.requested_delivery_date) BETWEEN 15 AND 30 THEN 1 ELSE 0 END) as w4",
                [$today, $today, $today]
            )
            ->first();

        return [
            'w1' => (int) ($row->w1 ?? 0),  // 超過 1〜7日
            'w2' => (int) ($row->w2 ?? 0),  // 超過 8〜14日
            'w4' => (int) ($row->w4 ?? 0),  // 超過 15〜30日
        ];
    }

    //public function getUpcomingRequestedDeliveries(): array
    //{
    //    $today = Carbon::today();
    //    $end   = $today->copy()->addDays(30);
    //
    //    // 当日以降〜30日、受注のみ（内示除外）
    //    $details = TmsOrderDetail::query()
    //        ->with([
    //            'order:id,customer_name,indication',
    //            'product:id,product_name',
    //        ])
    //        // ← whereDate で日付のみ比較（タイムゾーン差異の影響を避ける）
    //        ->whereDate('requested_delivery_date', '>=', $today->toDateString())
    //        ->whereDate('requested_delivery_date', '<=', $end->toDateString())
    //        // ← indication を TRIM して '受注' を比較（末尾/先頭空白対策）
    //        ->whereHas('order', function ($q) {
    //            $q->where(DB::raw('TRIM(indication)'), '受注');
    //        })
    //        ->orderBy('requested_delivery_date')
    //        ->get();
    //
    //    $grouped = [];
    //
    //    foreach ($details as $d) {
    //        $date = $d->requested_delivery_date; // 'YYYY-MM-DD'
    //        $grouped[$date] ??= [];
    //        $grouped[$date][] = [
    //            'order_detail_id' => $d->id,
    //            'order_id'        => $d->order_id,
    //            'customer_name'   => optional($d->order)->customer_name ?? '不明',
    //            'product_name'    => optional($d->product)->product_name ?? '不明',
    //            'quantity_kg'     => round(($d->quantity ?? 0) / 1000, 3),
    //        ];
    //    }
    //
    //    // 30日分のキーを必ず用意
    //    $cursor = $today->copy();
    //    while ($cursor->lte($end)) {
    //        $key = $cursor->toDateString();
    //        $grouped[$key] = $grouped[$key] ?? [];
    //        $cursor->addDay();
    //    }
    //
    //    ksort($grouped);
    //    return $grouped;
    //}
    public function getUpcomingRequestedDeliveries(?string $organization = null): array
    {
        $today = Carbon::today();
        $end   = $today->copy()->addDays(30);

        // 当日以降〜30日、受注のみ（内示除外）
        $q = TmsOrderDetail::query()
            ->with([
                'order:id,customer_name,indication',
                'product:id,product_name',
            ])
            // 日付のみ比較（TZ差異回避）
            ->whereDate('requested_delivery_date', '>=', $today->toDateString())
            ->whereDate('requested_delivery_date', '<=', $end->toDateString())
            // 受注のみ（indicationに空白等が混ざっても防ぐ）
            ->whereHas('order', function ($q) {
                $q->where(DB::raw('TRIM(indication)'), '受注');
            });

        // ★ 拠点フィルタ（null/空文字の時は全件）
        if (!is_null($organization) && $organization !== '') {
            $q->where('delivery_organization', $organization);
        }

        $details = $q->orderBy('requested_delivery_date')->get();

        $grouped = [];

        foreach ($details as $d) {
            $date = $d->requested_delivery_date; // 'YYYY-MM-DD'
            $grouped[$date] ??= [];
            $grouped[$date][] = [
                'order_detail_id' => $d->id,
                'order_id'        => $d->order_id,
                'customer_name'   => optional($d->order)->customer_name ?? '不明',
                'product_name'    => optional($d->product)->product_name ?? '不明',
                'quantity_kg'     => round(($d->quantity ?? 0) / 1000, 3),
            ];
        }

        // 30日分のキーを必ず用意（欠け補完）
        $cursor = $today->copy();
        while ($cursor->lte($end)) {
            $key = $cursor->toDateString();
            $grouped[$key] = $grouped[$key] ?? [];
            $cursor->addDay();
        }

        ksort($grouped);
        return $grouped;
    }

}
