<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Check if index exists on a table
     */
    protected function indexExists(string $table, string $index): bool
    {
        $connection = Schema::getConnection();
        $database = $connection->getDatabaseName();
        
        $result = DB::select(
            "SELECT COUNT(*) as count FROM information_schema.statistics 
             WHERE table_schema = ? AND table_name = ? AND index_name = ?",
            [$database, $table, $index]
        );
        
        return $result[0]->count > 0;
    }
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add featured columns to flights table
        Schema::table('flights', function (Blueprint $table) {
            if (!Schema::hasColumn('flights', 'is_featured')) {
                $table->boolean('is_featured')->default(false)->after('allows_agent_collaboration');
            }
            if (!Schema::hasColumn('flights', 'featured_at')) {
                $table->timestamp('featured_at')->nullable()->after('allows_agent_collaboration');
            }
            if (!Schema::hasColumn('flights', 'featured_expires_at')) {
                $table->timestamp('featured_expires_at')->nullable()->after('allows_agent_collaboration');
            }
            
            if (!$this->indexExists('flights', 'flights_is_featured_index')) {
                $table->index('is_featured');
            }
            if (!$this->indexExists('flights', 'flights_featured_expires_at_index')) {
                $table->index('featured_expires_at');
            }
        });

        // Add featured columns to hotels table
        Schema::table('hotels', function (Blueprint $table) {
            if (!Schema::hasColumn('hotels', 'is_featured')) {
                $table->boolean('is_featured')->default(false)->after('is_active');
            }
            if (!Schema::hasColumn('hotels', 'featured_at')) {
                $table->timestamp('featured_at')->nullable()->after('is_active');
            }
            if (!Schema::hasColumn('hotels', 'featured_expires_at')) {
                $table->timestamp('featured_expires_at')->nullable()->after('is_active');
            }
            
            if (!$this->indexExists('hotels', 'hotels_is_featured_index')) {
                $table->index('is_featured');
            }
            if (!$this->indexExists('hotels', 'hotels_featured_expires_at_index')) {
                $table->index('featured_expires_at');
            }
        });

        // Add featured columns to packages table
        Schema::table('packages', function (Blueprint $table) {
            if (!Schema::hasColumn('packages', 'is_featured')) {
                $table->boolean('is_featured')->default(false)->after('is_premium');
            }
            if (!Schema::hasColumn('packages', 'featured_at')) {
                $table->timestamp('featured_at')->nullable()->after('is_premium');
            }
            if (!Schema::hasColumn('packages', 'featured_expires_at')) {
                $table->timestamp('featured_expires_at')->nullable()->after('is_premium');
            }
            
            if (!$this->indexExists('packages', 'packages_is_featured_index')) {
                $table->index('is_featured');
            }
            if (!$this->indexExists('packages', 'packages_featured_expires_at_index')) {
                $table->index('featured_expires_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('flights', function (Blueprint $table) {
            $table->dropIndex(['is_featured']);
            $table->dropIndex(['featured_expires_at']);
            $table->dropColumn(['is_featured', 'featured_at', 'featured_expires_at']);
        });

        Schema::table('hotels', function (Blueprint $table) {
            $table->dropIndex(['is_featured']);
            $table->dropIndex(['featured_expires_at']);
            $table->dropColumn(['is_featured', 'featured_at', 'featured_expires_at']);
        });

        Schema::table('packages', function (Blueprint $table) {
            $table->dropIndex(['is_featured']);
            $table->dropIndex(['featured_expires_at']);
            $table->dropColumn(['is_featured', 'featured_at', 'featured_expires_at']);
        });
    }
};
