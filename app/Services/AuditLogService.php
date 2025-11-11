<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditLogService
{
    /**
     * Log an action performed on a model.
     *
     * @param string $action
     * @param Model $model
     * @param array $oldValues
     * @param array $newValues
     * @param string|null $description
     * @return AuditLog
     */
    public static function log(string $action, Model $model, array $oldValues = [], array $newValues = [], ?string $description = null): AuditLog
    {
        return AuditLog::create([
            'user_id' => Auth::id(),
            'action_type' => $action,
            'model_type' => get_class($model),
            'model_id' => $model->getKey(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'description' => $description,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }

    /**
     * Log creation of a model.
     *
     * @param Model $model
     * @param string|null $description
     * @return AuditLog
     */
    public static function logCreated(Model $model, ?string $description = null): AuditLog
    {
        return self::log('created', $model, [], $model->toArray(), $description);
    }

    /**
     * Log update of a model.
     *
     * @param Model $model
     * @param array $oldValues
     * @param array $newValues
     * @param string|null $description
     * @return AuditLog
     */
    public static function logUpdated(Model $model, array $oldValues = [], array $newValues = [], ?string $description = null): AuditLog
    {
        return self::log('updated', $model, $oldValues, $newValues, $description);
    }

    /**
     * Log deletion of a model.
     *
     * @param Model $model
     * @param string|null $description
     * @return AuditLog
     */
    public static function logDeleted(Model $model, ?string $description = null): AuditLog
    {
        return self::log('deleted', $model, $model->toArray(), [], $description);
    }

    /**
     * Log assignment action.
     *
     * @param Model $model
     * @param string|null $description
     * @return AuditLog
     */
    public static function logAssigned(Model $model, ?string $description = null): AuditLog
    {
        return self::log('assigned', $model, [], $model->toArray(), $description);
    }

    /**
     * Log reassignment action.
     *
     * @param Model $model
     * @param string|null $description
     * @return AuditLog
     */
    public static function logReassigned(Model $model, ?string $description = null): AuditLog
    {
        return self::log('reassigned', $model, [], $model->toArray(), $description);
    }

    /**
     * Log invoice payment action.
     *
     * @param Model $model
     * @param string|null $description
     * @return AuditLog
     */
    public static function logPaid(Model $model, ?string $description = null): AuditLog
    {
        return self::log('paid', $model, [], $model->toArray(), $description);
    }

    /**
     * Log invoice sent action.
     *
     * @param Model $model
     * @param string|null $description
     * @return AuditLog
     */
    public static function logSent(Model $model, ?string $description = null): AuditLog
    {
        return self::log('sent', $model, [], $model->toArray(), $description);
    }
}