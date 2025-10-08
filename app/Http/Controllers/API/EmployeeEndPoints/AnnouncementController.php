<?php

namespace App\Http\Controllers\API\EmployeeEndPoints;

use App\Http\Controllers\API\BaseApiController;
use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AnnouncementController extends BaseApiController
{
    /**
     * Get all valid announcements for the authenticated employee
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAnnouncements(Request $request)
    {
        try {
            $employee = Auth::user()->employee;

            $announcements = Announcement::where('company_id', $employee->company_id)
                ->whereIn('audience', ['employees', 'both'])
                ->get();

            return $this->sendResponse([
                'announcements' => $announcements
            ], 'Announcements retrieved successfully');

        } catch (\Exception $e) {
            return $this->sendError('Error retrieving announcements', [$e->getMessage()], 500);
        }
    }

    /**
     * Get a specific announcement details
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAnnouncement($id)
    {
        try {
            $employee = Auth::user()->employee;
            
            $announcement = Announcement::where('company_id', $employee->company_id)
                ->whereIn('audience', ['employees', 'both'])
                ->where('id', $id)
                ->first();

            if (!$announcement) {
                return $this->sendError('Announcement not found', [], 404);
            }

            // Check if announcement is published
            if ($announcement->publish_date && Carbon::parse($announcement->publish_date)->isFuture()) {
                return $this->sendError('Announcement not available yet', [], 403);
            }

            // Check if announcement is expired
            if ($announcement->expires_at && Carbon::parse($announcement->expires_at)->isPast()) {
                return $this->sendError('Announcement has expired', [], 403);
            }

            // Mark as read if we're tracking read status
            // Assuming you have a pivot table for tracking read status
            // $employee->readAnnouncements()->syncWithoutDetaching([$announcement->id => ['read_at' => now()]]);

            return $this->sendResponse([
                'announcement' => $announcement
            ], 'Announcement retrieved successfully');

        } catch (\Exception $e) {
            return $this->sendError('Error retrieving announcement', [$e->getMessage()], 500);
        }
    }

    /**
     * Get unread announcements count
     *
     * @param int $employeeId
     * @return int
     */
    private function getUnreadCount($employeeId)
    {
        // This is a placeholder method. Implement the actual logic based on your read tracking system
        // For example, if you have a pivot table tracking read status:
        /*
        return Announcement::where('company_id', Auth::user()->employee->company_id)
            ->whereIn('audience', ['employees', 'both'])
            ->whereNotIn('id', function($query) use ($employeeId) {
                $query->select('announcement_id')
                    ->from('announcement_reads')
                    ->where('employee_id', $employeeId);
            })
            ->count();
        */
        
        return 0; // Return 0 for now
    }
}
