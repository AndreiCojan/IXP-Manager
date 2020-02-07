<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDocstoreFiles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('docstore_files', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->bigInteger('docstore_directory_id')->nullable(false)->unsigned();
            $table->string('name',100);
            $table->string('disk',100)->default('docstore');
            $table->string('path',255);
            $table->string('sha256',64)->nullable();
            $table->text('description',100)->nullable();
            $table->smallInteger('min_privs');

            // we're not using a FK constraint here as users can be deleted without deleting files.
            $table->integer('created_by')->nullable();

            $table->foreign('docstore_directory_id')->references('id')->on('docstore_directories');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('docstore_files');
    }
}
