<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use Carbon\Carbon;

class AdminAttendanceExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithTitle
{
    protected $attendances;
    protected $filters;

    public function __construct($attendances, $filters = [])
    {
        $this->attendances = $attendances;
        $this->filters = $filters;
    }

    public function collection()
    {
        return $this->attendances;
    }

    public function headings(): array
    {
        return [
            'Employee Code',
            'Employee Name',
            'Department',
            'Designation',
            'Date',
            'Day',
            'Check In',
            'Check Out',
            'Status',
            'Working Hours',
            'Remarks'
        ];
    }

    public function map($attendance): array
    {
        return [
            $attendance->employee->employee_code ?? 'N/A',
            $attendance->employee->user->name ?? 'N/A',
            $attendance->employee->department->name ?? 'N/A',
            $attendance->employee->designation->title ?? 'N/A',
            $attendance->date->format('d-m-Y'),
            $attendance->date->format('l'),
            $attendance->check_in ? Carbon::parse($attendance->check_in)->format('h:i A') : 'N/A',
            $attendance->check_out ? Carbon::parse($attendance->check_out)->format('h:i A') : 'N/A',
            $attendance->status,
            $this->calculateWorkingHours($attendance->check_in, $attendance->check_out),
            $attendance->remarks ?? 'N/A'
        ];
    }

    public function title(): string
    {
        return 'Attendance' . ($this->filters ? ' - ' . implode(' ', $this->filters) : '');
    }

    private function calculateWorkingHours($checkIn, $checkOut)
    {
        if (!$checkIn || !$checkOut) {
            return 'N/A';
        }

        $start = Carbon::parse($checkIn);
        $end = Carbon::parse($checkOut);
        $hours = $end->diffInMinutes($start) / 60;
        
        return number_format($hours, 2) . ' hrs';
    }
}
