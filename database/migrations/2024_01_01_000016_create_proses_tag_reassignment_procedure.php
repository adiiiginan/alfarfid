<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS proses_tag_reassignment");

        DB::unprepared("
            CREATE PROCEDURE proses_tag_reassignment(
                IN  p_old_garment_id   CHAR(36),
                IN  p_tag_id           CHAR(36),
                IN  p_new_garment_id   CHAR(36),
                IN  p_rated_max_cycles INT,
                IN  p_operator_id      CHAR(36),
                OUT p_result           JSON
            )
            proc_block: BEGIN
                DECLARE v_total_cycles_used INT;
                DECLARE v_tag_status        VARCHAR(20);
                DECLARE v_new_binding_id    CHAR(36);
                DECLARE v_err_msg           VARCHAR(255);

                DECLARE EXIT HANDLER FOR SQLEXCEPTION
                BEGIN
                    ROLLBACK;
                    RESIGNAL;
                END;

                START TRANSACTION;

                -- Guard: old_garment_id tidak boleh sama dengan new_garment_id.
                -- Baju baru wajib row terpisah (INSERT), bukan reuse baju lama (FR-04).
                IF p_old_garment_id = p_new_garment_id THEN
                    SET v_err_msg = 'old_garment_id tidak boleh sama dengan new_garment_id - baju baru wajib row terpisah (FR-04)';
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = v_err_msg;
                END IF;

                SELECT total_cycles_used, status
                INTO v_total_cycles_used, v_tag_status
                FROM tags
                WHERE tag_id = p_tag_id
                FOR UPDATE;

                IF v_tag_status = 'retired' THEN
                    SET v_err_msg = CONCAT('Tag ', p_tag_id, ' sudah berstatus retired, tidak bisa dipakai ulang');
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = v_err_msg;
                END IF;

                IF NOT EXISTS (
                    SELECT 1 FROM tag_garment_bindings
                    WHERE tag_id = p_tag_id
                      AND garment_id = p_old_garment_id
                      AND is_current = TRUE
                ) THEN
                    SET v_err_msg = 'Binding aktif tidak ditemukan - proses kemungkinan sudah pernah dijalankan (cek idempotency)';
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = v_err_msg;
                END IF;

                UPDATE tag_garment_bindings
                SET unbound_at = NOW(6),
                    is_current = FALSE
                WHERE tag_id = p_tag_id
                  AND garment_id = p_old_garment_id
                  AND is_current = TRUE;

                UPDATE garments
                SET status = 'retired',
                    date_retired = COALESCE(date_retired, CURDATE()),
                    current_tag_id = NULL
                WHERE garment_id = p_old_garment_id
                  AND status <> 'retired';

                IF v_total_cycles_used >= p_rated_max_cycles THEN
                    UPDATE tags
                    SET status = 'retired'
                    WHERE tag_id = p_tag_id;

                    INSERT INTO notifications (category, message, created_at)
                    VALUES (
                        'procurement_alert',
                        CONCAT('Tag RFID ', p_tag_id, ' telah mencapai batas umur fisik (',
                               v_total_cycles_used, '/', p_rated_max_cycles,
                               ' siklus) dan dipensiunkan. Perlu pengadaan tag baru.'),
                        NOW(6)
                    );

                    INSERT INTO audit_logs (table_name, record_id, action, changed_by, new_value, changed_at)
                    VALUES (
                        'tags',
                        p_tag_id,
                        'update',
                        p_operator_id,
                        JSON_OBJECT(
                            'event_type', 'tag_retired_on_reassignment',
                            'old_garment_id', p_old_garment_id,
                            'total_cycles_used', v_total_cycles_used
                        ),
                        NOW(6)
                    );

                    SET p_result = JSON_OBJECT(
                        'tag_retired', TRUE,
                        'garment_bound', FALSE,
                        'message', 'Tag telah mencapai batas umur fisik dan dipensiunkan. Gunakan tag lain untuk baju baru.'
                    );

                    COMMIT;
                    LEAVE proc_block;
                END IF;

                SET v_new_binding_id = UUID();

                INSERT INTO tag_garment_bindings (binding_id, tag_id, garment_id, bound_at, bound_by, is_current)
                VALUES (v_new_binding_id, p_tag_id, p_new_garment_id, NOW(6), p_operator_id, TRUE);

                UPDATE garments
                SET current_cycle_count = 0,
                    current_tag_id = p_tag_id,
                    status = 'active'
                WHERE garment_id = p_new_garment_id;

                INSERT INTO audit_logs (table_name, record_id, action, changed_by, new_value, changed_at)
                VALUES (
                    'tag_garment_bindings',
                    v_new_binding_id,
                    'insert',
                    p_operator_id,
                    JSON_OBJECT(
                        'event_type', 'tag_reassignment_completed',
                        'tag_id', p_tag_id,
                        'old_garment_id', p_old_garment_id,
                        'new_garment_id', p_new_garment_id,
                        'tag_total_cycles_used_unchanged', v_total_cycles_used
                    ),
                    NOW(6)
                );

                SET p_result = JSON_OBJECT(
                    'tag_retired', FALSE,
                    'garment_bound', TRUE,
                    'new_binding_id', v_new_binding_id,
                    'message', 'Tag berhasil dipindahkan ke baju baru. current_cycle_count baju baru direset ke 0, total_cycles_used tag tidak berubah.'
                );

                COMMIT;
            END
        ");
    }

    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS proses_tag_reassignment");
    }
};
