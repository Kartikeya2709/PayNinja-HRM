# Route Analysis Report - web.php

## Critical Errors Found

---

## Organizational Issues

### 2. **Duplicate Route Group Definitions**

**Issue:** Similar routes are defined in multiple places, creating confusion and potential conflicts:

#### Employee Salary/Payroll Routes (Lines 465-481)
```php
// Inside middleware(['role:user,employee'])->prefix('employee')
Route::prefix('salary')->name('salary.')->group(function () {
    Route::get('details', [\App\Http\Controllers\Employee\SalaryController::class, 'details'])
    Route::get('monthly/{year}/{month}', [\App\Http\Controllers\Employee\SalaryController::class, 'monthlyDetails'])
    Route::get('payslips', [\App\Http\Controllers\PayslipController::class, 'listPayslips'])
    Route::get('payslip/{employee}/{monthYear?}', ...)
    Route::get('payslip/{employee}/{monthYear}/download/{salaryId?}', ...)
});
```

#### Employee Payroll Routes (Lines 126-133)
```php
// Inside middleware(['role:company_admin,admin,employee'])
Route::prefix('employee/payroll')->name('employee.payroll.')->group(function () {
    Route::get('/', [EmployeePayrollController::class, 'index'])
    Route::get('/{payroll}', [EmployeePayrollController::class, 'show'])
    Route::get('/{payroll}/download', [EmployeePayrollController::class, 'downloadPayslip'])
});
```

**Problem:** Different route prefixes for similar functionality:
- `/employee/salary/payslips` (lines 470)
- `/employee/payroll` (line 127)

---

### 3. **Missing Import for PayslipController**

**Issue:** `PayslipController` is used in routes but not imported at the top

**Locations:**
- Line 469: `PayslipController::class`
- Line 512: `PayslipController::class`
- Line 520: `PayslipController::class`

**Fix:** Add import
```php
use App\Http\Controllers\PayslipController;
```

---

### 4. **Missing Import for EmploymentTypeManagementController**

**Issue:** Route at line 395 uses `EmploymentTypeManagementController` but no import exists

```php
// Line 395-401
Route::resource('employment-types', \App\Http\Controllers\EmploymentTypeManagementController::class)
```

**Problem:** Full namespace path used instead of import (inconsistent with other routes)

**Fix:** Add import at top
```php
use App\Http\Controllers\EmploymentTypeManagementController;
```

---

### 5. **Inconsistent Route Prefix Naming**

**Issue:** Different naming conventions for similar routes

| Routes | Prefix | Inconsistency |
|--------|--------|---------------|
| Leave requests | `/leave-requests` | Hyphenated |
| Leave balances | `/leave-balances` | Hyphenated |
| Academic holidays | `/academic-holidays` | Hyphenated |
| Admin attendance | `/admin-attendance` | Hyphenated |
| Employee payroll | `/employee/payroll` | Slash-separated |
| Employee salary | `/employee/salary` | Slash-separated |

---

### 6. **Middleware Application Issues**

#### Issue A: Employee Routes Missing Middleware (Lines 573-625)
```php
Route::middleware(['role:user,employee'])->prefix('employee')->name('employee.')->group(function () {
    // All these routes are exposed to users with 'user' OR 'employee' role
```

**Problem:** The role middleware allows both 'user' AND 'employee' roles. 'user' is broader and may grant unintended access.

#### Issue B: Duplicate Admin Routes (Lines 662-673)
```php
// Line 662-673: Admin Resignation Routes
Route::middleware(['role:admin,company_admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('resignations', App\Http\Controllers\Admin\ResignationController::class)
});

// Also defined at lines 446-450 in a different section
```

---

## Route Structure Problems

### 7. **Unverified Route Handlers**

The following controllers are referenced but their methods should be verified to exist:

| Controller | Method | Line | Status |
|-----------|--------|------|--------|
| `CompanyAdminController` | `assetDashboard` | 306 | ✓ Exists |
| `CompanyAdminController` | `moduleAccess` | 519 | ✓ Exists |
| `CompanyAdminController` | `employees` | Line? | ? Unverified |
| `CompanyAdminController` | `createEmployee` | 524 | ✓ Exists |
| `CompanyAdminController` | `storeEmployee` | 526 | ? Unverified |
| `PayslipController` | `listPayslips` | 470 | ✓ Exists |
| `PayslipController` | `getAllPayslips` | 512 | ? Unverified |
| `Employee\SalaryController` | `details` | 586 | ✓ Exists |
| `Admin\ResignationController` | `completeExitInterview` | 667 | ? Unverified |

---

## Missing Routes

### 8. **Incomplete Resource Routes**

Several routes are partially defined:

```php
// Line 335-337: EmployeePayrollConfigController
Route::put('/{employee}', ...)
Route::put('/{employee}/update-salary', ...)
// Missing: create, store, show, destroy, index
```

Comment on line 339 suggests:
```php
// Add other resource routes (create, store, show, destroy) here later if needed
```

---

## Code Quality Issues

### 9. **Commented Out Routes** (Lines 52-66)

```php
// Route::get('/migrate-fresh-seed', function () { ... })
// Route::get('/migrate', function () { ... })
```

**Recommendation:** Remove dead code or document why it's kept

---

### 10. **Inconsistent Use of Full Namespace**

Some routes use full namespace paths instead of imports:

```php
// Line 395 - Uses full path
Route::resource('employment-types', \App\Http\Controllers\EmploymentTypeManagementController::class)

// Line 662 - Uses full path  
Route::resource('resignations', App\Http\Controllers\Admin\ResignationController::class)

// Line 338 - Should also use full path for consistency
Route::middleware(['auth', 'role:admin,company_admin'])->prefix('company')
```

---

## Security Concerns

### 11. **Overly Permissive Route Groups**

```php
// Line 121: Allows company_admin, admin, AND employee in same group
Route::middleware(['role:company_admin,admin,employee'])->group(function () {
    // Sensitive routes like attendance, leave, payroll
});
```

**Issue:** This combines admin and employee permissions in one group, which could lead to unintended access elevation.

**Recommendation:** Separate into distinct middleware groups:
```php
Route::middleware(['role:employee'])->group(function () { /* employee routes */ });
Route::middleware(['role:admin,company_admin'])->group(function () { /* admin routes */ });
```

---

## Summary of Fixes Required

| Priority | Issue | Location | Fix |
|----------|-------|----------|-----|
| 🔴 CRITICAL | Missing `SalaryController` import | Top of file | Add `use App\Http\Controllers\Employee\SalaryController;` |
| 🔴 CRITICAL | Missing `PayslipController` import | Top of file | Add `use App\Http\Controllers\PayslipController;` |
| 🟡 HIGH | Missing `EmploymentTypeManagementController` import | Top of file | Add `use App\Http\Controllers\EmploymentTypeManagementController;` |
| 🟡 HIGH | Duplicate route groups | Lines 573-625, 662-673 | Review and consolidate |
| 🟡 HIGH | Inconsistent middleware | Line 121 | Separate employee/admin permissions |
| 🟠 MEDIUM | Incomplete resource routes | Lines 335-337 | Implement missing methods |
| 🟠 MEDIUM | Inconsistent namespacing | Various | Use imports consistently |
| 🟠 MEDIUM | Dead code | Lines 52-66 | Remove or document |

---

## Recommended Actions

1. **Immediate:** Add missing controller imports to top of file
2. **Review:** Test all route handlers to ensure methods exist
3. **Refactor:** Consolidate duplicate route groups
4. **Security:** Separate admin and employee middleware
5. **Code Quality:** Remove commented-out code
6. **Standards:** Enforce consistent import usage across all routes
