<?php

namespace Tohur\SocialConnect\Classes;

use Illuminate\Support\Arr;
use Laravel\Socialite\Two\ProviderInterface;
use SocialiteProviders\Manager\OAuth2\User;
use Socialite;
use URL;
use SocialiteProviders\Discord\Provider;

class DiscordProviderExt extends Provider {

    /**
     * {@inheritdoc}
     */
    protected function mapUserToObject(array $user) {
        return (new User())->setRaw($user)->map([
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'nickname' => sprintf('%s#%s', $user['username'], $user['discriminator']),
                    'name' => $user['username'],
                    'email' => (array_key_exists('email', $user)) ? $user['email'] : null,
                    'avatar_original' => (is_null($user['avatar'])) ? null : sprintf('https://cdn.discordapp.com/avatars/%s/%s.jpg', $user['id'], $user['avatar']),
        ]);
    }

}
