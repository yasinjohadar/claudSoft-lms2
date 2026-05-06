<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class DatabaseInfoController extends Controller
{
    public function index()
    {
        $dbName = config('database.connections.' . config('database.default') . '.database');
        $dbHost = config('database.connections.' . config('database.default') . '.host');
        $dbDriver = config('database.connections.' . config('database.default') . '.driver');

        $tables = DB::select("
            SELECT 
                TABLE_NAME as table_name,
                TABLE_ROWS as row_count,
                ROUND(((DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024), 2) AS size_mb,
                ROUND((DATA_LENGTH / 1024 / 1024), 2) AS data_mb,
                ROUND((INDEX_LENGTH / 1024 / 1024), 2) AS index_mb,
                ENGINE as engine,
                TABLE_COLLATION as collation,
                CREATE_TIME as created_at,
                UPDATE_TIME as updated_at
            FROM information_schema.TABLES 
            WHERE TABLE_SCHEMA = ? 
            AND TABLE_TYPE = 'BASE TABLE'
            ORDER BY (DATA_LENGTH + INDEX_LENGTH) DESC
        ", [$dbName]);

        $totalSize = DB::selectOne("
            SELECT ROUND(SUM(DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024, 2) as total_size_mb
            FROM information_schema.TABLES 
            WHERE TABLE_SCHEMA = ?
        ", [$dbName]);

        $totalRows = DB::selectOne("
            SELECT SUM(TABLE_ROWS) as total_rows
            FROM information_schema.TABLES 
            WHERE TABLE_SCHEMA = ?
        ", [$dbName]);

        $totalTables = count($tables);

        $largestTable = $tables[0] ?? null;

        return view('admin.database-info.index', compact(
            'tables',
            'totalSize',
            'totalRows',
            'totalTables',
            'dbName',
            'dbHost',
            'dbDriver',
            'largestTable'
        ));
    }

    public function optimize($tableName)
    {
        try {
            DB::statement("OPTIMIZE TABLE " . DB::getPdo()->quote($tableName));
            return back()->with('success', 'تم تحسين الجدول بنجاح: ' . $tableName);
        } catch (\Exception $e) {
            return back()->with('error', 'حدث خطأ أثناء تحسين الجدول: ' . $e->getMessage());
        }
    }

    public function analyze($tableName)
    {
        try {
            DB::statement("ANALYZE TABLE " . DB::getPdo()->quote($tableName));
            return back()->with('success', 'تم تحليل الجدول بنجاح: ' . $tableName);
        } catch (\Exception $e) {
            return back()->with('error', 'حدث خطأ أثناء تحليل الجدول: ' . $e->getMessage());
        }
    }
}
