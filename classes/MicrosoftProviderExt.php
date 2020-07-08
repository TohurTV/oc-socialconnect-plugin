<?php

namespace Tohur\SocialConnect\Classes;

use Illuminate\Support\Arr;
use Laravel\Socialite\Two\ProviderInterface;
use SocialiteProviders\Manager\OAuth2\User;
use Socialite;
use URL;
use SocialiteProviders\Graph\Provider;

class MicrosoftProviderExt extends Provider {

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
