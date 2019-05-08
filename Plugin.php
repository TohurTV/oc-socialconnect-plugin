<?php

namespace Tohur\SocialConnect;

use App;
use Backend;
use Event;
use URL;
use Illuminate\Foundation\AliasLoader;
use System\Classes\PluginBase;
use RainLab\User\Models\User;
use RainLab\User\Controllers\Users as UsersController;
use Backend\Widgets\Form;
use Tohur\SocialConnect\Classes\ProviderManager;

/**
 * SocialConnect Plugin Information File
 *
 * http://www.mrcasual.com/on/coding/laravel4-package-management-with-composer/
 * https://cartalyst.com/manual/sentry-social
 *
 */
class Plugin extends PluginBase {

    // Make this plugin run on updates page
    public $elevated = true;
    public $require = ['RainLab.User'];

    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails() {
        return [
            'name' => 'Social Connect',
            'description' => 'Allows visitors to register/sign in with their social media accounts',
            'author' => 'Tohur',
            'icon' => 'icon-users'
        ];
    }

    public function registerSettings() {
        return [
            'settings' => [
                'label' => 'Social Connect',
                'description' => 'Manage Social Login providers.',
                'icon' => 'icon-users',
                'class' => 'Tohur\SocialConnect\Models\Settings',
                'order' => 600,
                'permissions' => ['rainlab.users.access_settings'],
            ]
        ];
    }

    public function registerComponents() {
        return [
            'Tohur\SocialConnect\Components\SocialConnect' => 'socialconnect',
        ];
    }

    public function boot() {
        // Load socialite
        App::register(\SocialiteProviders\Manager\ServiceProvider::class);
        AliasLoader::getInstance()->alias('Socialite', 'Laravel\Socialite\Facades\Socialite');

        User::extend(function($model) {
            $model->hasMany['tohur_socialconnect_providers'] = ['Tohur\SocialConnect\Models\Provider'];
        });

        // Add 'Social Logins' column to users list
        UsersController::extendListColumns(function($widget, $model) {
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
        Event::listen('backend.form.extendFields', function(Form $form) {
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
        Event::listen('backend.form.extendFields', function($widget) {
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
        Event::listen('backend.auth.extendSigninView', function() {
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

    function register_tohur_socialconnect_providers() {
        return [
            '\\Tohur\\SocialConnect\\SocialConnectProviders\\Google' => [
                'label' => 'Google',
                'alias' => 'Google',
                'description' => 'Log in with Google'
            ],
            '\\Tohur\\SocialConnect\\SocialConnectProviders\\Twitter' => [
                'label' => 'Twitter',
                'alias' => 'Twitter',
                'description' => 'Log in with Twitter'
            ],
            '\\Tohur\\SocialConnect\\SocialConnectProviders\\Facebook' => [
                'label' => 'Facebook',
                'alias' => 'Facebook',
                'description' => 'Log in with Facebook'
            ],
            '\\Tohur\\SocialConnect\\SocialConnectProviders\\Discord' => [
                'label' => 'Discord',
                'alias' => 'Discord',
                'description' => 'Log in with Discord'
            ],
        ];
    }

}
