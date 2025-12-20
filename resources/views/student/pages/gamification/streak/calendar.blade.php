@extends('student.layouts.master')

@section('page-title')
    تقويم السلسلة
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div>
                    <h4 class="mb-0">تقويم السلسلة</h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('gamification.streak.index') }}">السلسلة اليومية</a></li>
                            <li class="breadcrumb-item active">التقويم</li>
                        </ol>
                    </nav>
                </div>
                <div>
                    <a href="{{ route('gamification.streak.index') }}" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-arrow-right me-1"></i>العودة
                    </a>
                </div>
            </div>

            <!-- اختيار الشهر -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('gamification.streak.calendar') }}" class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label">السنة</label>
                            <select name="year" class="form-select">
                                @for($y = now()->year; $y >= now()->year - 1; $y--)
                                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">الشهر</label>
                            <select name="month" class="form-select">
                                @for($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::create(null, $m, 1)->locale('ar')->translatedFormat('F') }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search me-1"></i>عرض
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- التقويم -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar-alt me-2"></i>
                        {{ \Carbon\Carbon::create($year, $month, 1)->locale('ar')->translatedFormat('F Y') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="calendar-grid">
                        <!-- أيام الأسبوع -->
                        <div class="row mb-2">
                            @foreach(['السبت', 'الأحد', 'الاثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة'] as $day)
                                <div class="col calendar-day-header">
                                    <strong>{{ $day }}</strong>
                                </div>
                            @endforeach
                        </div>

                        <!-- الأيام -->
                        @php
                            $startDate = \Carbon\Carbon::create($year, $month, 1);
                            $endDate = $startDate->copy()->endOfMonth();
                            $firstDayOfWeek = $startDate->dayOfWeek; // 0 = Saturday
                            $daysInMonth = $endDate->day;
                        @endphp

                        <div class="row">
                            @for($i = 0; $i < $firstDayOfWeek; $i++)
                                <div class="col calendar-day-empty"></div>
                            @endfor

                            @for($day = 1; $day <= $daysInMonth; $day++)
                                @php
                                    $dateKey = \Carbon\Carbon::create($year, $month, $day)->format('Y-m-d');
                                    $isActive = isset($calendar[$dateKey]) && $calendar[$dateKey]['is_active'];
                                    $dayData = $calendar[$dateKey] ?? null;
                                @endphp
                                <div class="col calendar-day {{ $isActive ? 'active' : '' }} {{ $dateKey == now()->format('Y-m-d') ? 'today' : '' }}">
                                    <div class="calendar-day-content">
                                        <div class="calendar-day-number">{{ $day }}</div>
                                        @if($isActive)
                                            <div class="calendar-day-info">
                                                <i class="fas fa-fire text-danger"></i>
                                                @if($dayData && isset($dayData['activities_count']))
                                                    <small>{{ $dayData['activities_count'] }} نشاط</small>
                                                @endif
                                            </div>
                                            @if($dayData && isset($dayData['points_earned']) && $dayData['points_earned'] > 0)
                                                <div class="calendar-day-points">
                                                    <small class="text-success">+{{ $dayData['points_earned'] }} نقطة</small>
                                                </div>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            @endfor
                        </div>
                    </div>

                    <!-- المفتاح -->
                    <div class="mt-4 pt-3 border-top">
                        <div class="row">
                            <div class="col-md-4 mb-2">
                                <div class="d-flex align-items-center">
                                    <div class="calendar-legend active me-2"></div>
                                    <span>يوم نشط</span>
                                </div>
                            </div>
                            <div class="col-md-4 mb-2">
                                <div class="d-flex align-items-center">
                                    <div class="calendar-legend today me-2"></div>
                                    <span>اليوم</span>
                                </div>
                            </div>
                            <div class="col-md-4 mb-2">
                                <div class="d-flex align-items-center">
                                    <div class="calendar-legend me-2"></div>
                                    <span>يوم غير نشط</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
    <style>
        .calendar-grid {
            font-family: 'Cairo', sans-serif;
        }

        .calendar-day-header {
            text-align: center;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 5px;
            margin-bottom: 5px;
        }

        .calendar-day {
            min-height: 80px;
            border: 1px solid #e0e0e0;
            padding: 5px;
            margin-bottom: 5px;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        .calendar-day-empty {
            min-height: 80px;
        }

        .calendar-day.active {
            background: #fff3cd;
            border-color: #ffc107;
        }

        .calendar-day.today {
            border: 2px solid #0555a2;
            background: #e7f3ff;
        }

        .calendar-day.active.today {
            background: #d4edda;
            border-color: #28a745;
        }

        .calendar-day-content {
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .calendar-day-number {
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 5px;
        }

        .calendar-day-info {
            font-size: 11px;
            color: #666;
            margin-top: auto;
        }

        .calendar-day-points {
            font-size: 10px;
            margin-top: 2px;
        }

        .calendar-legend {
            width: 20px;
            height: 20px;
            border: 1px solid #e0e0e0;
            border-radius: 3px;
            display: inline-block;
        }

        .calendar-legend.active {
            background: #fff3cd;
            border-color: #ffc107;
        }

        .calendar-legend.today {
            border: 2px solid #0555a2;
            background: #e7f3ff;
        }
    </style>
    @endpush
@stop



