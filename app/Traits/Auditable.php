<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

trait Auditable
{
    /**
     * Boot the Auditable trait for a model.
     */
    public static function bootAuditable(): void
    {
        static::creating(function (Model $model) {
            static::syncAuditContext();
        });

        static::created(function (Model $model) {
            static::recordAuditLog($model, 'insert');
        });

        static::updating(function (Model $model) {
            static::syncAuditContext();
        });

        static::updated(function (Model $model) {
            if (! static::hasDatabaseTrigger($model->getTable(), 'UPDATE')) {
                static::recordAuditLog($model, 'update');
            }
        });

        static::deleting(function (Model $model) {
            static::syncAuditContext();
        });

        static::deleted(function (Model $model) {
            if (! static::hasDatabaseTrigger($model->getTable(), 'DELETE')) {
                static::recordAuditLog($model, 'delete');
            }
        });
    }

    /**
     * Sinkronisasi variabel sesi MySQL @current_user_id agar trigger DB menangkap user login.
     */
    protected static function syncAuditContext(): void
    {
        try {
            if (Auth::check()) {
                DB::statement('SET @current_user_id = ?', [Auth::id()]);
            }
        } catch (\Throwable) {
            // Abaikan jika DB bukan MySQL atau koneksi belum siap
        }
    }

    /**
     * Cek apakah tabel sudah memiliki database trigger MySQL untuk event tertentu.
     */
    protected static function hasDatabaseTrigger(string $table, string $event): bool
    {
        $tablesWithTriggers = ['devices', 'divisions', 'garment_categories', 'garments', 'tags', 'users'];
        return in_array($table, $tablesWithTriggers, true);
    }

    /**
     * Simpan record audit log ke tabel audit_logs.
     */
    protected static function recordAuditLog(Model $model, string $action): void
    {
        try {
            if (! Schema::hasTable('audit_logs')) {
                return;
            }
        } catch (\Throwable) {
            return;
        }

        $hidden = array_merge(
            $model->getHidden(),
            ['password', 'password_hash', 'remember_token']
        );

        $oldValue = null;
        $newValue = null;

        if ($action === 'insert') {
            $attrs = collect($model->getAttributes())->except($hidden)->all();
            $newValue = json_encode($attrs);
        } elseif ($action === 'update') {
            $changes = collect($model->getChanges())->except($hidden)->all();
            if (empty($changes)) {
                return;
            }
            $original = collect($model->getOriginal())->only(array_keys($changes))->all();
            $oldValue = json_encode($original);
            $newValue = json_encode($changes);
        } elseif ($action === 'delete') {
            $attrs = collect($model->getOriginal())->except($hidden)->all();
            $oldValue = json_encode($attrs);
        }

        try {
            AuditLog::create([
                'table_name' => $model->getTable(),
                'record_id'  => (string) $model->getKey(),
                'action'     => $action,
                'old_value'  => $oldValue,
                'new_value'  => $newValue,
                'changed_by' => Auth::id(),
            ]);
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
