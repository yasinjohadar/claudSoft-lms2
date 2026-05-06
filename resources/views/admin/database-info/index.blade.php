@extends('admin.layouts.master')

@section('page-title')
    معلومات قاعدة البيانات
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div>
                    <h4 class="mb-1">معلومات قاعدة البيانات</h4>
                    <p class="mb-0 text-muted">عرض تفاصيل كاملة عن قاعدة البيانات والجداول</p>
                </div>
                <div class="btn-list mt-3 mt-md-0">
                    <a href="{{ route('admin.database-info.index') }}" class="btn btn-primary btn-wave">
                        <i class="ri-refresh-line me-2"></i>تحديث البيانات
                    </a>
                </div>
            </div>

            @include('admin.components.alerts')

            <div class="row">
                <div class="col-xl-3 col-md-6">
                    <div class="card custom-card overflow-hidden">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-md bg-primary-transparent">
                                    <i class="ri-database-2-line fs-20 text-primary"></i>
                                </div>
                                <div class="ms-3">
                                    <p class="text-muted mb-0 fs-13">حجم قاعدة البيانات</p>
                                    <h4 class="mb-0 fw-semibold">{{ number_format($totalSize->total_size_mb, 2) }} MB</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card custom-card overflow-hidden">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-md bg-success-transparent">
                                    <i class="ri-table-alt-line fs-20 text-success"></i>
                                </div>
                                <div class="ms-3">
                                    <p class="text-muted mb-0 fs-13">عدد الجداول</p>
                                    <h4 class="mb-0 fw-semibold">{{ $totalTables }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card custom-card overflow-hidden">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-md bg-info-transparent">
                                    <i class="ri-database-line fs-20 text-info"></i>
                                </div>
                                <div class="ms-3">
                                    <p class="text-muted mb-0 fs-13">إجمالي السجلات</p>
                                    <h4 class="mb-0 fw-semibold">{{ number_format($totalRows->total_rows ?? 0) }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card custom-card overflow-hidden">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-md bg-warning-transparent">
                                    <i class="ri-hard-drive-2-line fs-20 text-warning"></i>
                                </div>
                                <div class="ms-3">
                                    <p class="text-muted mb-0 fs-13">أكبر جدول</p>
                                    <h6 class="mb-0 fw-semibold text-truncate" title="{{ $largestTable->table_name ?? 'لا يوجد' }}">
                                        {{ $largestTable->table_name ?? 'لا يوجد' }}
                                    </h6>
                                    <small class="text-muted">{{ $largestTable ? number_format($largestTable->size_mb, 2) . ' MB' : '' }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-xl-4">
                    <div class="card custom-card">
                        <div class="card-header justify-content-between">
                            <div class="card-title">معلومات الاتصال</div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered mb-0">
                                    <tbody>
                                        <tr>
                                            <th class="text-muted" style="width: 40%;">نوع قاعدة البيانات</th>
                                            <td class="fw-semibold">{{ strtoupper($dbDriver) }}</td>
                                        </tr>
                                        <tr>
                                            <th class="text-muted">اسم قاعدة البيانات</th>
                                            <td class="fw-semibold">{{ $dbName }}</td>
                                        </tr>
                                        <tr>
                                            <th class="text-muted">المضيف</th>
                                            <td class="fw-semibold">{{ $dbHost }}</td>
                                        </tr>
                                        <tr>
                                            <th class="text-muted">ترميز البيانات</th>
                                            <td class="fw-semibold">
                                                @php
                                                    $collation = DB::selectOne("SELECT @@collation_connection as collation");
                                                @endphp
                                                {{ $collation->collation ?? 'غير محدد' }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-8">
                    <div class="card custom-card">
                        <div class="card-header justify-content-between">
                            <div class="card-title">توزيع حجم قاعدة البيانات</div>
                        </div>
                        <div class="card-body">
                            @php
                                $totalData = array_sum(array_column($tables, 'data_mb'));
                                $totalIndex = array_sum(array_column($tables, 'index_mb'));
                                $dataPercent = $totalSize->total_size_mb > 0 ? ($totalData / $totalSize->total_size_mb) * 100 : 0;
                                $indexPercent = $totalSize->total_size_mb > 0 ? ($totalIndex / $totalSize->total_size_mb) * 100 : 0;
                            @endphp
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-muted">بيانات</span>
                                    <span class="fw-semibold">{{ number_format($totalData, 2) }} MB ({{ number_format($dataPercent, 1) }}%)</span>
                                </div>
                                <div class="progress progress-sm">
                                    <div class="progress-bar bg-primary" style="width: {{ $dataPercent }}%"></div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-muted">فهارس (Indexes)</span>
                                    <span class="fw-semibold">{{ number_format($totalIndex, 2) }} MB ({{ number_format($indexPercent, 1) }}%)</span>
                                </div>
                                <div class="progress progress-sm">
                                    <div class="progress-bar bg-success" style="width: {{ $indexPercent }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card custom-card">
                <div class="card-header justify-content-between">
                    <div class="card-title">
                        <i class="ri-table-2 me-1"></i>
                        تفاصيل الجداول
                    </div>
                    <span class="badge bg-primary">{{ $totalTables }} جدول</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered text-nowrap table-hover" id="database-tables">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>اسم الجدول</th>
                                    <th>المحرك</th>
                                    <th>عدد السجلات</th>
                                    <th>حجم البيانات</th>
                                    <th>حجم الفهارس</th>
                                    <th>الحجم الكلي</th>
                                    <th>الترميز</th>
                                    <th>تاريخ الإنشاء</th>
                                    <th>آخر تحديث</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($tables as $index => $table)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            <span class="fw-semibold text-primary">{{ $table->table_name }}</span>
                                        </td>
                                        <td><span class="badge bg-light text-dark">{{ $table->engine }}</span></td>
                                        <td>
                                            <span class="fw-semibold">{{ number_format($table->row_count) }}</span>
                                        </td>
                                        <td>{{ number_format($table->data_mb, 2) }} MB</td>
                                        <td>{{ number_format($table->index_mb, 2) }} MB</td>
                                        <td>
                                            <span class="badge bg-primary">{{ number_format($table->size_mb, 2) }} MB</span>
                                        </td>
                                        <td><small>{{ $table->collation }}</small></td>
                                        <td>
                                            <small>{{ $table->created_at ? \Carbon\Carbon::parse($table->created_at)->format('Y-m-d') : '-' }}</small>
                                        </td>
                                        <td>
                                            <small>{{ $table->updated_at ? \Carbon\Carbon::parse($table->updated_at)->format('Y-m-d H:i') : '-' }}</small>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <form action="{{ route('admin.database-info.optimize', $table->table_name) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-outline-success btn-wave" title="تحسين الجدول" onclick="return confirm('هل تريد تحسين هذا الجدول؟')">
                                                        <i class="ri-magic-line"></i>
                                                    </button>
                                                </form>
                                                <form action="{{ route('admin.database-info.analyze', $table->table_name) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-outline-info btn-wave" title="تحليل الجدول" onclick="return confirm('هل تريد تحليل هذا الجدول؟')">
                                                        <i class="ri-search-line"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="table-primary">
                                    <th colspan="3">الإجمالي</th>
                                    <th>{{ number_format($totalRows->total_rows ?? 0) }}</th>
                                    <th>{{ number_format($totalData, 2) }} MB</th>
                                    <th>{{ number_format($totalIndex, 2) }} MB</th>
                                    <th>{{ number_format($totalSize->total_size_mb, 2) }} MB</th>
                                    <th colspan="4"></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            $('#database-tables').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/ar.json'
                },
                order: [[6, 'desc']],
                pageLength: 25,
                dom: 'Bfrtip',
                buttons: [
                    {
                        extend: 'copy',
                        text: 'نسخ',
                        className: 'btn btn-sm btn-outline-primary'
                    },
                    {
                        extend: 'csv',
                        text: 'CSV',
                        className: 'btn btn-sm btn-outline-success'
                    },
                    {
                        extend: 'excel',
                        text: 'Excel',
                        className: 'btn btn-sm btn-outline-success'
                    },
                    {
                        extend: 'print',
                        text: 'طباعة',
                        className: 'btn btn-sm btn-outline-info'
                    }
                ]
            });
        });
    </script>
@endpush
