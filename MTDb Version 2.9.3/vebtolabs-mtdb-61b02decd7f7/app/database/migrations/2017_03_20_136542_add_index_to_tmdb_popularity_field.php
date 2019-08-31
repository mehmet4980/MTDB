<?php

use Illuminate\Database\Migrations\Migration;

class AddIndexToTmdbPopularityField extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('titles', function($table)
		{
    		$table->index('tmdb_popularity');
    		$table->index('temp_id');
		});

        Schema::table('actors', function($table)
        {
            $table->index('temp_id');
        });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		//
	}

}