<?php

namespace Tohur\SocialConnect;

use App;
use Event;
use URL;
use Illuminate\Foundation\AliasLoader;
use System\Classes\PluginBase;
use System\Classes\SettingsManager;
use RainLab\User\Models\User;
use RainLab\User\Models\UserGroup;
use Carbon\Carbon;
use RainLab\User\Controllers\Users as UsersController;
use Backend\Widgets\Form;
use Tohur\SocialConnect\Classes\Apis\TwitchAPI;
use Tohur\SocialConnect\Classes\ProviderManager;

/**
 * SocialConnect Plugin Information File
 *
 * http://www.mrcasual.com/on/coding/laravel4-package-management-with-composer/
 * https://cartalyst.com/manual/sentry-social
 *
 */
class Plugin extends PluginBase
{

    // Make this plugin run on updates page
    public $elevated = true;
    public $require = ['RainLab.User'];

    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name' => 'Social Connect',
            'description' => 'Allows visitors to register/sign in with their social media accounts',
            'author' => 'Joshua Webb',
            'icon' => 'icon-users'
        ];
    }

    public function registerSettings()
    {
        return [
            'settings' => [
                'label' => 'Social Connect',
                'description' => 'Manage Social Login providers.',
                'category' => SettingsManager::CATEGORY_USERS,
                'icon' => 'icon-users',
                'class' => 'Tohur\SocialConnect\Models\Settings',
                'order' => 600,
                'permissions' => ['rainlab.users.access_settings'],
            ]
        ];
    }

    public function registerComponents()
    {
        return [
            'Tohur\SocialConnect\Components\SocialConnect' => 'socialconnect',
        ];
    }

    /**
     * Register method, called when the plugin is first registered.
     * @return void
     */
    public function register()
    {
    }

    public function registerSchedule($schedule)
    {
        $schedule->call(function () {
            $twitch = new TwitchAPI();
            $twitchAPISettings = \Tohur\SocialConnect\Models\Settings::instance()->get('providers', []);
            if (!strlen($twitchAPISettings['Twitch']['client_id']))
                throw new ApplicationException('Twitch API access is not configured. Please configure it on the Social Connect Settings Twitch tab.');
            $client_id = $twitchAPISettings['Twitch']['client_id'];
            $client_secret = $twitchAPISettings['Twitch']['client_secret'];

            $count = \DB::table('tohur_socialconnect_providers')->count();
            if ($count == 0) {
                throw new ApplicationException('There are no twitch apptokens.');
            } else {
                $Tokens = \DB::table('tohur_socialconnect_providers')->get();
                foreach ($Tokens as $Token) {
                    if ($Token->provider_id == 'Twitch') {
                        $expiresIn = $Token->provider_expiresIn;
                        $current = Carbon::now();
                        if ($Token->updated_at == null) {
                            $time = $Token->created_at;
                        } else {
                            $time = $Token->updated_at;
                        }
                        $expired = Carbon::parse($time)->addSeconds($expiresIn);

                        if ($current > $expired) {
                            $tokenRequest = json_decode($twitch->helixTokenRequest($twitch->oAuthbaseUrl . "?grant_type=refresh_token&refresh_token=" . $Token->provider_refreshToken . "&client_id=" . $client_id . "&client_secret=" . $client_secret . ""), true);
                            $accessToken = $tokenRequest['access_token'];
                            $refreshToken = $tokenRequest['refresh_token'];
                            $tokenExpires = $expiresIn;
                            \Db::table('tohur_socialconnect_providers')
                                ->where('provider_id', '=', 'Twitch')
                                ->update(['provider_token' => $accessToken, 'provider_refreshToken' => $refreshToken, 'provider_expiresIn' => $tokenExpires, 'updated_at' => now()]);
                        }
                    } else {

                    }
                }
            }
        })->everyFiveMinutes();

        $schedule->call(function () {
            $twitch = new TwitchAPI();
            $twitchAPISettings = \Tohur\SocialConnect\Models\Settings::instance()->get('providers', []);
            if (!strlen($twitchAPISettings['Twitch']['client_id']))
                throw new ApplicationException('Twitch API access is not configured. Please configure it on the Social Connect Settings Twitch tab.');
            $client_id = $twitchAPISettings['Twitch']['client_id'];
            $client_secret = $twitchAPISettings['Twitch']['client_secret'];

            $count = \DB::table('tohur_socialconnect_twitch_apptokens')->count();
            if ($count == 0) {
                throw new ApplicationException('There are no twitch apptokens.');
            } else {
                $tokens = \DB::select('select * from tohur_socialconnect_twitch_apptokens where id = ?', array(1));
                $expiresIn = $tokens[0]->expires_in;
                $current = Carbon::now();
                if ($tokens[0]->updated_at == null) {
                    $time = $tokens[0]->created_at;
                } else {
                    $time = $tokens[0]->updated_at;
                }
                $expired = Carbon::parse($time)->addSeconds($expiresIn);

                if ($current > $expired) {
                    $revokeRequest = json_decode($twitch->helixTokenRequest($twitch->oRevokebaseUrl . "?client_id=" . $client_id . "&token=" . $tokens[0]->access_token . ""), true);
                    $tokenRequest = json_decode($twitch->helixTokenRequest($twitch->oAuthbaseUrl . "?grant_type=client_credentials&client_id=" . $client_id . "&client_secret=" . $client_secret . "&scope=channel:read:hype_train%20channel:read:subscriptions%20bits:read%20user:read:broadcast%20user:read:email"), true);
                    $accessToken = $tokenRequest['access_token'];
                    $tokenExpires = $tokenRequest['expires_in'];
                    \Db::table('tohur_socialconnect_twitch_apptokens')
                        ->where('id', 1)
                        ->update(['access_token' => $accessToken, 'expires_in' => $tokenExpires, 'updated_at' => now()]);
                }
            }
        })->weekly();
    }

    public function boot()
    {
        // Load socialite
        App::register(\SocialiteProviders\Manager\ServiceProvider::class);
        AliasLoader::getInstance()->alias('Socialite', 'Laravel\Socialite\Facades\Socialite');

        User::extend(function ($model) {
            $model->hasMany['tohur_socialconnect_providers'] = ['Tohur\SocialConnect\Models\Provider'];
        });

        User::extend(function ($model) {
            $model->addDynamicMethod('addUserGroup', function ($group) use ($model) {
                if ($group instanceof \October\Rain\Support\Collection) {
                    return $model->groups()->saveMany($group);
                }

                if (is_string($group)) {
                    $group = UserGroup::whereCode($group)->first();

                    return $model->groups()->save($group);
                }

                if ($group instanceof \RainLab\User\Models\UserGroup) {
                    return $model->groups()->save($group);
                }
            });
        });

        // Add 'Social Logins' column to users list
        UsersController::extendListColumns(function ($widget, $model) {
            if (!$model instanceof \RainLab\User\Models\User)
                return;

            $widget->addColumns([
                'tohur_socialconnect_user_providers' => [
                    'label' => 'Social Logins',
                    'type' => 'partial',
                    'path' => '~/plugins/tohur/socialconnect/models/provider/_provider_column.htm',
                    'searchable' => false
                ]
            ]);
        });

        // Generate Social Login settings form
        Event::listen('backend.form.extendFields', function (Form $form) {
            if (!$form->getController() instanceof \System\Controllers\Settings)
                return;
            if (!$form->model instanceof \Tohur\SocialConnect\Models\Settings)
                return;

            foreach (ProviderManager::instance()->listProviders() as $class => $details) {
                $classObj = $class::instance();
                $classObj->extendSettingsForm($form);
            }
        });

        // Add 'Social Providers' field to edit users form
        Event::listen('backend.form.extendFields', function ($widget) {
            if (!$widget->getController() instanceof \RainLab\User\Controllers\Users)
                return;
            if (!$widget->model instanceof \RainLab\User\Models\User)
                return;
            if (!in_array($widget->getContext(), ['update', 'preview']))
                return;

            $widget->addFields([
                'tohur_socialconnect_user_providers' => [
                    'label' => 'Social Providers',
                    'type' => 'Tohur\SocialConnect\FormWidgets\LoginProviders',
                ],
            ], 'secondary');
        });

        // Add backend login provider integration
        Event::listen('backend.auth.extendSigninView', function () {
            $providers = ProviderManager::instance()->listProviders();

            $social_connect_links = [];
            foreach ($providers as $provider_class => $provider_details)
                if ($provider_class::instance()->isEnabledForBackend())
                    $social_connect_links[$provider_details['alias']] = URL::route('tohur_socialconnect_provider', [$provider_details['alias']]) . '?s=' . Backend::url() . '&f=' . Backend::url('backend/auth/signin');

            if (!count($social_connect_links))
                return;

            require __DIR__ . '/partials/backend/_login.htm';
        });
    }

    function register_tohur_socialconnect_providers()
    {
        return [
            '\\Tohur\\SocialConnect\\SocialConnectProviders\\Facebook' => [
                'label' => 'Facebook',
                'alias' => 'Facebook',
                'description' => 'Log in with Facebook'
            ],
            '\\Tohur\\SocialConnect\\SocialConnectProviders\\Twitter' => [
                'label' => 'Twitter',
                'alias' => 'Twitter',
                'description' => 'Log in with Twitter'
            ],
            '\\Tohur\\SocialConnect\\SocialConnectProviders\\Google' => [
                'label' => 'Google',
                'alias' => 'Google',
                'description' => 'Log in with Google'
            ],
            '\\Tohur\\SocialConnect\\SocialConnectProviders\\Microsoft' => [
                'label' => 'Microsoft',
                'alias' => 'Microsoft',
                'description' => 'Log in with Microsoft'
            ],
            '\\Tohur\\SocialConnect\\SocialConnectProviders\\Discord' => [
                'label' => 'Discord',
                'alias' => 'Discord',
                'description' => 'Log in with Discord'
            ],
            '\\Tohur\\SocialConnect\\SocialConnectProviders\\Twitch' => [
                'label' => 'Twitch',
                'alias' => 'Twitch',
                'description' => 'Log in with Twitch'
            ],
        ];
    }

}
