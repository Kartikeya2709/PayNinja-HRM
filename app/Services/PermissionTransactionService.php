<?php

namespace App\Services;

use App\Models\Company;
use App\Models\CompanyPackage;
use App\Models\Package;
use App\Models\Role;
use App\Models\AuditLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class PermissionTransactionService
{
    /**
     * Execute an operation with atomic permission synchronization
     *
     * @param callable $operation The main operation to execute
     * @param int $companyId Company ID
     * @param int|null $packageId Package ID (null to clear permissions)
     * @param array $context Additional context for logging
     * @return mixed Result of the operation
     * @throws Exception
     */
    public function executeWithPermissionSync(callable $operation, int $companyId, ?int $packageId = null, array $context = [])
    {
        DB::beginTransaction();
        try {
            // Validate authorization before proceeding
            $this->validatePermissionUpdateAuthorization($companyId);

            // Execute main operation
            $result = $operation();

            // Validate and sync permissions atomically
            $this->validateAndSyncPermissions($companyId, $packageId, $context);

            DB::commit();
            return $result;

        } catch (Exception $e) {
            DB::rollBack();
            $this->logPermissionSyncFailure($companyId, $packageId, $e, $context);
            throw $e;
        }
    }

    /**
     * Validate and synchronize permissions atomically
     */
    private function validateAndSyncPermissions(int $companyId, ?int $packageId, array $context = [])
    {
        // Validate only one active package per company
        $activePackages = CompanyPackage::where('company_id', $companyId)
            ->where('is_active', true)
            ->count();

        if ($activePackages > 1) {
            throw new Exception('Multiple active packages detected for company ' . $companyId);
        }

        // Sync permissions based on active package
        if ($packageId) {
            $this->updateCompanyRolePermissions($companyId, $packageId, $context);
        } else {
            $this->clearCompanyRolePermissions($companyId, $context);
        }
    }

    /**
     * Update company role permissions with validation
     */
    private function updateCompanyRolePermissions(int $companyId, int $packageId, array $context = [])
    {
        try {
            // Lock resources to prevent race conditions
            $package = Package::lockForUpdate()->findOrFail($packageId);
            $roles = Role::where('company_id', $companyId)->lockForUpdate()->get();

            if ($roles->isEmpty()) {
                throw new Exception("No roles found for company {$companyId}");
            }

            // Store old permissions for audit logging
            $oldPermissions = [];
            foreach ($roles as $role) {
                $oldPermissions[$role->id] = $role->permissions;
            }

            // Validate package has modules
            if (!$package->modules || !is_array($package->modules)) {
                throw new Exception("Package {$packageId} has invalid or missing modules configuration");
            }

            // Update permissions for all roles
            foreach ($roles as $role) {
                $role->update(['permissions' => $package->modules]);
            }

            // Log permission changes
            $this->logPermissionChanges($companyId, $oldPermissions, $package->modules, $context);

            Log::info('Successfully updated role permissions', [
                'company_id' => $companyId,
                'package_id' => $packageId,
                'roles_updated' => $roles->count()
            ]);

            return true;

        } catch (Exception $e) {
            Log::error('Failed to update role permissions', [
                'company_id' => $companyId,
                'package_id' => $packageId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Clear company role permissions with validation
     */
    private function clearCompanyRolePermissions(int $companyId, array $context = [])
    {
        try {
            $roles = Role::where('company_id', $companyId)->lockForUpdate()->get();

            if ($roles->isEmpty()) {
                Log::warning("No roles found for company {$companyId} during permission clearance");
                return true;
            }

            // Store old permissions for audit logging
            $oldPermissions = [];
            foreach ($roles as $role) {
                $oldPermissions[$role->id] = $role->permissions;
            }

            // Clear permissions for all roles
            foreach ($roles as $role) {
                $role->update(['permissions' => null]);
            }

            // Validate permissions were cleared
            $this->validatePermissionsCleared($companyId);

            // Log permission clearance
            $this->logPermissionClearance($companyId, $oldPermissions, $context);

            Log::info('Successfully cleared role permissions', [
                'company_id' => $companyId,
                'roles_updated' => $roles->count()
            ]);

            return true;

        } catch (Exception $e) {
            Log::error('Failed to clear role permissions', [
                'company_id' => $companyId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Validate that permissions were applied correctly
     */
    private function validatePermissionsApplied(int $companyId, array $expectedPermissions)
    {
        $roles = Role::where('company_id', $companyId)->get();

        foreach ($roles as $role) {
            if ($role->permissions !== $expectedPermissions) {
                Log::info(gettype($role->permissions) .'vs'. gettype($expectedPermissions));
                throw new Exception("Permission validation failed for role {$role->id}: expected " .
                    json_encode($expectedPermissions) . ", got " . json_encode($role->permissions));
            }
        }
    }

    /**
     * Validate that permissions were cleared correctly
     */
    private function validatePermissionsCleared(int $companyId)
    {
        $roles = Role::where('company_id', $companyId)->get();

        foreach ($roles as $role) {
            if ($role->permissions !== null) {
                throw new Exception("Permission clearance validation failed for role {$role->id}: expected null, got " .
                    json_encode($role->permissions));
            }
        }
    }

    /**
     * Validate authorization for permission updates
     */
    private function validatePermissionUpdateAuthorization(int $companyId)
    {
        $user = auth()->user();

        // Check if user is superadmin
        if (!$user || !$user->hasRole('superadmin')) {
            throw new Exception('Unauthorized: Only superadmin can modify company permissions');
        }

        // Validate company exists and is accessible
        $company = Company::find($companyId);
        if (!$company) {
            throw new Exception("Company {$companyId} not found");
        }

        // Additional security checks can be added here
        // e.g., check if user has access to this specific company
    }

    /**
     * Log permission changes for audit trail
     */
    private function logPermissionChanges(int $companyId, array $oldPermissions, array $newPermissions, array $context = [])
    {
        try {
            AuditLog::create([
                'user_id' => auth()->id(),
                'action_type' => 'permissions_updated',
                'model_type' => Role::class,
                'model_id' => null, // Multiple roles affected
                'old_values' => [
                    'company_id' => $companyId,
                    'permissions' => $oldPermissions
                ],
                'new_values' => [
                    'company_id' => $companyId,
                    'permissions' => $newPermissions
                ],
                'description' => 'Role permissions updated for company ' . $companyId .
                    (isset($context['package_name']) ? " (Package: {$context['package_name']})" : ''),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'metadata' => array_merge($context, [
                    'roles_affected' => count($oldPermissions),
                    'timestamp' => now()->toISOString()
                ])
            ]);
        } catch (Exception $e) {
            Log::error('Failed to log permission changes', [
                'company_id' => $companyId,
                'error' => $e->getMessage()
            ]);
            // Don't throw here as this is logging, not core functionality
        }
    }

    /**
     * Log permission clearance for audit trail
     */
    private function logPermissionClearance(int $companyId, array $oldPermissions, array $context = [])
    {
        try {
            AuditLog::create([
                'user_id' => auth()->id(),
                'action_type' => 'permissions_cleared',
                'model_type' => Role::class,
                'model_id' => null, // Multiple roles affected
                'old_values' => [
                    'company_id' => $companyId,
                    'permissions' => $oldPermissions
                ],
                'new_values' => [
                    'company_id' => $companyId,
                    'permissions' => null
                ],
                'description' => 'Role permissions cleared for company ' . $companyId,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'metadata' => array_merge($context, [
                    'roles_affected' => count($oldPermissions),
                    'timestamp' => now()->toISOString()
                ])
            ]);
        } catch (Exception $e) {
            Log::error('Failed to log permission clearance', [
                'company_id' => $companyId,
                'error' => $e->getMessage()
            ]);
            // Don't throw here as this is logging, not core functionality
        }
    }

    /**
     * Log permission synchronization failures
     */
    private function logPermissionSyncFailure(int $companyId, ?int $packageId, Exception $e, array $context = [])
    {
        try {
            AuditLog::create([
                'user_id' => auth()->id(),
                'action_type' => 'permission_sync_failed',
                'model_type' => CompanyPackage::class,
                'model_id' => null,
                'old_values' => [
                    'company_id' => $companyId,
                    'package_id' => $packageId
                ],
                'new_values' => null,
                'description' => 'Permission synchronization failed: ' . $e->getMessage(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'metadata' => array_merge($context, [
                    'error_type' => get_class($e),
                    'error_message' => $e->getMessage(),
                    'timestamp' => now()->toISOString()
                ])
            ]);
        } catch (Exception $logError) {
            Log::error('Failed to log permission sync failure', [
                'company_id' => $companyId,
                'package_id' => $packageId,
                'original_error' => $e->getMessage(),
                'log_error' => $logError->getMessage()
            ]);
        }
    }

    /**
     * Check if company has valid permission state
     */
    public function validateCompanyPermissionState(int $companyId): array
    {
        $issues = [];

        // Check for multiple active packages
        $activePackages = CompanyPackage::where('company_id', $companyId)
            ->where('is_active', true)
            ->count();

        if ($activePackages > 1) {
            $issues[] = 'Multiple active packages detected';
        }

        // Check role permission consistency
        $roles = Role::where('company_id', $companyId)->get();

        if ($activePackages === 1) {
            $activePackage = CompanyPackage::where('company_id', $companyId)
                ->where('is_active', true)
                ->with('package')
                ->first();

            foreach ($roles as $role) {
                if ($role->permissions !== $activePackage->package->modules) {
                    $issues[] = "Role {$role->name} has inconsistent permissions";
                }
            }
        } elseif ($activePackages === 0) {
            foreach ($roles as $role) {
                if ($role->permissions !== null) {
                    $issues[] = "Role {$role->name} has permissions but no active package";
                }
            }
        }

        return [
            'is_valid' => empty($issues),
            'issues' => $issues,
            'active_packages' => $activePackages,
            'roles_count' => $roles->count()
        ];
    }
}