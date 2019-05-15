<?php

namespace Tohur\SocialConnect\Classes;

use Illuminate\Support\Arr;
use Laravel\Socialite\Two\ProviderInterface;
use SocialiteProviders\Manager\OAuth2\User;
use SocialiteProviders\Live\Provider;

class MicrosoftProvider extends Provider {

    /**
     * {@inheritdoc}
     */
    protected function mapUserToObject(array $user) {
        return (new User())->setRaw($user)->map([
                    'id' => $user['id'],
                    'nickname' => null,
                    'name' => $user['displayName'],
                    'email' => $user['userPrincipalName'],
                    'avatar' => null,
        ]);
    }

}
