<?php

namespace Tohur\SocialConnect\Classes;

use Illuminate\Support\Arr;
use Laravel\Socialite\Two\ProviderInterface;
use SocialiteProviders\Manager\OAuth2\User;
use Socialite;
use URL;
use Tohur\SocialConnect\Models\Settings;
use SocialiteProviders\Twitch\Provider;

class TwitchProviderExt extends Provider {

    
     /**
     * {@inheritdoc}
     */
    protected $scopes = ['user:read:email', 'bits:read', 'channel:read:subscriptions'];
    
    /**
     * {@inheritdoc}
     */
    protected function mapUserToObject(array $user) {
        $user = $user['data']['0'];
        return (new User())->setRaw($user)->map([
                    'id' => $user['id'],
                    'username' => $user['login'],
                    'nickname' => $user['display_name'],
                    'name' => $user['display_name'],
                    'email' => Arr::get($user, 'email'),
                    'avatar_original' => $user['profile_image_url'],
        ]);
    }

}
