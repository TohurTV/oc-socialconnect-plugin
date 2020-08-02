<?php

namespace Tohur\SocialConnect\Classes\Apis;

use October\Rain\Exception\ApplicationException;

class StreamElementsAPI {

    /**
     * @var string StreamElements Authencation Base URL
     */
    public $oAuthbaseUrl = 'https://api.streamelements.com/oauth2/authorize';

    /**
     * @var string StreamElements Token Base URL
     */
    public $oTokenbaseUrl = 'https://api.streamelements.com/oauth2/token';

    /**
     * @var string StreamElements Token revoke Base URL
     */
    public $oRevokebaseUrl = 'https://api.streamelements.com/oauth2/token/revoke';

    /**
     * @var string StreamElements kappa API Base URL
     */
    public $kappabaseUrl = 'https://api.streamelements.com/kappa/v2';

    /**
     * Do Kappa API setup with given url
     *
     * @param string $url
     * @return string
     */
    function kappaApi($url) {
        $APISettings = \Tohur\SocialConnect\Models\Apisettings::instance()->get('api', []);
        if (!strlen($APISettings['Streamelements']['streamelementskey']))
            throw new ApplicationException('Streamelements API access is not configured. Please configure it on the API Settings page.');
        $token = $APISettings['Streamelements']['streamelementskey'];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: Bearer ' . $token,
            'Accept: application/json'
        ));

        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }

    /**
     * Do Kappa API setup with given url
     *
     * @param string $url
     * @return string
     */
    function kappaApiPost($url, $data) {
        $APISettings = \Tohur\SocialConnect\Models\Apisettings::instance()->get('api', []);
        if (!strlen($APISettings['Streamelements']['streamelementskey']))
            throw new ApplicationException('Streamelements API access is not configured. Please configure it on the API Settings page.');
        $token = $APISettings['Streamelements']['streamelementskey'];
        $Postdata = $data;
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $Postdata);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: Bearer ' . $token,
            'Accept: application/json',
            'Content-Type: text/plain'
        ));
        curl_exec($ch);
        curl_close($ch);
    }

}
