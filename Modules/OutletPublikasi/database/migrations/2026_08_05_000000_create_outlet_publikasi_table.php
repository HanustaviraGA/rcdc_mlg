<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('outlet_publikasi')) {
            Schema::create('outlet_publikasi', function (Blueprint $table) {
                $table->id();
                $table->string('nama_conference');
                $table->string('tipe_kerjasama', 20)->default('-');
                $table->date('deadline_submission');
                $table->text('scope');
                $table->string('contact_pic');
                $table->timestamps();

                $table->index('nama_conference');
                $table->index('created_at');
            });
        }

        $this->registerDashboardMenu();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('sys_role_menu')) {
            DB::table('sys_role_menu')->where('role_menu_menu_id', 'A0045')->delete();
        }

        if (Schema::hasTable('sys_menu')) {
            DB::table('sys_menu')->where('menu_id', 'A0045')->delete();
        }

        Schema::dropIfExists('outlet_publikasi');
    }

    private function registerDashboardMenu(): void
    {
        if (! Schema::hasTable('sys_menu') || ! DB::table('sys_menu')->where('menu_id', 'A004')->exists()) {
            return;
        }

        $timestamp = now();

        DB::table('sys_menu')->updateOrInsert(
            ['menu_id' => 'A0045'],
            [
                'menu_kode' => 'outletpublikasi',
                'menu_judul' => 'Outlet Publikasi',
                'menu_order' => '03.05',
                'menu_parent' => 'A004',
                'menu_aktif' => 1,
                'menu_icon' => 'fa fa-newspaper',
                'menu_level' => 2,
                'menu_sub' => 0,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]
        );

        if (! Schema::hasTable('sys_role') || ! Schema::hasTable('sys_role_menu')) {
            return;
        }

        foreach (['OWN001', 'DSN001'] as $roleId) {
            if (! DB::table('sys_role')->where('role_id', $roleId)->exists()) {
                continue;
            }

            DB::table('sys_role_menu')->updateOrInsert(
                [
                    'role_menu_menu_id' => 'A0045',
                    'role_menu_role_id' => $roleId,
                ],
                [
                    'role_menu_id' => md5('outletpublikasi-'.$roleId),
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ]
            );
        }
    }
};
