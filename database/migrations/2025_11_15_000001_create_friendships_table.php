<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFriendshipsTable extends Migration
{
    public function up()
    {
        Schema::create('friendships', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('requester_id');
            $table->unsignedBigInteger('addressee_id');
            $table->enum('status', ['pending','accepted'])->default('pending');
            $table->timestamps();

            $table->foreign('requester_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('addressee_id')->references('id')->on('users')->onDelete('cascade');

            $table->unique(['requester_id','addressee_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('friendships');
    }
}
