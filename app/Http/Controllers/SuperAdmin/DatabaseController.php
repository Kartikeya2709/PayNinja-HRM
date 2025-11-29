<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DatabaseController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:superadmin');
    }

    /**
     * Display a listing of database tables.
     */
    public function index(Request $request)
    {
        $tables = DB::select('SHOW TABLES');
        $tableNames = array_map(function($table) {
            return array_values((array)$table)[0];
        }, $tables);

        $search = $request->input('search');
        $filteredTableNames = $tableNames;

        if ($search) {
            $filteredTableNames = array_filter($tableNames, function($tableName) use ($search) {
                return stripos($tableName, $search) !== false;
            });
        }

        $tableInfo = [];
        foreach ($filteredTableNames as $tableName) {
            try {
                $recordCount = DB::table($tableName)->count();
                $columns = Schema::getColumnListing($tableName);
                $tableInfo[] = [
                    'name' => $tableName,
                    'record_count' => $recordCount,
                    'column_count' => count($columns),
                    'columns' => $columns
                ];
            } catch (\Exception $e) {
                // Skip tables that can't be accessed
                continue;
            }
        }

        $totalTables = count($tableInfo);
        $totalRecords = array_sum(array_column($tableInfo, 'record_count'));
        $allTablesCount = count($tableNames);
        $allRecordsCount = array_sum(array_map(function($tableName) {
            try {
                return DB::table($tableName)->count();
            } catch (\Exception $e) {
                return 0;
            }
        }, $tableNames));

        return view('superadmin.database.index', compact('tableInfo', 'totalTables', 'totalRecords', 'allTablesCount', 'allRecordsCount', 'search'));
    }

    /**
     * Show the data in a specific table.
     */
    public function showTable(Request $request, $table)
    {
        // Validate table exists
        if (!Schema::hasTable($table)) {
            return redirect()->route('superadmin.database.index')->with('error', 'Table does not exist.');
        }

        // Clear query results if requested
        if ($request->input('clear_query')) {
            session()->forget(['query_results_' . $table, 'query_sql_' . $table, 'query_total_' . $table]);
        }

        // Get table columns
        $columns = Schema::getColumnListing($table);

        $queryResults = null;
        $query = null;
        $isSelect = false;
        $affectedRows = 0;

        // Handle query execution if POST
        if ($request->isMethod('post')) {
            $request->validate([
                'query' => 'required|string'
            ]);

            $query = trim($request->input('query'));

            // Remove comments and normalize whitespace
            $query = preg_replace('/--.*$/m', '', $query); // Remove single-line comments
            $query = preg_replace('/\/\*.*?\*\//s', '', $query); // Remove multi-line comments
            $query = trim($query);

            try {
                // Check if it's a SELECT query (case-insensitive)
                if (preg_match('/^\s*select\s/i', $query)) {
                    $queryResults = DB::select($query);
                    $isSelect = true;
                    $affectedRows = count($queryResults);

                    // Store query results and metadata in session for pagination
                    session([
                        'query_results_' . $table => $queryResults,
                        'query_sql_' . $table => $query,
                        'query_total_' . $table => $affectedRows
                    ]);
                } else {
                    return redirect()->back()->with('error', 'Only SELECT queries are allowed in table view.');
                }
            } catch (\Exception $e) {
                return redirect()->back()->with('error', 'Query execution failed: ' . $e->getMessage());
            }
        }

        // Check if we have stored query results for this table
        if (session()->has('query_results_' . $table)) {
            $storedResults = session('query_results_' . $table);
            $query = session('query_sql_' . $table);
            $affectedRows = session('query_total_' . $table);
            $isSelect = true;

            // Paginate the stored results
            $perPage = 50;
            $currentPage = $request->input('page', 1);
            $offset = ($currentPage - 1) * $perPage;

            $paginatedResults = array_slice($storedResults, $offset, $perPage);
            $queryResults = new \Illuminate\Pagination\LengthAwarePaginator(
                $paginatedResults,
                $affectedRows,
                $perPage,
                $currentPage,
                ['path' => $request->url(), 'pageName' => 'page']
            );
            $queryResults->appends($request->except('page'));
        }

        // Get table data with pagination and optional search
        $dbQuery = DB::table($table);
        $search = $request->input('search');

        if ($search) {
            $dbQuery->where(function ($q) use ($columns, $search) {
                foreach ($columns as $column) {
                    $q->orWhere($column, 'LIKE', '%' . $search . '%');
                }
            });
        }

        $data = $dbQuery->paginate(50)->appends($request->query());

        // Get total records count (without search filter for display)
        $totalRecords = DB::table($table)->count();

        return view('superadmin.database.show', compact('table', 'columns', 'data', 'totalRecords', 'search', 'queryResults', 'query', 'isSelect', 'affectedRows'));
    }

    /**
     * Show the SQL query interface.
     */
    public function query(Request $request)
    {
        $preFilledQuery = $request->input('query');
        return view('superadmin.database.query', compact('preFilledQuery'));
    }

    /**
     * Execute SQL query.
     */
    public function executeQuery(Request $request)
    {
        $request->validate([
            'query' => 'required|string'
        ]);

        $query = trim($request->input('query'));

        // Remove comments and normalize whitespace
        $query = preg_replace('/--.*$/m', '', $query); // Remove single-line comments
        $query = preg_replace('/\/\*.*?\*\//s', '', $query); // Remove multi-line comments
        $query = trim($query);

        try {
            // Check if it's a SELECT query (case-insensitive)
            if (preg_match('/^\s*select\s/i', $query)) {
                $results = DB::select($query);
                $isSelect = true;
                $affectedRows = count($results);
                $message = "Query executed successfully. Found {$affectedRows} rows.";
            } elseif (preg_match('/^\s*(insert|update|delete|create|alter|drop|truncate|grant|revoke)\s/i', $query)) {
                // For data modification queries
                $affectedRows = DB::statement($query);
                $results = [];
                $isSelect = false;
                $message = "Query executed successfully. Affected rows: {$affectedRows}";
            } elseif (preg_match('/^\s*(show|describe|explain)\s/i', $query)) {
                // For informational queries
                $results = DB::select($query);
                $isSelect = true;
                $affectedRows = count($results);
                $message = "Query executed successfully. Found {$affectedRows} results.";
            } else {
                // Try as a general statement
                $affectedRows = DB::statement($query);
                $results = [];
                $isSelect = false;
                $message = "Query executed successfully. Affected rows: {$affectedRows}";
            }

            return view('superadmin.database.query', compact('query', 'results', 'isSelect', 'affectedRows'))
                ->with('success', $message);

        } catch (\Exception $e) {
            return view('superadmin.database.query', compact('query'))
                ->with('error', 'Query execution failed: ' . $e->getMessage());
        }
    }

    /**
     * Export a specific table as SQL.
     */
    public function exportTable($table)
    {
        if (!Schema::hasTable($table)) {
            return redirect()->route('superadmin.database.index')->with('error', 'Table does not exist.');
        }

        $columns = Schema::getColumnListing($table);
        $data = DB::table($table)->get();

        $sql = $this->generateTableExportSQL($table, $columns, $data);

        $filename = $table . '_' . date('Y-m-d_H-i-s') . '.sql';

        return response($sql)
            ->header('Content-Type', 'application/sql')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Export the entire database as SQL.
     */
    public function exportDatabase()
    {
        $tables = DB::select('SHOW TABLES');
        $tableNames = array_map(function($table) {
            return array_values((array)$table)[0];
        }, $tables);

        $sql = "-- Database Export\n";
        $sql .= "-- Generated on: " . date('Y-m-d H:i:s') . "\n\n";

        $sql .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";

        foreach ($tableNames as $tableName) {
            try {
                $columns = Schema::getColumnListing($tableName);
                $data = DB::table($tableName)->get();

                $sql .= $this->generateTableExportSQL($tableName, $columns, $data);
                $sql .= "\n";
            } catch (\Exception $e) {
                // Skip tables that can't be accessed
                continue;
            }
        }

        $sql .= "SET FOREIGN_KEY_CHECKS = 1;\n";

        $filename = 'database_export_' . date('Y-m-d_H-i-s') . '.sql';

        return response($sql)
            ->header('Content-Type', 'application/sql')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Generate SQL for table export.
     */
    private function generateTableExportSQL($tableName, $columns, $data)
    {
        $sql = "-- Table: {$tableName}\n";
        $sql .= "-- Records: " . $data->count() . "\n\n";

        if ($data->isEmpty()) {
            $sql .= "-- No data in table {$tableName}\n\n";
            return $sql;
        }

        foreach ($data as $row) {
            $values = [];
            foreach ($columns as $column) {
                $value = $row->$column;

                if (is_null($value)) {
                    $values[] = 'NULL';
                } elseif (is_numeric($value)) {
                    $values[] = $value;
                } elseif (is_bool($value)) {
                    $values[] = $value ? '1' : '0';
                } else {
                    // Escape single quotes and wrap in quotes
                    $escaped = str_replace("'", "''", $value);
                    $values[] = "'" . $escaped . "'";
                }
            }

            $sql .= "INSERT INTO `{$tableName}` (`" . implode('`, `', $columns) . "`) VALUES (" . implode(', ', $values) . ");\n";
        }

        $sql .= "\n";
        return $sql;
    }
}
