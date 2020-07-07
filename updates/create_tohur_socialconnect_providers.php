<?php

namespace Tohur\SocialConnect\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateTohurSocialConnectProvidersTable extends Migration {

    public function up() {
        Schema::create('tohur_socialconnect_providers', function($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('user_id')->unsigned()->index()->nullable();
            $table->string('provider_id')->default('');
            $table->string('provider_token')->default('');
            $table->string('provider_refreshToken')->default('');
            $table->string('provider_expiresIn')->default('');
            $table->timestamps();
            $table->index(['provider_id', 'provider_token'], 'provider_id_token_index');
        });
        
        Schema::create('tohur_socialconnect_twitch_apptokens', function($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('access_token', 100)->default('')->index();
            $table->string('expires_in', 100)->default('')->index();
            $table->timestamps();
        });
        
        Schema::table('users', function($table) {
            $table->string('tohur_socialconnect_user_providers')->nullable();
            $table->string('tohur_socialconnect_twitchid')->nullable();
        });
    }

    public function down() {
        Schema::drop('tohur_socialconnect_providers');
        Schema::drop('tohur_socialconnect_twitch_apptokens');
        Schema::table('users', function($table) {
            $table->dropColumn('tohur_socialconnect_user_providers');
            $table->dropColumn('tohur_socialconnect_twitchid');
        });
    }

}
