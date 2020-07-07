<?php

namespace Tohur\SocialConnect\Models;

use App;
use Str;
use Model;
use Carbon\Carbon;
use October\Rain\Support\Markdown;

/**
 * Post Model
 */
class Provider extends Model {

    public $timestamps = true;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'tohur_socialconnect_providers';

    /**
     * @var array The attributes that are mass assignable.
     */
    protected $fillable = ['user_id', 'provider_id', 'provider_token','provider_refreshToken', 'provider_expiresIn'];

    /**
     * @var array Relations
     */
    public $belongsTo = [
        'user' => ['RainLab\User\Models\User']
    ];

}
