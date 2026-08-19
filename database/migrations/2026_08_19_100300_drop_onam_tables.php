<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Onceden onam_imzalar (FK bagli) sonra onam_formlari
        Schema::dropIfExists('onam_imzalar');
        Schema::dropIfExists('onam_formlari');
    }

    public function down(): void
    {
        // Kalici olarak kaldirildi (KTS/USBS kapsam d isi kalma amac ali).
    }
};
