<?php

namespace App\Http\Controllers\Admin\Gamification;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Gamification\ShopService;
use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    protected ShopService $shopService;

    public function __construct(ShopService $shopService)
    {
        $this->shopService = $shopService;
    }

    /**
     * عرض قائمة المشتريات
     * ملاحظة: ميزة المشتريات قيد التطوير
     */
    public function index(Request $request)
    {
        return redirect()->route('admin.gamification.shop.items.index')
            ->with('info', 'ميزة المشتريات قيد التطوير حالياً');
    }

    /**
     * عرض تفاصيل مشترية
     * ملاحظة: ميزة المشتريات قيد التطوير
     */
    public function show($id)
    {
        return redirect()->route('admin.gamification.shop.items.index')
            ->with('info', 'ميزة المشتريات قيد التطوير حالياً');
    }

    /**
     * إحصائيات المشتريات
     * ملاحظة: ميزة المشتريات قيد التطوير
     */
    public function statistics(Request $request)
    {
        return redirect()->route('admin.gamification.shop.items.index')
            ->with('info', 'ميزة المشتريات قيد التطوير حالياً');
    }

    /**
     * تقرير المشتريات
     * ملاحظة: ميزة المشتريات قيد التطوير
     */
    public function report(Request $request)
    {
        return redirect()->route('admin.gamification.shop.items.index')
            ->with('info', 'ميزة المشتريات قيد التطوير حالياً');
    }
}
