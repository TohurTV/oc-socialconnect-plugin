<?php

namespace Tohur\SocialConnect\Classes;

use Illuminate\Support\Arr;
use Socialite;
use URL;
use SocialiteProviders\Graph\Provider;

class MicrosoftProvider extends Provider {

    /**
     * {@inheritdoc}
     */
    protected function mapUserToObject(array $user) {
        return (new User())->setRaw($user)->map([
          'id'                => $user['id'],
            'name'              => $user['displayName'],
            'email'             => $user['mail'],
            'displayName'       => $user['displayName']
        ]);
    }

}
