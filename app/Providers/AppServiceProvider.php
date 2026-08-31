<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $storagePaths = [
            storage_path('framework/views'),
            storage_path('framework/cache/data'),
            storage_path('framework/sessions'),
            storage_path('logs'),
        ];

        foreach ($storagePaths as $path) {
            if (!is_dir($path)) {
                @mkdir($path, 0775, true);
            }
        }

        try {
            if (!\Illuminate\Support\Facades\Schema::hasTable('company_user')) {
                \Illuminate\Support\Facades\Schema::create('company_user', function (\Illuminate\Database\Schema\Blueprint $table) {
                    $table->id();
                    $table->unsignedBigInteger('company_id');
                    $table->unsignedBigInteger('user_id');
                    $table->timestamps();

                    $table->unique(['company_id', 'user_id']);
                });
            }

            if (\Illuminate\Support\Facades\Schema::hasTable('company_user')) {
                if (\Illuminate\Support\Facades\DB::table('company_user')->count() === 0) {
                    // Populate from existing manager_id in companies
                    $companies = \Illuminate\Support\Facades\DB::table('companies')->whereNotNull('manager_id')->get();
                    foreach ($companies as $c) {
                        \Illuminate\Support\Facades\DB::table('company_user')->insertOrIgnore([
                            'company_id' => $c->id,
                            'user_id'    => $c->manager_id,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }

                    // Populate from existing company_id in users
                    $users = \Illuminate\Support\Facades\DB::table('users')->whereNotNull('company_id')->get();
                    foreach ($users as $u) {
                        \Illuminate\Support\Facades\DB::table('company_user')->insertOrIgnore([
                            'company_id' => $u->company_id,
                            'user_id'    => $u->id,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        } catch (\Throwable $e) {
            // DB might not be ready or configured yet
        }
    }
}
