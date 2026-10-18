<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The campaign_list pivot declared both foreign keys as string(), but
 * campaigns.id and mailing_lists.id are bigint. MySQL coerces varchar to
 * bigint in a join; Postgres refuses outright with
 *
 *   operator does not exist: bigint = character varying
 *
 * which makes every page touching $campaign->lists return a 500.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('campaign_list')) {
            return;
        }

        $this->assertConvertible();

        $driver = Schema::getConnection()->getDriverName();

        foreach (['campaign_id', 'mailing_list_id'] as $column) {
            if ($driver === 'pgsql') {
                // Postgres will not change the type without an explicit cast.
                DB::statement("ALTER TABLE campaign_list ALTER COLUMN {$column} TYPE bigint USING {$column}::bigint");
            } elseif ($driver === 'mysql' || $driver === 'mariadb') {
                DB::statement("ALTER TABLE campaign_list MODIFY {$column} BIGINT NOT NULL");
            }
            // sqlite is dynamically typed, so no change is needed there.
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('campaign_list')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        foreach (['campaign_id', 'mailing_list_id'] as $column) {
            if ($driver === 'pgsql') {
                DB::statement("ALTER TABLE campaign_list ALTER COLUMN {$column} TYPE varchar(255) USING {$column}::varchar");
            } elseif ($driver === 'mysql' || $driver === 'mariadb') {
                DB::statement("ALTER TABLE campaign_list MODIFY {$column} VARCHAR(255) NOT NULL");
            }
        }
    }

    /**
     * Refuse to run if any row holds a value that is not a plain integer,
     * rather than letting the cast fail partway through the table.
     */
    private function assertConvertible(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        foreach (['campaign_id', 'mailing_list_id'] as $column) {
            $query = DB::table('campaign_list')->whereNotNull($column);

            if ($driver === 'pgsql') {
                $query->whereRaw("{$column}::text !~ '^[0-9]+$'");
            } elseif ($driver === 'mysql' || $driver === 'mariadb') {
                $query->whereRaw("{$column} NOT REGEXP '^[0-9]+$'");
            } else {
                continue;
            }

            $bad = $query->count();

            if ($bad > 0) {
                throw new RuntimeException(
                    "campaign_list.{$column} has {$bad} row(s) that are not plain integers. "
                    . "Inspect and clean them before re-running this migration, e.g.:\n"
                    . "  SELECT id, campaign_id, mailing_list_id FROM campaign_list;"
                );
            }
        }
    }
};
