<?php

namespace App\Http\Controllers\API\EmployeeEndPoints;

use App\Http\Controllers\API\BaseApiController;
use App\Models\AcademicHoliday;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AcademicHolidayController extends BaseApiController
{
    /**
     * Get all holidays for the current year
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getHolidays(Request $request)
    {
        try {
            $employee = Auth::user()->employee;
            $query = AcademicHoliday::where('company_id', $employee->company_id);

            // Filter by date range
            // Set year variable first
            $year = $request->year ?? Carbon::now()->year;

            if ($request->has('from_date') && $request->has('to_date')) {
                $query->where(function($q) use ($request) {
                    $q->whereBetween('from_date', [
                        Carbon::parse($request->from_date)->startOfDay(),
                        Carbon::parse($request->to_date)->endOfDay()
                    ])->orWhereBetween('to_date', [
                        Carbon::parse($request->from_date)->startOfDay(),
                        Carbon::parse($request->to_date)->endOfDay()
                    ]);
                });
            } else {
                // Filter by month if provided
                if ($request->has('month')) {
                    $query->whereMonth('from_date', $request->month)
                          ->whereYear('from_date', $year);
                } else {
                    $query->whereYear('from_date', $year);
                }
            }

            // Get upcoming holidays if requested
            if ($request->upcoming) {
                $query->where('from_date', '>=', Carbon::today());
            }

            // Filter by name search
            if ($request->has('search')) {
                $query->where('name', 'like', '%' . $request->search . '%');
            }

            $holidays = $query->orderBy('from_date', 'asc')
                            ->select([
                                'id',
                                'name',
                                'from_date',
                                'to_date',
                                'description'
                            ])
                            ->get()
                            ->map(function($holiday) {
                                $holiday->duration = Carbon::parse($holiday->from_date)->diffInDays(Carbon::parse($holiday->to_date)) + 1;
                                return $holiday;
                            });

            return $this->sendResponse([
                'holidays' => $holidays,
                'total_holidays' => $holidays->count(),
                'total_days' => $holidays->sum('duration'),
                'year' => $year
            ], 'Holidays retrieved successfully');

        } catch (\Exception $e) {
            return $this->sendError('Error retrieving holidays', [$e->getMessage()], 500);
        }
    }

    /**
     * Get holiday calendar for current month
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCalendar(Request $request)
    {
        try {
            $employee = Auth::user()->employee;
            
            // Get month and year from request or use current
            $month = $request->month ?? Carbon::now()->month;
            $year = $request->year ?? Carbon::now()->year;
            
            $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();

            $holidays = AcademicHoliday::where('company_id', $employee->company_id)
                ->where(function($query) use ($startDate, $endDate) {
                    $query->whereBetween('from_date', [$startDate, $endDate])
                          ->orWhereBetween('to_date', [$startDate, $endDate]);
                })
                ->get();

            // Create calendar array
            $calendar = collect();
            $currentDate = $startDate->copy();

            while ($currentDate <= $endDate) {
                $dayHolidays = $holidays->filter(function($holiday) use ($currentDate) {
                    return Carbon::parse($holiday->from_date)->startOfDay() <= $currentDate &&
                           Carbon::parse($holiday->to_date)->endOfDay() >= $currentDate;
                });

                $calendar->push([
                    'date' => $currentDate->format('Y-m-d'),
                    'day' => $currentDate->format('d'),
                    'day_name' => $currentDate->format('l'),
                    'is_holiday' => $dayHolidays->isNotEmpty(),
                    'holidays' => $dayHolidays->map(function($holiday) {
                        return [
                            'id' => $holiday->id,
                            'name' => $holiday->name,
                            'description' => $holiday->description
                        ];
                    })->values()->toArray()
                ]);

                $currentDate->addDay();
            }

            return $this->sendResponse([
                'calendar' => $calendar,
                'month' => $month,
                'year' => $year,
                'total_holidays' => $holidays->count()
            ], 'Holiday calendar retrieved successfully');

        } catch (\Exception $e) {
            return $this->sendError('Error retrieving holiday calendar', [$e->getMessage()], 500);
        }
    }

    /**
     * Get details of a specific holiday
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getHoliday($id)
    {
        try {
            $employee = Auth::user()->employee;
            
            $holiday = AcademicHoliday::where('company_id', $employee->company_id)
                ->where('id', $id)
                ->first();

            if (!$holiday) {
                return $this->sendError('Holiday not found', [], 404);
            }

            return $this->sendResponse([
                'holiday' => $holiday
            ], 'Holiday details retrieved successfully');

        } catch (\Exception $e) {
            return $this->sendError('Error retrieving holiday details', [$e->getMessage()], 500);
        }
    }
}
