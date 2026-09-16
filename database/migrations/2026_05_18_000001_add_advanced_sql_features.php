<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Advanced SQL Features Migration
 *
 * Implements:
 *   - Composite and covering indexes for query performance
 *   - Database views for reporting aggregates
 *   - Stored procedures for complex business logic
 *   - Triggers for audit trail automation
 *   - Aggregate function support via views
 *
 * NOTE: Views, stored procedures, and triggers require MySQL/MariaDB.
 *       SQLite (dev default) will skip those blocks gracefully.
 */
return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        // ── 1. Additional indexes ─────────────────────────────────────────────
        Schema::table('applicants', function (Blueprint $table) {
            // Composite index for the most common admin query: status + created_at
            if (! $this->indexExists('applicants', 'applicants_status_created_at_index')) {
                $table->index(['status', 'created_at'], 'applicants_status_created_at_index');
            }
            // Composite index for exam assignment queries
            if (! $this->indexExists('applicants', 'applicants_exam_schedule_status_index')) {
                $table->index(['exam_schedule_id', 'status'], 'applicants_exam_schedule_status_index');
            }
            // Covering index for student dashboard (deoris_user_id + status)
            if (! $this->indexExists('applicants', 'applicants_user_status_index')) {
                $table->index(['deoris_user_id', 'status'], 'applicants_user_status_index');
            }
        });

        Schema::table('exam_schedules', function (Blueprint $table) {
            // Composite index for upcoming exam queries
            if (! $this->indexExists('exam_schedules', 'exam_schedules_status_date_index')) {
                $table->index(['status', 'exam_date'], 'exam_schedules_status_date_index');
            }
        });

        Schema::table('exam_scores', function (Blueprint $table) {
            // Index for score analytics queries
            if (! $this->indexExists('exam_scores', 'exam_scores_schedule_score_index')) {
                $table->index(['exam_schedule_id', 'score'], 'exam_scores_schedule_score_index');
            }
        });

        Schema::table('deoris_event_outbox', function (Blueprint $table) {
            // Index for retry worker queries
            if (! $this->indexExists('deoris_event_outbox', 'outbox_status_attempts_index')) {
                $table->index(['status', 'attempts'], 'outbox_status_attempts_index');
            }
        });

        // MySQL/MariaDB-only features: PostgreSQL/Supabase skip these because they are not supported
        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            $this->createViews();
            $this->createStoredProcedures();
            $this->createTriggers();
        }
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            $this->dropTriggers();
            $this->dropStoredProcedures();
            $this->dropViews();
        }

        Schema::table('applicants', function (Blueprint $table) {
            $table->dropIndexIfExists('applicants_status_created_at_index');
            $table->dropIndexIfExists('applicants_exam_schedule_status_index');
            $table->dropIndexIfExists('applicants_user_status_index');
        });

        Schema::table('exam_schedules', function (Blueprint $table) {
            $table->dropIndexIfExists('exam_schedules_status_date_index');
        });

        Schema::table('exam_scores', function (Blueprint $table) {
            $table->dropIndexIfExists('exam_scores_schedule_score_index');
        });

        Schema::table('deoris_event_outbox', function (Blueprint $table) {
            $table->dropIndexIfExists('outbox_status_attempts_index');
        });
    }

    // ── Views ─────────────────────────────────────────────────────────────────

    private function createViews(): void
    {
        // View: applicant_summary — joins applicants with exam schedules and scores
        DB::statement("
            CREATE OR REPLACE VIEW vw_applicant_summary AS
            SELECT
                a.id                                                    AS applicant_id,
                a.deoris_user_id,
                a.grade_level,
                a.status,
                a.admission_status,
                a.exam_schedule_id,
                a.exam_seat_number,
                a.exam_room,
                a.created_at                                            AS applied_at,
                es.title                                                AS exam_title,
                es.exam_date,
                es.exam_type,
                es.venue,
                es.batch,
                sc.score,
                sc.total_items,
                CASE
                    WHEN sc.total_items > 0
                    THEN ROUND((sc.score / sc.total_items) * 100, 2)
                    ELSE NULL
                END                                                     AS score_percentage,
                CASE
                    WHEN sc.total_items > 0 AND (sc.score / sc.total_items) >= 0.75
                    THEN 'passed'
                    WHEN sc.total_items > 0
                    THEN 'failed'
                    ELSE 'not_scored'
                END                                                     AS exam_result
            FROM applicants a
            LEFT JOIN exam_schedules es ON a.exam_schedule_id = es.id
            LEFT JOIN exam_scores sc    ON sc.applicant_id = a.id
                                       AND sc.exam_schedule_id = a.exam_schedule_id
        ");

        // View: exam_schedule_stats — aggregate stats per schedule
        DB::statement("
            CREATE OR REPLACE VIEW vw_exam_schedule_stats AS
            SELECT
                es.id                                                   AS schedule_id,
                es.title,
                es.exam_date,
                es.exam_type,
                es.status,
                es.slots,
                COUNT(DISTINCT a.id)                                    AS assigned_count,
                es.slots - COUNT(DISTINCT a.id)                        AS available_slots,
                COUNT(DISTINCT sc.id)                                   AS scored_count,
                COUNT(DISTINCT eq.id)                                   AS question_count,
                COUNT(DISTINCT CASE WHEN eq.is_active = 1 THEN eq.id END) AS active_question_count,
                AVG(CASE WHEN sc.total_items > 0
                    THEN (sc.score / sc.total_items) * 100
                    ELSE NULL END)                                      AS avg_score_pct,
                MAX(CASE WHEN sc.total_items > 0
                    THEN (sc.score / sc.total_items) * 100
                    ELSE NULL END)                                      AS max_score_pct,
                MIN(CASE WHEN sc.total_items > 0
                    THEN (sc.score / sc.total_items) * 100
                    ELSE NULL END)                                      AS min_score_pct,
                SUM(CASE WHEN sc.total_items > 0
                    AND (sc.score / sc.total_items) >= 0.75 THEN 1 ELSE 0 END) AS passed_count,
                SUM(CASE WHEN sc.total_items > 0
                    AND (sc.score / sc.total_items) < 0.75 THEN 1 ELSE 0 END)  AS failed_count
            FROM exam_schedules es
            LEFT JOIN applicants a  ON a.exam_schedule_id = es.id
            LEFT JOIN exam_scores sc ON sc.exam_schedule_id = es.id
            LEFT JOIN exam_questions eq ON eq.exam_schedule_id = es.id
            GROUP BY es.id, es.title, es.exam_date, es.exam_type, es.status, es.slots
        ");

        // View: admission_funnel — monthly application funnel for dashboard charts
        DB::statement("
            CREATE OR REPLACE VIEW vw_admission_funnel AS
            SELECT
                DATE_FORMAT(created_at, '%Y-%m')                        AS month,
                COUNT(*)                                                AS total_applications,
                SUM(CASE WHEN status = 'Pending'      THEN 1 ELSE 0 END) AS pending,
                SUM(CASE WHEN status = 'Under Review' THEN 1 ELSE 0 END) AS under_review,
                SUM(CASE WHEN status = 'Approved'     THEN 1 ELSE 0 END) AS approved,
                SUM(CASE WHEN status = 'Rejected'     THEN 1 ELSE 0 END) AS rejected,
                COUNT(DISTINCT deoris_user_id)                          AS unique_students
            FROM applicants
            GROUP BY DATE_FORMAT(created_at, '%Y-%m')
            ORDER BY month DESC
        ");
    }

    private function dropViews(): void
    {
        DB::statement('DROP VIEW IF EXISTS vw_admission_funnel');
        DB::statement('DROP VIEW IF EXISTS vw_exam_schedule_stats');
        DB::statement('DROP VIEW IF EXISTS vw_applicant_summary');
    }

    // ── Stored Procedures ─────────────────────────────────────────────────────

    private function createStoredProcedures(): void
    {
        // Procedure: sp_get_applicant_report — full applicant report with exam data
        DB::unprepared("
            DROP PROCEDURE IF EXISTS sp_get_applicant_report;
        ");
        DB::unprepared("
            CREATE PROCEDURE sp_get_applicant_report(
                IN p_status     VARCHAR(50),
                IN p_date_from  DATE,
                IN p_date_to    DATE,
                IN p_limit      INT,
                IN p_offset     INT
            )
            BEGIN
                SELECT
                    a.id,
                    a.deoris_user_id,
                    a.grade_level,
                    a.status,
                    a.admission_status,
                    a.created_at                                        AS applied_at,
                    es.title                                            AS exam_title,
                    es.exam_date,
                    es.exam_type,
                    sc.score,
                    sc.total_items,
                    CASE
                        WHEN sc.total_items > 0
                        THEN ROUND((sc.score / sc.total_items) * 100, 2)
                        ELSE NULL
                    END                                                 AS score_pct
                FROM applicants a
                LEFT JOIN exam_schedules es ON a.exam_schedule_id = es.id
                LEFT JOIN exam_scores sc    ON sc.applicant_id = a.id
                WHERE (p_status IS NULL OR a.status = p_status)
                  AND (p_date_from IS NULL OR DATE(a.created_at) >= p_date_from)
                  AND (p_date_to   IS NULL OR DATE(a.created_at) <= p_date_to)
                ORDER BY a.created_at DESC
                LIMIT p_limit OFFSET p_offset;
            END
        ");

        // Procedure: sp_exam_score_distribution — score buckets for histogram
        DB::unprepared("
            DROP PROCEDURE IF EXISTS sp_exam_score_distribution;
        ");
        DB::unprepared("
            CREATE PROCEDURE sp_exam_score_distribution(IN p_schedule_id BIGINT)
            BEGIN
                SELECT
                    CASE
                        WHEN pct < 10  THEN '0-9'
                        WHEN pct < 20  THEN '10-19'
                        WHEN pct < 30  THEN '20-29'
                        WHEN pct < 40  THEN '30-39'
                        WHEN pct < 50  THEN '40-49'
                        WHEN pct < 60  THEN '50-59'
                        WHEN pct < 70  THEN '60-69'
                        WHEN pct < 80  THEN '70-79'
                        WHEN pct < 90  THEN '80-89'
                        ELSE '90-100'
                    END                                                 AS bucket,
                    COUNT(*)                                            AS count
                FROM (
                    SELECT ROUND((score / NULLIF(total_items, 0)) * 100, 0) AS pct
                    FROM exam_scores
                    WHERE (p_schedule_id IS NULL OR exam_schedule_id = p_schedule_id)
                      AND total_items > 0
                ) AS score_pcts
                GROUP BY bucket
                ORDER BY MIN(pct);
            END
        ");

        // Procedure: sp_cleanup_expired_sso_tokens — maintenance cleanup
        DB::unprepared("
            DROP PROCEDURE IF EXISTS sp_cleanup_expired_sso_tokens;
        ");
        DB::unprepared("
            CREATE PROCEDURE sp_cleanup_expired_sso_tokens()
            BEGIN
                DELETE FROM sso_tokens
                WHERE portal_issued_at < DATE_SUB(NOW(), INTERVAL 10 MINUTE);

                SELECT ROW_COUNT() AS deleted_count;
            END
        ");
    }

    private function dropStoredProcedures(): void
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_get_applicant_report');
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_exam_score_distribution');
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_cleanup_expired_sso_tokens');
    }

    // ── Triggers ──────────────────────────────────────────────────────────────

    private function createTriggers(): void
    {
        // Trigger: trg_applicants_status_change — auto-log status changes
        DB::unprepared("
            DROP TRIGGER IF EXISTS trg_applicants_status_change;
        ");
        DB::unprepared("
            CREATE TRIGGER trg_applicants_status_change
            AFTER UPDATE ON applicants
            FOR EACH ROW
            BEGIN
                IF OLD.status <> NEW.status THEN
                    INSERT INTO activity_logs (message, type, at, created_at, updated_at)
                    VALUES (
                        CONCAT(
                            'Applicant #', NEW.id,
                            ' status changed from ', OLD.status,
                            ' to ', NEW.status
                        ),
                        CASE NEW.status
                            WHEN 'Approved'     THEN 'green'
                            WHEN 'Rejected'     THEN 'red'
                            WHEN 'Under Review' THEN 'amber'
                            ELSE 'gray'
                        END,
                        NOW(),
                        NOW(),
                        NOW()
                    );
                END IF;
            END
        ");

        // Trigger: trg_exam_score_insert — auto-log score recording
        DB::unprepared("
            DROP TRIGGER IF EXISTS trg_exam_score_insert;
        ");
        DB::unprepared("
            CREATE TRIGGER trg_exam_score_insert
            AFTER INSERT ON exam_scores
            FOR EACH ROW
            BEGIN
                INSERT INTO activity_logs (message, type, at, created_at, updated_at)
                VALUES (
                    CONCAT(
                        'Exam score recorded for applicant #', NEW.applicant_id,
                        ': ', NEW.score, '/', NEW.total_items,
                        ' (', ROUND((NEW.score / NULLIF(NEW.total_items, 0)) * 100, 1), '%)'
                    ),
                    CASE
                        WHEN NEW.total_items > 0 AND (NEW.score / NEW.total_items) >= 0.75
                        THEN 'green'
                        ELSE 'red'
                    END,
                    NOW(),
                    NOW(),
                    NOW()
                );
            END
        ");

        // Trigger: trg_outbox_published — mark outbox event as published
        DB::unprepared("
            DROP TRIGGER IF EXISTS trg_outbox_published;
        ");
        DB::unprepared("
            CREATE TRIGGER trg_outbox_published
            BEFORE UPDATE ON deoris_event_outbox
            FOR EACH ROW
            BEGIN
                IF OLD.status <> 'published' AND NEW.status = 'published' THEN
                    SET NEW.published_at = NOW();
                END IF;
            END
        ");
    }

    private function dropTriggers(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_outbox_published');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_exam_score_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_applicants_status_change');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function indexExists(string $table, string $indexName): bool
    {
        try {
            $indexes = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]);
            return count($indexes) > 0;
        } catch (\Exception) {
            return false;
        }
    }
};
