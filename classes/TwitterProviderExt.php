<?php

namespace Tohur\SocialConnect\Classes;

use Illuminate\Support\Arr;
use Laravel\Socialite\One\User;
use Socialite;
use URL;
use Laravel\Socialite\One\TwitterProvider;

class TwitterProviderExt extends TwitterProvider {

    /**
     * {@inheritdoc}
     */
    public function user()
    {
        if (! $this->hasNecessaryVerifier()) {
            throw new InvalidArgumentException('Invalid request. Missing OAuth verifier.');
        }

        $user = $this->server->getUserDetails($token = $this->getToken(), $this->shouldBypassCache($token->getIdentifier(), $token->getSecret()));

        $extraDetails = [
            'location' => $user->location,
            'description' => $user->description,
        ];

        $instance = (new User)->setRaw(array_merge($user->extra, $user->urls, $extraDetails))
                ->setToken($token->getIdentifier(), $token->getSecret());

        return $instance->map([
            'id' => $user->uid,
            'username' => $user->nickname,
            'nickname' => $user->nickname,
            'name' => $user->name,
            'email' => $user->email,
            'avatar_original' => $user->imageUrl
        ]);
    }
}
