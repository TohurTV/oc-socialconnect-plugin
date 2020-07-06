<?php

namespace Tohur\SocialConnect\Classes;

use Illuminate\Support\Arr;
use Socialite;
use URL;
use Laravel\Socialite\Two\FacebookProvider;

class FacebookProvider extends FacebookProvider {

    /**
     * {@inheritdoc}
     */
    protected function mapUserToObject(array $user) {
        $avatarUrl = $this->graphUrl.'/'.$this->version.'/'.$user['id'].'/picture';

        return (new User)->setRaw($user)->map([
            'id' => $user['id'],
            'nickname' => isset($user['name']) ? $user['name'] : null,
            'name' => isset($user['name']) ? $user['name'] : null,
            'email' => isset($user['email']) ? $user['email'] : null,
            'avatar' => $avatarUrl.'?type=normal'
        ]);
    }

}