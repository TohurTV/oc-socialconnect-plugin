<?php

namespace Tohur\SocialConnect\Classes;

use Illuminate\Support\Arr;
use Laravel\Socialite\Two\ProviderInterface;
use SocialiteProviders\Manager\OAuth2\User;
use Socialite;
use URL;
use Laravel\Socialite\Two\FacebookProvider;

class FacebookProviderExt extends FacebookProvider {

    /**
     * {@inheritdoc}
     */
    protected function mapUserToObject(array $user) {
        $avatarUrl = $this->graphUrl.'/'.$this->version.'/'.$user['id'].'/picture';

        return (new User)->setRaw($user)->map([
            'id' => $user['id'],
            'username' => isset($user['name']) ? $user['name'] : null,
            'nickname' => isset($user['name']) ? $user['name'] : null,
            'name' => isset($user['name']) ? $user['name'] : null,
            'email' => isset($user['email']) ? $user['email'] : null,
            'avatar_original' => $avatarUrl.'?type=normal'
        ]);
    }

}
