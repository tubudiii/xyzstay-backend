<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveCustomFieldFromUsersTable extends Migration
{
    public function up()
    {
        // contoh: hapus kolom custom_field
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('custom_field');
        });
    }

    public function down()
    {
        // rollback: tambahkan kolom kembali
        Schema::table('users', function (Blueprint $table) {
            $table->string('custom_field')->nullable();
        });
    }
}
