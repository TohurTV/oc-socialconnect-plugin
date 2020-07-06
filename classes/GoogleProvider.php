<?php

namespace Tohur\SocialConnect\Classes;

use Illuminate\Support\Arr;
use Laravel\Socialite\Two\ProviderInterface;
use SocialiteProviders\Manager\OAuth2\User;
use Socialite;
use URL;
use Laravel\Socialite\Two\GoogleProvider;

class GoogleProvider extends GoogleProvider {

    /**
     * {@inheritdoc}
     */
    protected function mapUserToObject(array $user) {
        $avatarUrl = Arr::get($user, 'picture');

        return (new User)->setRaw($user)->map([
            'id' => $user['id'],
            'nickname' => Arr::get($user, 'nickname'),
            'name' => Arr::get($user, 'name'),
            'email' => Arr::get($user, 'email'),
            'avatar_original' => $avatarUrl
        ]);
    }

}
