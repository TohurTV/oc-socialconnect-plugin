<?php

namespace Tohur\SocialConnect\Classes;

use Illuminate\Support\Arr;
use Socialite;
use URL;
use Laravel\Socialite\One\TwitterProvider;

class TwitterProvider extends TwitterProvider {

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
            'nickname' => $user->nickname,
            'name' => $user->name,
            'email' => $user->email,
            'avatar' => $user->imageUrl
        ]);
    }
}
