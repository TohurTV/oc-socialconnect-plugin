<?php

Route::get('logout', function () {
    $user = Auth::getUser();

    Auth::logout();

    if ($user) {
        Event::fire('rainlab.user.logout', [$user]);
    }


    return Redirect::to('/');
});

// http://home.tohur.com/tohur/socialconnect/Google?s=/&f=/login
Route::get('socialconnect/{provider}', array("as" => "tohur_socialconnect_provider", 'middleware' => ['web'], function($provider_name, $action = "") {
        $success_redirect = Input::get('s', '/');
        $error_redirect = Input::get('f', '/login');
        Session::flash('tohur_socialconnect_successredirect', $success_redirect);
        Session::flash('tohur_socialconnect_errorredirect', $error_redirect);

        $provider_class = Tohur\SocialConnect\Classes\ProviderManager::instance()
                ->resolveProvider($provider_name);

        if (!$provider_class)
            return Redirect::to($error_redirect)->withErrors("Unknown login provider: $provider_name.");

        $provider = $provider_class::instance();

        return $provider->redirectToProvider();
    }))->where(['provider' => '[A-Z][a-zA-Z ]+']);

Route::get('socialconnect/{provider}/callback', ['as' => 'tohur_socialconnect_provider_callback', 'middleware' => ['web'], function($provider_name) {
        $success_redirect = Session::get('tohur_socialconnect_successredirect', '/');
        $error_redirect = Session::get('tohur_socialconnect_errorredirect', '/login');

        $provider_class = Tohur\SocialConnect\Classes\ProviderManager::instance()
                ->resolveProvider($provider_name);

        if (!$provider_class)
            return Redirect::to($error_redirect)->withErrors("Unknown login provider: $provider_name.");

        $provider = $provider_class::instance();

        try {
            // This will contain [token => ..., email => ..., ...]
            $provider_response = $provider->handleProviderCallback($provider_name);

            if (!is_array($provider_response))
                return Redirect::to($error_redirect);
        } catch (Exception $e) {
            // Log the error
            Log::error($e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());

            return Redirect::to($error_redirect)->withErrors([$e->getMessage()]);
        }

        $provider_details = [
            'provider_id' => $provider_name,
            'provider_token' => $provider_response['token'],
        ];
        $user_details = array_except($provider_response, 'token');

        // Backend logins
        if ($success_redirect == Backend::url()) {
            $user = Tohur\SocialConnect\Classes\UserManager::instance()
                    ->findBackendUserByEmail($user_details['email']);

            if (!$user)
                throw new October\Rain\Auth\AuthException(sprintf(
                                'Administrator with email address "%s" not found.', $user_details['email']
                ));

            // Support custom login handling
            $result = Event::fire('tohur.socialconnect.handleBackendLogin', [
                        $provider_details, $provider_response, $user
                            ], true);
            if ($result)
                return $result;

            BackendAuth::login($user, true);

            // Load version updates
            System\Classes\UpdateManager::instance()->update();

            // Log the sign in event
            Backend\Models\AccessLog::add($user);
        }
        // Frontend Logins
        else {
            // Grab the user associated with this provider. Creates or attach one if need be.
            $user = \Tohur\SocialConnect\Classes\UserManager::instance()->find(
                    $provider_details,
                    $user_details
            );

            // Support custom login handling
            $result = Event::fire('tohur.socialconnect.handleLogin', [
                        $provider_details, $provider_response, $user
                            ], true);
            if ($result)
                return $result;

            Auth::login($user);
        }

        return Redirect::to($success_redirect);
    }]);
