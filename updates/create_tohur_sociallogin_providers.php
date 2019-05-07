<?php namespace Tohur\SocialConnect\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateTohurSocialConnectProvidersTable extends Migration
{

    public function up()
    {
        Schema::create('tohur_socialconnect_providers', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('user_id')->unsigned()->index()->nullable();
            $table->string('provider_id')->default('');
            $table->string('provider_token')->default('');
            $table->index(['provider_id', 'provider_token'], 'provider_id_token_index');
        });
        
        Schema::table('users', function($table)
        {
            $table->string('avatar')->nullable();      #Avatar collumn
            $table->string('tohur_socialconnect_user_providers')->nullable();
        });
    }

    public function down()
    {
        Schema::drop('tohur_socialconnect_providers');
    }

}
