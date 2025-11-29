<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class LogsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:superadmin');
    }

    /**
     * Display the logs index page.
     */
    public function index()
    {
        $logFiles = $this->getLogFiles();

        return view('superadmin.logs.index', compact('logFiles'));
    }

    /**
     * Show the content of a specific log file.
     */
    public function show($filename)
    {
        $logFiles = $this->getLogFiles();

        if (!in_array($filename, $logFiles)) {
            return redirect()->route('superadmin.logs.index')->with('error', 'Log file not found.');
        }

        $path = storage_path('logs/' . $filename);

        if (!File::exists($path)) {
            return redirect()->route('superadmin.logs.index')->with('error', 'Log file does not exist.');
        }

        $content = File::get($path);
        $lines = explode("\n", $content);
        $lines = array_reverse($lines); // Show latest entries first

        // Paginate lines (show last 1000 lines)
        $perPage = 100;
        $page = request('page', 1);
        $offset = ($page - 1) * $perPage;
        $paginatedLines = array_slice($lines, $offset, $perPage);
        $totalLines = count($lines);

        return view('superadmin.logs.show', compact('filename', 'paginatedLines', 'totalLines', 'perPage', 'page'));
    }

    /**
     * Download a log file.
     */
    public function download($filename)
    {
        $logFiles = $this->getLogFiles();

        if (!in_array($filename, $logFiles)) {
            return redirect()->route('superadmin.logs.index')->with('error', 'Log file not found.');
        }

        $path = storage_path('logs/' . $filename);

        if (!File::exists($path)) {
            return redirect()->route('superadmin.logs.index')->with('error', 'Log file does not exist.');
        }

        return response()->download($path);
    }

    /**
     * Get list of log files.
     */
    private function getLogFiles()
    {
        $logPath = storage_path('logs');
        $files = File::files($logPath);

        $logFiles = [];
        foreach ($files as $file) {
            if ($file->getExtension() === 'log') {
                $logFiles[] = $file->getFilename();
            }
        }

        // Sort by modified time (newest first)
        usort($logFiles, function($a, $b) use ($logPath) {
            return filemtime($logPath . '/' . $b) <=> filemtime($logPath . '/' . $a);
        });

        return $logFiles;
    }
}
