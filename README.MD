# RocketHR-HRM: Comprehensive Human Resource Management System

## Overview
RocketHR is a full-featured Human Resource Management system built on Laravel 12 and integrated with the Stisla Bootstrap 5 template. It provides a robust platform for managing employee data, attendance, payroll, leave requests, reimbursements, and more, designed to streamline HR operations for companies of all sizes.

## Key Features
- Built on Laravel 12 with PHP 8.2+
- Responsive UI using Stisla Bootstrap 5 template
- User authentication and role-based access control (Super Admin, Company Admin, Admin, Employee)
- Employee management: CRUD operations, profile management, employee ID prefixing
- Attendance tracking with check-in/out, geolocation, and attendance corrections
- Payroll management including salary configuration, payroll runs, payslip generation, and PDF downloads
- Leave management: leave types, leave requests, leave balances, approvals, and calendar views
- Reimbursement management with approval workflows
- Shift, department, designation, and team management
- Academic holiday management with import/export templates
- Export reports in PDF and Excel formats using DomPDF and Maatwebsite Excel
- Multi-company support with company-specific data segregation
- Pre-configured dashboard with key HR metrics and reports

## Installation Guide

### Prerequisites
- PHP >= 8.2
- Composer
- MySQL ( <= 8.0.2) or compatible database


1. Install PHP dependencies:
    ```bash
    composer install
    ```

2. Copy the `.env.example` file and rename it to `.env`:
    ```bash
    cp .env.example .env
    ```

3. Generate application key:
    ```bash
    php artisan key:generate
    ```

4. Link the storage directory:
    ```bash
    php artisan storage:link
    ```

5. Configure your database settings in the `.env` file.

6. Run database migrations:
    ```bash
    php artisan migrate --seed
    ```

7. Start the development server:
    ```bash
    php artisan serve
    ```

If you encounter any issues or have suggestions for improvements, please open an issue or submit a pull request. Thank you for your interest and support!
