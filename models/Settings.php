<?php

namespace Tohur\SocialConnect\Models;

use Model;

class Settings extends Model {

    public $implement = ['System.Behaviors.SettingsModel'];
    // A unique code
    public $settingsCode = 'tohur_socialconnect_settings';
    // Reference to field configuration
    public $settingsFields = 'fields.yaml';
    protected $cache = [];

}
