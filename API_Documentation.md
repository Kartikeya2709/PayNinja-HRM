# API Documentation for Mobile Application

This document provides comprehensive API documentation for the RocketHR mobile application. All endpoints are prefixed with `/api/v1`.

## Table of Contents

### [1. Authentication](#1-authentication)
- [1.1 Login](#11-login)
- [1.2 Get Authenticated User](#12-get-authenticated-user)
- [1.3 Forget Password](#13-forget-password)

### [2. Profile Management](#2-profile-management)
- [2.1 Get Profile](#21-get-profile)
- [2.2 Get Colleagues](#22-get-colleagues)

### [3. Attendance Management](#3-attendance-management)
- [3.1 Get Attendance Settings and Today Attendance](#31-get-attendance-settings-and-today-attendance)
- [3.2 Get Attendance History](#32-get-attendance-history)
- [3.3 Check In](#33-check-in)
- [3.4 Check Out](#34-check-out)
- [3.5 Get Regularization Requests](#35-get-regularization-requests)
- [3.6 Create Regularization Request](#36-create-regularization-request)

### [4. Leave Management](#4-leave-management)
- [4.1 Get Leave Types](#41-get-leave-types)
- [4.2 Get Leave Balance](#42-get-leave-balance)
- [4.3 Apply for Leave](#43-apply-for-leave)
- [4.4 Get Leave Requests](#44-get-leave-requests)
- [4.5 Cancel Leave](#45-cancel-leave)

### [5. Announcements](#5-announcements)
- [5.1 Get Announcements](#51-get-announcements)
- [5.2 Get Announcement Details](#52-get-announcement-details)

### [6. Holidays](#6-holidays)
- [6.1 Get Holidays](#61-get-holidays)
- [6.2 Get Holiday Calendar](#62-get-holiday-calendar)
- [6.3 Get Holiday Details](#63-get-holiday-details)

### [7. Salary & Payroll](#7-salary--payroll)
- [7.1 Get Current Salary](#71-get-current-salary)
- [7.2 Get Payroll Records](#72-get-payroll-records)
- [7.4 Download Payslip](#74-download-payslip)

### [8. Reimbursements](#8-reimbursements)
- [8.1 Get Reimbursements](#81-get-reimbursements)
- [8.2 Get Reimbursement Details](#82-get-reimbursement-details)
- [8.3 Create Reimbursement](#83-create-reimbursement)
- [8.4 Update Reimbursement](#84-update-reimbursement)
- [8.5 Cancel Reimbursement](#85-cancel-reimbursement)

### [9. Field Visits](#9-field-visits)
- [9.1 Get Field Visits](#91-get-field-visits)
- [9.2 Get Field Visit Details](#92-get-field-visit-details)
- [9.3 Get Upcoming Field Visits](#93-get-upcoming-field-visits)
- [9.4 Get Field Visit Stats](#94-get-field-visit-stats)
- [9.5 Create Field Visit](#95-create-field-visit)
- [9.6 Start Field Visit](#96-start-field-visit)
- [9.7 Complete Field Visit](#97-complete-field-visit)

### [10. Resignations](#10-resignations)
- [10.1 Get Resignations](#101-get-resignations)
- [10.2 Get Resignation Details](#102-get-resignation-details)
- [10.3 Create Resignation](#103-create-resignation)
- [10.4 Update Resignation](#104-update-resignation)
- [10.5 Withdraw Resignation](#105-withdraw-resignation)
- [10.6 Get Resignation Types](#106-get-resignation-types)

### [Mobile-Specific Considerations](#mobile-specific-considerations)
- [Pagination](#pagination)
- [Offline Handling](#offline-handling)
- [Location Services](#location-services)
- [File Uploads](#file-uploads)
- [Real-time Updates](#real-time-updates)
- [Data Synchronization](#data-synchronization)
- [Performance Optimization](#performance-optimization)
- [Security Considerations](#security-considerations)
- [Error Handling](#error-handling)

---

## Base URL
```
for live: https://dashboard.rockethr.org/api/v1
for staging: https://testing.rockethr.org/api/v1
```

## Authentication
- **Type**: Bearer Token (Sanctum)
- **Header**: `Authorization: Bearer {token}`
- All endpoints except login require authentication
- Token obtained from login endpoint

## Response Formats

### Standard Response (BaseApiController)
```json
{
  "success": true,
  "data": {},
  "message": "Success message"
}
```

### Error Response
```json
{
  "success": false,
  "message": "Error message",
  "errors": ["Error details"]
}
```

### Direct Response (Some Controllers)
```json
{
  "status": true,
  "message": "Success message",
  "data": {}
}
```

## Common Error Codes
- `401`: Unauthorized
- `404`: Not Found
- `422`: Validation Error
- `500`: Internal Server Error

---

## 1. Authentication

### 1.1 Login
**Endpoint**: `POST /login`  
**Description**: Authenticate user and return access token  
**Authentication**: None required

**Request Body**:
```json
{
  "email": "string (required) - User email",
  "password": "string (required, min:6) - User password"
}
```

**Response Schema**:
```json
{
  "status": true,
  "message": "Login successful",
  "user": {
    "id": "integer",
    "email": "string",
    "role": "string"
  },
  "token": "string - Bearer token"
}
```

**Error Codes**: `401`, `422`  
**Example**:
```bash
curl -X POST /api/v1/login -H "Content-Type: application/json" -d '{"email":"user@example.com","password":"password123"}'
```

### 1.2 Get Authenticated User
**Endpoint**: `GET /user`  
**Description**: Get current authenticated user information  
**Authentication**: Required (Bearer token)

**Response Schema**:
```json
{
  "status": true,
  "user": {
    "id": "integer",
    "email": "string",
    "role": "string",
    "employee": {
      "id": "integer",
      "first_name": "string",
      "last_name": "string",
      "department": "string",
      "designation": "string"
    }
  }
}
```

### 1.3 Forget Password (Send Reset Link Mail)
**Endpoint**: `Post /forget-password`
**Description**: Send Password Reset Link via mail
**Authentication**: Required (Bearer token)

**Request Body**:
```json
{
  "email": "string (required) - User email",
}
```
**Response Schema**:
```json
{
    "status": true,
    "message": "We have emailed your password reset link."
}
```

---

## 2. Profile Management

### 2.1 Get Profile
**Endpoint**: `GET /profile`  
**Description**: Retrieve authenticated employee's profile information  
**Authentication**: Required

**Response Schema**:
```json
{
  "success": true,
  "data": {
    "personal_info": {
      "id": "integer",
      "name": "string",
      "email": "string",
      "phone": "string",
      "date_of_birth": "string",
      "gender": "string"
    },
    "employment_info": {
      "company": "string",
      "employee_id": "integer",
      "department": "string",
      "designation": "string",
      "joining_date": "string",
      "employment_type": "string"
    },
    "reporting_info": {
      "reporting_manager": {
        "id": "integer",
        "name": "string"
      }
    }
  },
  "message": "Profile retrieved successfully"
}
```

### 2.2 Get Colleagues
**Endpoint**: `GET /profile/colleagues`  
**Description**: Get list of colleagues from same company with filtering and pagination  
**Authentication**: Required

**Query Parameters**:
- `search`: string (optional) - Search by name, employee code, email
- `department_id`: integer (optional) - Filter by department
- `designation_id`: integer (optional) - Filter by designation
- `sort_by`: string (optional) - name, employee_code, joining_date
- `sort_order`: string (optional) - asc, desc
- `per_page`: integer (optional, 1-100) - Items per page

**Response Schema**:
```json
{
  "success": true,
  "data": {
    "company": {
      "id": "integer",
      "name": "string"
    },
    "total_colleagues": "integer",
    "colleagues": "array - Paginated colleague list",
    "pagination": {
      "current_page": "integer",
      "per_page": "integer",
      "total_pages": "integer",
      "total_records": "integer"
    }
  },
  "message": "Colleagues retrieved successfully"
}
```

---

## 3. Attendance Management

### 3.1 Get Attendance Settings and Today Attendance
**Endpoint**: `GET /attendance/check-in-out`
**Description**: Get attendance settings and today's attendance data for mobile app
**Authentication**: Required

**Response Schema**:
```json
{
  "success": true,
  "data": {
    "todayAttendance": {
      "id": "integer",
      "date": "string",
      "check_in": "string (HH:mm:ss)",
      "check_out": "string (HH:mm:ss)",
      "status": "string",
      "check_in_status": "string",
      "check_in_location": "string",
      "check_out_location": "string",
      "check_in_latitude": "number",
      "check_in_longitude": "number",
      "check_out_latitude": "number",
      "check_out_longitude": "number",
      "remarks": "string",
      "check_in_remarks": "string"
    },
    "settings": {
      "class": "string",
      "properties": {
        "office_start_time": "string (HH:mm:ss)",
        "office_end_time": "string (HH:mm:ss)",
        "grace_period": "string (HH:mm:ss)",
        "auto_absent_time": "string (HH:mm:ss)",
        "work_hours": "integer",
        "enable_geolocation": "boolean",
        "office_latitude": "number",
        "office_longitude": "number",
        "geofence_radius": "integer",
        "weekend_days": "array",
        "allow_multiple_check_in": "boolean",
        "track_location": "boolean"
      }
    },
    "isWeekend": "boolean",
    "today": "string (Y-m-d)",
    "isExemptFromGeolocation": "boolean"
  },
  "message": "Attendance settings and today's attendance retrieved successfully"
}
```

**Notes**:
- `todayAttendance` will be `null` if employee hasn't checked in today
- `isWeekend` indicates if current date is a weekend according to company settings
- `isExemptFromGeolocation` indicates if employee is exempted from geolocation requirements
- `enable_geolocation` setting determines if geolocation tracking is active for check-in/out

### 3.2 Get Attendance History
**Endpoint**: `GET /attendance/history`
**Description**: Get employee's attendance history with optional date filtering
**Authentication**: Required

**Query Parameters**:
- `from_date`: date (optional)
- `to_date`: date (optional)
- `month`: integer (optional, 1-12)
- `year`: integer (optional)

**Response Schema**:
```json
{
  "success": true,
  "data": {
    "attendances": "array - Attendance records",
    "summary": {
      "present": "integer",
      "absent": "integer",
      "late": "integer",
      "half_day": "integer",
      "leave": "integer"
    }
  },
  "message": "Attendance history retrieved successfully"
}
```

### 3.3 Check In
**Endpoint**: `POST /attendance/check-in`
**Description**: Record employee check-in with location
**Authentication**: Required

**Request Body**:
```json
{
  "check_in_location": "string (required) - Latitude,Longitude",
  "device_info": "string (optional)"
}
```

**Response Schema**:
```json
{
  "success": true,
  "data": {
    "attendance": "object - Attendance record"
  },
  "message": "Checked in successfully"
}
```

**Error Codes**: `400`, `422`

### 3.4 Check Out
**Endpoint**: `POST /attendance/check-out`
**Description**: Record employee check-out with location
**Authentication**: Required

**Request Body**:
```json
{
  "check_out_location": "string (required) - Latitude,Longitude",
  "device_info": "string (optional)"
}
```

**Response Schema**:
```json
{
  "success": true,
  "data": {
    "attendance": "object - Attendance record"
  },
  "message": "Checked out successfully"
}
```

**Error Codes**: `400`

### 3.5 Get Regularization Requests
**Endpoint**: `GET /attendance/regularization`
**Description**: Get employee's attendance regularization requests
**Authentication**: Required

**Query Parameters**:
- `status`: string (optional) - pending, approved, rejected
- `from_date`: date (optional)
- `to_date`: date (optional)
- `month`: integer (optional)
- `year`: integer (optional)
- `per_page`: integer (optional)

**Response Schema**:
```json
{
  "success": true,
  "data": {
    "summary": {
      "total": "integer",
      "pending": "integer",
      "approved": "integer",
      "rejected": "integer"
    },
    "regularizations": {
      "current_page": "integer",
      "requests": "array - Regularization requests"
    }
  },
  "message": "Regularization requests retrieved successfully"
}
```

### 3.6 Create Regularization Request
**Endpoint**: `POST /attendance/regularization`
**Description**: Submit attendance regularization request
**Authentication**: Required

**Request Body**:
```json
{
  "date": "date (required) - Date to regularize",
  "check_in": "string (optional) - HH:MM format",
  "check_out": "string (optional) - HH:MM format",
  "reason": "string (required)"
}
```

**Response Schema**:
```json
{
  "success": true,
  "data": {
    "regularization": "object - Created request"
  },
  "message": "Regularization request submitted successfully"
}
```

---

## 4. Leave Management

### 4.1 Get Leave Types
**Endpoint**: `GET /leave/types`  
**Description**: Get available leave types with balance information  
**Authentication**: Required

**Response Schema**:
```json
{
  "success": true,
  "data": {
    "leave_types": [
      {
        "id": "integer",
        "name": "string",
        "default_days": "integer",
        "description": "string",
        "balance": {
          "total_days": "integer",
          "used_days": "integer",
          "remaining_days": "integer"
        }
      }
    ],
    "summary": {
      "total_leave_types": "integer",
      "total_days_allocated": "integer",
      "total_days_used": "integer",
      "total_days_remaining": "integer"
    }
  },
  "message": "Leave types retrieved successfully"
}
```

### 4.2 Get Leave Balance
**Endpoint**: `GET /leave/balance`  
**Description**: Get employee's current leave balance  
**Authentication**: Required

**Response Schema**:
```json
{
  "success": true,
  "data": {
    "leave_balance": "array - Leave balances",
    "summary": {
      "total_leave_days": "integer",
      "total_used_days": "integer",
      "total_remaining_days": "integer"
    }
  },
  "message": "Leave balance retrieved successfully"
}
```

### 4.3 Apply for Leave
**Endpoint**: `POST /leave/apply`  
**Description**: Submit leave application  
**Authentication**: Required

**Request Body**:
```json
{
  "leave_type_id": "integer (required)",
  "start_date": "date (required) - After today",
  "end_date": "date (required) - After start_date",
  "reason": "string (required)"
}
```

**Response Schema**:
```json
{
  "success": true,
  "data": "object - Leave request",
  "message": "Leave application submitted successfully"
}
```

**Error Codes**: `400`

### 4.4 Get Leave Requests
**Endpoint**: `GET /leave/requests`  
**Description**: Get employee's leave requests with filtering  
**Authentication**: Required

**Query Parameters**:
- `year`: integer (optional)
- `month`: integer (optional)
- `status`: string (optional) - pending, approved, rejected, cancelled
- `leave_type_id`: integer (optional)
- `from_date`: date (optional)
- `to_date`: date (optional)
- `search`: string (optional)
- `sort_by`: string (optional)
- `sort_order`: string (optional)
- `per_page`: integer (optional)

**Response Schema**:
```json
{
  "success": true,
  "data": {
    "leaves": "array - Leave requests",
    "pagination": "object - Pagination info",
    "summary": {
      "total_requests": "integer",
      "pending_requests": "integer",
      "approved_requests": "integer",
      "rejected_requests": "integer",
      "cancelled_requests": "integer"
    }
  },
  "message": "Leave requests retrieved successfully"
}
```

### 4.5 Cancel Leave
**Endpoint**: `POST /leave/{id}/cancel`  
**Description**: Cancel pending leave request  
**Authentication**: Required

**Response Schema**:
```json
{
  "success": true,
  "data": {
    "leave": "object - Updated leave"
  },
  "message": "Leave application cancelled successfully"
}
```

**Error Codes**: `400`

---

## 5. Announcements

### 5.1 Get Announcements
**Endpoint**: `GET /announcements`  
**Description**: Get company announcements for employees  
**Authentication**: Required

**Response Schema**:
```json
{
  "success": true,
  "data": {
    "announcements": "array - Announcement list"
  },
  "message": "Announcements retrieved successfully"
}
```

### 5.2 Get Announcement Details
**Endpoint**: `GET /announcements/{id}`  
**Description**: Get specific announcement details  
**Authentication**: Required

**Response Schema**:
```json
{
  "success": true,
  "data": {
    "announcement": "object - Announcement details"
  },
  "message": "Announcement retrieved successfully"
}
```

**Error Codes**: `404`, `403`

---

## 6. Holidays

### 6.1 Get Holidays
**Endpoint**: `GET /holidays`  
**Description**: Get company holidays with filtering  
**Authentication**: Required

**Query Parameters**:
- `year`: integer (optional)
- `month`: integer (optional)
- `from_date`: date (optional)
- `to_date`: date (optional)
- `upcoming`: boolean (optional)
- `search`: string (optional)

**Response Schema**:
```json
{
  "success": true,
  "data": {
    "holidays": "array - Holiday list",
    "total_holidays": "integer",
    "total_days": "integer",
    "year": "integer"
  },
  "message": "Holidays retrieved successfully"
}
```

### 6.2 Get Holiday Calendar
**Endpoint**: `GET /holidays/calendar`  
**Description**: Get holiday calendar for specific month  
**Authentication**: Required

**Query Parameters**:
- `month`: integer (optional)
- `year`: integer (optional)

**Response Schema**:
```json
{
  "success": true,
  "data": {
    "calendar": "array - Daily calendar with holiday info",
    "month": "integer",
    "year": "integer",
    "total_holidays": "integer"
  },
  "message": "Holiday calendar retrieved successfully"
}
```

### 6.3 Get Holiday Details
**Endpoint**: `GET /holidays/{id}`  
**Description**: Get specific holiday details  
**Authentication**: Required

**Response Schema**:
```json
{
  "success": true,
  "data": {
    "holiday": "object - Holiday details"
  },
  "message": "Holiday details retrieved successfully"
}
```

---

## 7. Salary & Payroll

### 7.1 Get Current Salary
**Endpoint**: `GET /salary/current`  
**Description**: Get employee's current salary details  
**Authentication**: Required

**Response Schema**:
```json
{
  "employee_id": "integer",
  "employee_name": "string",
  "salary": {
    "id": "integer",
    "basic_salary": "number",
    "ctc": "number",
    "notes": "string"
  }
}
```

### 7.2 Get Payroll Records
**Endpoint**: `GET /salary/payroll-records`  
**Description**: Get employee's payroll records  
**Authentication**: Required

**Query Parameters**:
- `year`: integer (optional)
- `month`: integer (optional)

**Response Schema**:
```json
{
  "payroll_records": [
    {
      "id": "integer",
      "pay_period_start": "date",
      "pay_period_end": "date",
      "payment_date": "date",
      "gross_salary": "number",
      "net_salary": "number",
      "total_deductions": "number",
      "status": "string",
      "notes": "string"
    }
  ]
}
```


**Response Schema**:
```json
{
  "payroll_record": {
    "id": "integer",
    "pay_period_start": "date",
    "pay_period_end": "date",
    "payment_date": "date",
    "basic_salary": "number",
    "gross_salary": "number",
    "net_salary": "number",
    "status": "string",
    "present_days": "integer",
    "leave_days": "integer",
    "overtime_hours": "number",
    "overtime_amount": "number",
    "incentives": "number",
    "bonus": "number",
    "advance_salary": "number",
    "total_deductions": "number",
    "total_earnings": "number",
    "notes": "string"
  }
}
```

### 7.3 Download Payslip
**Endpoint**: `GET /salary/payslip/{id}`  
**Description**: Download payslip PDF  
**Authentication**: Required

**Response**: PDF file download

---

## 8. Reimbursements

### 8.1 Get Reimbursements
**Endpoint**: `GET /reimbursements`  
**Description**: Get employee's reimbursement requests with filtering  
**Authentication**: Required

**Query Parameters**:
- `status`: string (optional)
- `year`: integer (optional)
- `month`: integer (optional)
- `from_date`: date (optional)
- `to_date`: date (optional)
- `min_amount`: number (optional)
- `max_amount`: number (optional)
- `search`: string (optional)
- `sort_by`: string (optional)
- `sort_order`: string (optional)
- `per_page`: integer (optional)

**Response Schema**:
```json
{
  "reimbursements": "array - Paginated list",
  "pagination": "object - Pagination info",
  "summary": {
    "total_requests": "integer",
    "total_amount": "number",
    "status_summary": "object"
  }
}
```

### 8.2 Get Reimbursement Details
**Endpoint**: `GET /reimbursements/{id}`  
**Description**: Get specific reimbursement details  
**Authentication**: Required

**Response Schema**:
```json
{
  "reimbursement": {
    "id": "integer",
    "title": "string",
    "description": "string",
    "amount": "number",
    "expense_date": "date",
    "receipt_path": "string",
    "status": "string",
    "created_at": "datetime",
    "updated_at": "datetime"
  }
}
```

### 8.3 Create Reimbursement
**Endpoint**: `POST /reimbursements`  
**Description**: Create new reimbursement request  
**Authentication**: Required

**Request Body**:
```json
{
  "title": "string (required)",
  "description": "string (required)",
  "amount": "number (required)",
  "expense_date": "date (required)",
  "receipt": "file (optional) - JPEG, PNG, PDF, max 5MB"
}
```

**Response Schema**:
```json
{
  "message": "Reimbursement request created successfully",
  "reimbursement": "object - Created reimbursement"
}
```

### 8.4 Update Reimbursement
**Endpoint**: `POST /reimbursements/{id}`  
**Description**: Update pending reimbursement request  
**Authentication**: Required

**Request Body**: Same as create, all optional

**Response Schema**:
```json
{
  "message": "Reimbursement request updated successfully",
  "reimbursement": "object - Updated reimbursement"
}
```

### 8.5 Cancel Reimbursement
**Endpoint**: `DELETE /reimbursements/{id}`  
**Description**: Cancel pending reimbursement request  
**Authentication**: Required

**Response Schema**:
```json
{
  "message": "Reimbursement request cancelled successfully"
}
```

---

## 9. Field Visits

### 9.1 Get Field Visits
**Endpoint**: `GET /field-visits`  
**Description**: Get employee's field visits with filtering  
**Authentication**: Required

**Query Parameters**:
- `status`: string (optional)
- `approval_status`: string (optional)
- `start_date`: date (optional)
- `end_date`: date (optional)
- `search`: string (optional)
- `location`: string (optional)
- `sort_by`: string (optional)
- `sort_order`: string (optional)
- `per_page`: integer (optional)
- `upcoming_only`: boolean (optional)

**Response Schema**:
```json
{
  "field_visits": "array - Paginated list",
  "pagination": "object - Pagination info",
  "summary": {
    "total_visits": "integer",
    "status_summary": "object",
    "approval_summary": "object"
  }
}
```

### 9.2 Get Field Visit Details
**Endpoint**: `GET /field-visits/{id}`  
**Description**: Get specific field visit details  
**Authentication**: Required

**Response Schema**:
```json
{
  "field_visit": "object - Field visit details"
}
```

### 9.3 Get Upcoming Field Visits
**Endpoint**: `GET /field-visits/upcoming/list`  
**Description**: Get list of upcoming field visits  
**Authentication**: Required

**Response Schema**:
```json
{
  "upcoming_field_visits": "array - Upcoming visits"
}
```

### 9.4 Get Field Visit Stats
**Endpoint**: `GET /field-visits/stats/summary`  
**Description**: Get field visit statistics  
**Authentication**: Required

**Response Schema**:
```json
{
  "field_visit_stats": {
    "total_visits": "integer",
    "completed_visits": "integer",
    "pending_visits": "integer",
    "in_progress_visits": "integer",
    "approved_visits": "integer",
    "pending_approval_visits": "integer"
  }
}
```

### 9.5 Create Field Visit
**Endpoint**: `POST /field-visits`  
**Description**: Create new field visit request  
**Authentication**: Required

**Request Body**:
```json
{
  "visit_title": "string (required)",
  "visit_description": "string (optional)",
  "location_name": "string (required)",
  "location_address": "string (required)",
  "scheduled_start_datetime": "datetime (required)",
  "scheduled_end_datetime": "datetime (required)"
}
```

**Response Schema**:
```json
{
  "message": "Field visit created successfully",
  "field_visit": "object - Created visit"
}
```

### 9.6 Start Field Visit
**Endpoint**: `POST /field-visits/{id}/start`  
**Description**: Mark field visit as started  
**Authentication**: Required

**Response Schema**:
```json
{
  "message": "Field visit started successfully",
  "field_visit": "object - Updated visit"
}
```

### 9.7 Complete Field Visit
**Endpoint**: `POST /field-visits/{id}/complete`  
**Description**: Mark field visit as completed  
**Authentication**: Required

**Request Body**:
```json
{
  "visit_notes": "string (optional)",
  "manager_feedback": "string (optional)",
  "latitude": "number (optional)",
  "longitude": "number (optional)"
}
```

**Response Schema**:
```json
{
  "message": "Field visit completed successfully",
  "field_visit": "object - Updated visit"
}
```

---

## 10. Resignations

### 10.1 Get Resignations
**Endpoint**: `GET /resignations`  
**Description**: Get employee's resignation requests  
**Authentication**: Required

**Query Parameters**:
- `status`: string (optional)
- `resignation_type`: string (optional)
- `from_date`: date (optional)
- `to_date`: date (optional)
- `search`: string (optional)
- `exit_process`: string (optional)
- `sort_by`: string (optional)
- `sort_order`: string (optional)
- `per_page`: integer (optional)
- `active_only`: boolean (optional)

**Response Schema**:
```json
{
  "resignations": "array - Paginated list",
  "pagination": "object - Pagination info",
  "summary": {
    "total_resignations": "integer",
    "status_summary": "object",
    "exit_process_summary": "object"
  }
}
```

### 10.2 Get Resignation Details
**Endpoint**: `GET /resignations/{id}`  
**Description**: Get specific resignation details  
**Authentication**: Required

**Response Schema**:
```json
{
  "resignation": "object - Resignation details"
}
```

### 10.3 Create Resignation
**Endpoint**: `POST /resignations/create`  
**Description**: Create new resignation request  
**Authentication**: Required

**Request Body**:
```json
{
  "resignation_type": "string (required) - voluntary, involuntary, retirement, contract_end",
  "reason": "string (required)",
  "resignation_date": "date (required)",
  "last_working_date": "date (required)",
  "notice_period_days": "integer (optional)",
  "employee_remarks": "string (optional)",
  "attachment": "file (optional)"
}
```

**Response Schema**:
```json
{
  "message": "Resignation request submitted successfully",
  "resignation": "object - Created resignation"
}
```

### 10.4 Update Resignation
**Endpoint**: `POST /resignations/{id}`  
**Description**: Update pending resignation request  
**Authentication**: Required

**Request Body**: Same as create, all optional

**Response Schema**:
```json
{
  "message": "Resignation request updated successfully",
  "resignation": "object - Updated resignation"
}
```

### 10.5 Withdraw Resignation
**Endpoint**: `POST /resignations/{id}/withdraw`  
**Description**: Withdraw resignation request  
**Authentication**: Required

**Response Schema**:
```json
{
  "message": "Resignation request withdrawn successfully"
}
```

### 10.6 Get Resignation Types
**Endpoint**: `GET /resignations/types/list`  
**Description**: Get available resignation types  
**Authentication**: Required

**Response Schema**:
```json
{
  "resignation_types": {
    "voluntary": "Voluntary Resignation",
    "involuntary": "Involuntary Termination",
    "retirement": "Retirement",
    "contract_end": "Contract End"
  }
}
```

---

## Mobile-Specific Considerations

### Pagination
Many list endpoints support pagination:
- Use `per_page` parameter (default 10, max 100)
- Response includes pagination metadata
- Implement infinite scroll or "load more" in mobile app

### Offline Handling
- **Caching Strategy**: Cache frequently accessed data (profile, holidays, leave balance)
- **Sync Endpoints**: No dedicated sync endpoints found - implement periodic full refresh
- **Offline Queue**: Queue requests (check-in/out, leave applications) for sync when online
- **Conflict Resolution**: Handle server conflicts when syncing offline data

### Location Services
- **Check-in/Out**: Send GPS coordinates as "latitude,longitude" string
- **Field Visits**: Include location data for visit tracking
- **Permissions**: Request location permissions for attendance features

### File Uploads
- **Supported Formats**: JPEG, JPG, PNG, PDF for receipts and attachments
- **Size Limits**: 5MB max for most uploads
- **Storage**: Files stored on server, paths returned in responses

### Real-time Updates
- **Polling**: Implement periodic polling for status updates (approvals, notifications)
- **Push Notifications**: Consider implementing push notifications for important updates
- **WebSocket**: For real-time attendance status, approval notifications

### Data Synchronization
- **Last Modified**: Track last sync timestamp for incremental updates
- **Version Control**: Handle API response format changes
- **Error Recovery**: Implement retry logic for failed requests

### Performance Optimization
- **Image Compression**: Compress images before upload
- **Batch Requests**: Group multiple requests where possible
- **Response Compression**: Ensure server supports gzip compression
- **Caching Headers**: Respect cache headers for static data

### Security Considerations
- **Token Storage**: Securely store auth tokens (Keychain/iOS, Keystore/Android)
- **Certificate Pinning**: Implement SSL certificate pinning
- **Biometric Auth**: Support biometric authentication for login
- **Session Management**: Handle token expiration and refresh

### Error Handling
- **Network Errors**: Implement retry with exponential backoff
- **Validation Errors**: Display field-specific error messages
- **Auth Errors**: Handle token expiration with re-authentication flow
- **Offline Mode**: Graceful degradation when network unavailable
