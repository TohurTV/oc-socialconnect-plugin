<?php

namespace Tohur\SocialConnect\Classes;

use Illuminate\Support\Arr;
use Laravel\Socialite\Two\ProviderInterface;
use SocialiteProviders\Manager\OAuth2\User;
use SocialiteProviders\Twitch\Provider;

class TwitchProvider extends Provider {

    /**
     * {@inheritdoc}
     */
    protected function mapUserToObject(array $user) {
        $user = $user['data']['0'];
        return (new User())->setRaw($user)->map([
                    'id' => $user['id'],
                    'nickname' => $user['display_name'],
                    'name' => $user['display_name'],
                    'email' => Arr::get($user, 'email'),
                    'avatar_original' => $user['profile_image_url'],
        ]);
    }

}
