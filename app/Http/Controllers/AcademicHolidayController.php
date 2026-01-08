<?php

namespace App\Http\Controllers;

use App\Models\AcademicHoliday;
use App\Models\Company;
use App\Imports\AcademicHolidayImport;
use App\Exports\AcademicHolidayTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Carbon;

class AcademicHolidayController extends Controller
{
    private function getAcademicHolidayFromEncryptedId(string $encryptedId): AcademicHoliday
    {
        try {
            $id = Crypt::decrypt($encryptedId);
            $user = Auth::user();
            $companyId = $user->company_id;
            return AcademicHoliday::where('company_id', $companyId)->findOrFail($id);
        } catch (\Exception $e) {
            abort(404);
        }
    }

    private function encryptAcademicHolidayId(int $id): string
    {
        return Crypt::encrypt($id);
    }

    public function index()
    {
        $user = Auth::user();
        $companyId = $user->company_id;
        $holidays = AcademicHoliday::where('company_id', $companyId)
            ->orderBy('from_date')
            ->get()
            ->map(function ($holiday) {
                $holiday->encrypted_id = $this->encryptAcademicHolidayId($holiday->id);
                return $holiday;
            });

        $isReadOnly = $user; // can’t add/edit/delete
        return view('company.academic-holidays.index', compact('holidays', 'isReadOnly'));
    }

    public function EmployeeIndex()
    {
        $companyHolidays = AcademicHoliday::where('company_id', Auth::user()->company_id)
            ->orderBy('from_date')
            ->get();

        return view('company.academic-holidays.EmployeeIndex', compact('companyHolidays'));
    }

    public function create(Request $request)
    {
        return view('company.academic-holidays.create');
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $companyId = $user->company_id;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
            'description' => 'nullable|string'
        ]);

        AcademicHoliday::create([
            'company_id' => $companyId,
            'name' => $validated['name'],
            'from_date' => $validated['from_date'],
            'to_date' => $validated['to_date'],
            'description' => $validated['description'],
            'created_by' => Auth::id()
        ]);

        return redirect()->route('academic-holidays.index')->with('success', 'Holiday created successfully');
    }

    public function edit(Request $request, $encryptedId)
    {
        $holiday = $this->getAcademicHolidayFromEncryptedId($encryptedId);
        $holiday->encrypted_id = $encryptedId;

        // Explicitly cast from_date and to_date to Carbon instances
        if ($holiday->from_date && !($holiday->from_date instanceof Carbon)) {
            $holiday->from_date = Carbon::parse($holiday->from_date);
        }
        if ($holiday->to_date && !($holiday->to_date instanceof Carbon)) {
            $holiday->to_date = Carbon::parse($holiday->to_date);
        }

        return view('company.academic-holidays.create', compact('holiday'));
    }

    public function update(Request $request, $encryptedId)
    {
        $holiday = $this->getAcademicHolidayFromEncryptedId($encryptedId);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
            'description' => 'nullable|string'
        ]);

        $holiday->update($validated);

        return redirect()->route('academic-holidays.index')->with('success', 'Holiday updated successfully');
    }

    public function destroy(Request $request, $encryptedId)
    {
        $holiday = $this->getAcademicHolidayFromEncryptedId($encryptedId);
        $holiday->delete();

        return redirect()->route('academic-holidays.index')->with('success', 'Holiday deleted successfully');
    }

    public function import(Request $request)
    {
        $user = Auth::user();
        $companyId = $user->company_id;
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        try {
            // Initialize counters and import instance
            $imported = 0;
            $skipped = 0;
            $errors = [];
            $import = new AcademicHolidayImport($companyId);

            try {
                Excel::import($import, $request->file('file'));
                $imported = $import->getRowCount();
            } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
                foreach ($e->failures() as $failure) {
                    $rowNumber = $failure->row();
                    $errors[] = "Row {$rowNumber}: " . implode('; ', array_map(
                        fn($field, $messages) => "$field - " . implode(', ', (array)$messages),
                        $failure->attribute(),
                        $failure->errors()
                    ));
                }
            } catch (\Exception $e) {
                $errors[] = "Error: " . $e->getMessage();
            }

            // Prepare response message
            $message = [];
            if ($imported > 0) {
                $message[] = "{$imported} holidays imported successfully.";
            }
            if ($skipped > 0) {
                $message[] = "{$skipped} holidays skipped.";
            }

            if (count($errors) > 0) {
                // If there were some successes but also errors
                if ($imported > 0) {
                    return redirect()->route('academic-holidays.index', $companyId)
                        ->with('warning', implode(' ', $message) . ' Some rows had errors: ' . implode('; ', $errors));
                }
                // If there were only errors
                return redirect()->back()
                    ->with('error', 'Import failed: ' . implode('; ', $errors))
                    ->withInput();
            }

            // If everything was successful
            return redirect()->route('academic-holidays.index')->with('success', implode(' ', $message));

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error importing holidays: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function downloadTemplate()
    {
        if (!Auth::user()->hasRole(['admin', 'company_admin'])) {
            abort(403, 'Unauthorized action.');
        }
        return Excel::download(new AcademicHolidayTemplate(), 'academic_holidays_template.xlsx');
    }
}
