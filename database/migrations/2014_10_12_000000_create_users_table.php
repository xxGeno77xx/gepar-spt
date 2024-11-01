<?php

use App\Support\Database\StatesClass;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            $table->string('name');

            $table->string('lastName')
                ->nullable();

            $table->string('username');

            $table->string('email')->unique();

            $table->string('poste')->nullable();

            $table->timestamp('email_verified_at')->nullable();

            $table->boolean('notification')->comment('Users that will get notified with mails: true(1) for yes, false(0) for no');

            $table->enum('state', [StatesClass::Activated()->value, StatesClass::Deactivated()->value, StatesClass::Suspended()->value]);

            $table->string('password');

            $table->rememberToken();

            $table->unsignedBigInteger('departement_id')->nullable();  //is actually direction_id

            // $table->unsignedBigInteger('division_id');  //is actually division_id

            $table->integer('login_attempts')->default(1)->comment('number of times a user can attempt login before account is blocked');

            $table->softDeletes();
            $table->timestamps();

            // $sequence = DB::getSequence();
            // $sequence->drop('users_id_seq');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
