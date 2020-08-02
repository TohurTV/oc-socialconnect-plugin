<?php

namespace Tohur\SocialConnect\Models;

use Model;

class Apisettings extends Model {

    public $implement = ['System.Behaviors.SettingsModel'];
    // A unique code
    public $settingsCode = 'tohur_socialconnect_apisettings';
    // Reference to field configuration
    public $settingsFields = 'fields.yaml';
    protected $cache = [];

}
