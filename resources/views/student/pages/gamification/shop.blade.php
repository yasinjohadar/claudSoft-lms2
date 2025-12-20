@extends('student.layouts.master')

@section('page-title')
    المتجر
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <h4 class="mb-0">المتجر</h4>
                <div>
                    <span class="badge bg-primary fs-6 me-2"><i class="fas fa-star me-1"></i>{{ $userPoints ?? 0 }} نقطة</span>
                    <span class="badge bg-warning fs-6"><i class="fas fa-gem me-1"></i>{{ $userGems ?? 0 }} جوهرة</span>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <!-- الفئات -->
            @forelse($categories ?? [] as $category)
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">{{ $category->icon ?? '📦' }} {{ $category->name }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @forelse($category->items ?? [] as $item)
                                <div class="col-lg-3 col-md-4 col-6 mb-4">
                                    <div class="card border-0 shadow-sm h-100">
                                        <div class="card-body text-center">
                                            <div class="fs-1 mb-3">{{ $item->icon ?? '🎁' }}</div>
                                            <h6 class="fw-bold">{{ $item->name }}</h6>
                                            <p class="small text-muted mb-3">{{ $item->description }}</p>

                                            <div class="mb-3">
                                                @if($item->price_points > 0)
                                                    <span class="badge bg-primary me-1">{{ $item->price_points }} نقطة</span>
                                                @endif
                                                @if($item->price_gems > 0)
                                                    <span class="badge bg-warning">{{ $item->price_gems }} جوهرة</span>
                                                @endif
                                            </div>

                                            @if($item->required_level > 1)
                                                <p class="small text-muted mb-2">المستوى المطلوب: {{ $item->required_level }}</p>
                                            @endif

                                            @if($item->stock !== null)
                                                <p class="small text-muted mb-2">متبقي: {{ $item->stock }}</p>
                                            @endif

                                            <form action="{{ route('gamification.shop.purchase', $item->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-primary w-100"
                                                    @if(($userPoints ?? 0) < $item->price_points || ($userGems ?? 0) < $item->price_gems) disabled @endif>
                                                    <i class="fas fa-shopping-cart me-1"></i> شراء
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12">
                                    <p class="text-muted text-center py-3">لا توجد منتجات في هذه الفئة</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            @empty
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-store fa-3x text-muted mb-3"></i>
                        <p class="text-muted">المتجر فارغ حالياً</p>
                    </div>
                </div>
            @endforelse

            <!-- مشترياتي -->
            @if(count($myPurchases ?? []) > 0)
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-light">
                        <h5 class="mb-0"><i class="fas fa-shopping-bag me-2"></i>مشترياتي</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>المنتج</th>
                                        <th>السعر</th>
                                        <th>تاريخ الشراء</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($myPurchases as $purchase)
                                        <tr>
                                            <td>{{ $purchase->item->icon ?? '' }} {{ $purchase->item->name }}</td>
                                            <td>{{ $purchase->price_paid }} {{ $purchase->currency == 'points' ? 'نقطة' : 'جوهرة' }}</td>
                                            <td>{{ $purchase->created_at->format('Y/m/d') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@stop
