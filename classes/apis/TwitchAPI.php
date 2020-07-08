<?php

namespace Tohur\SocialConnect\Classes\Apis;

use October\Rain\Exception\ApplicationException;

class TwitchAPI
{

    /**
     * @var string Twitch Authencation Base URL
     */
    public $oAuthbaseUrl = 'https://id.twitch.tv/oauth2/token';

    /**
     * @var string Twitch Authencation Base URL
     */
    public $oRevokebaseUrl = 'https://id.twitch.tv/oauth2/revoke';

    /**
     * @var string Twitch Kraken API Base URL
     */
    public $krakenbaseUrl = 'https://api.twitch.tv/kraken';

    /**
     * @var string Twitch helix API Base URL
     */
    public $helixbaseUrl = 'https://api.twitch.tv/helix';

    /**
     * @var string Rest URL based on Toplist Type
     */
    public $typeUrl;

    /**
     * Do Helix API setup with given url
     *
     * @param string $url
     * @return string
     */
    function helixTokenRequest($url)
    {
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);

        curl_close($ch);
        return $result;
    }

    /**
     * Do Helix API setup with given url
     *
     * @param string $url
     * @return string
     */
    function helixApi($url, $acessToken = null)
    {
        $twitchAPISettings = \Tohur\SocialConnect\Models\Settings::instance()->get('providers', []);
        if (!strlen($twitchAPISettings['Twitch']['client_id']))
            throw new ApplicationException('Twitch API access is not configured. Please configure it on the Social Connect Settings Twitch tab.');
        $client_id = $twitchAPISettings['Twitch']['client_id'];
        $client_secret = $twitchAPISettings['Twitch']['client_secret'];
        $count = \DB::table('tohur_socialconnect_twitch_apptokens')->count();
        if ($count == 0) {
            $tokenRequest = json_decode($this->helixTokenRequest($this->oAuthbaseUrl . "?client_id=" . $client_id . "&client_secret=" . $client_secret . "&grant_type=client_credentials&scope=channel:read:hype_train%20channel:read:subscriptions%20bits:read%20user:read:broadcast%20user:read:email"), true);
            $accessToken = $tokenRequest['access_token'];
            $tokenExpires = $tokenRequest['expires_in'];
            \Db::table('tohur_socialconnect_twitch_apptokens')->insert([
                ['access_token' => $accessToken, 'expires_in' => $tokenExpires, 'created_at' => now()]
            ]);
            $token = $accessToken;
        } elseif ($acessToken != null) {
            $token = $acessToken;
        } else {
            $getToken = \DB::select('select * from tohur_socialconnect_twitch_apptokens where id = ?', array(1));
            $token = $getToken[0]->access_token;
        }

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Client-ID: ' . $client_id,
            'Authorization: Bearer ' . $token
        ));

        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }

    /**
     * Do kraken API Request with given url
     *
     * @param string $url
     * @return string
     */
    public function oldapiRequest($url)
    {
        $twitchAPISettings = \Tohur\SocialConnect\Models\Settings::instance()->get('providers', []);
        $client_id = $twitchAPISettings['Twitch']['client_id'];
        return file_get_contents($this->krakenbaseUrl . $url . "&client_id=" . $client_id . "&api_version=5");
    }

    /**
     * Get Videolist with given Type, Limit and Offset
     *
     * @param string $type
     * @param int $limit
     * @param int $offset
     * @return string
     */
    public function getVideoList($channel, $limit = 10, $offset = 0, $broadcastType = 'archive')
    {
        $user = $this->getUser($channel);
        $channelID = $user[0]['id'];
        $object = json_decode($this->helixApi($this->helixbaseUrl . "/videos?user_id=" . $channelID . "&first=" . $limit . "&type=" . $broadcastType), true);
        return $object['data'];
    }

    /**
     * Get Cliplist with given Type, Limit and Offset
     *
     * @param string $type
     * @param int $limit
     * @param int $offset
     * @return string
     */
    public function getClipList($channel, $limit = 10, $offset = 0, $period = 'all')
    {
        $user = $this->getUser($channel);
        $channelID = $user[0]['id'];
        $object = json_decode($this->helixApi($this->helixbaseUrl . "/clips?broadcaster_id=" . $channelID . "&first=" . $limit), true);
        return $object['data'];
    }

    /**
     * Get BitsLeaderboard with given Type, Limit and Offset
     *
     * @param string $type
     * @param int $limit
     * @param int $offset
     * @return string
     */
    public function getBitsLeaderboard($acessToken = null, $limit = 10, $period = 'all')
    {
        $object = json_decode($this->helixApi($this->helixbaseUrl . "/bits/leaderboard?count=" . $limit, $acessToken), true);
        return $object['data'];
    }

    /**
     * Get Current Channel info
     *
     * @param string $type
     * @param int $limit
     * @param int $offset
     * @return string
     */
    public function getChannelinfo($channel)
    {
        $user = $this->getUser($channel);
        $channelID = $user[0]['id'];
        $object = json_decode($this->helixApi($this->helixbaseUrl . "/channels?broadcaster_id=" . $channelID), true);
        return $object['data'];
    }

    /**
     * Get User
     *
     * @param string $type
     * @param int $limit
     * @param int $offset
     * @return string
     */
    public function getUser($channel)
    {
        $object = json_decode($this->helixApi($this->helixbaseUrl . "/users?login=" . $channel), true);
        return $object['data'];
    }

    /**
     * Get Channel Follow Count
     *
     * @param string $type
     * @param int $limit
     * @param int $offset
     * @return string
     */
    public function getFollowcount($channel)
    {
        $user = $this->getUser($channel);
        $channelID = $user[0]['id'];
        $object = json_decode($this->helixApi($this->helixbaseUrl . "/users/follows?to_id=" . $channelID), true);
        return $object['total'];
    }

    /**
     * Get Latest channel follower
     *
     * @param string $type
     * @param int $limit
     * @param int $offset
     * @return string
     */
    public function getLatestfollower($channel)
    {
        $user = $this->getUser($channel);
        $channelID = $user[0]['id'];
        $object = json_decode($this->helixApi($this->helixbaseUrl . "/users/follows?to_id=" . $channelID), true);
        return $object['data'][0]['from_name'];
    }

    /**
     * Get channel Stream Information
     *
     * @param string $type
     * @param int $limit
     * @param int $offset
     * @return string
     */
    public function getStream($channel)
    {
        $user = $this->getUser($channel);
        $channelID = $user[0]['id'];
        $object = json_decode($this->helixApi($this->helixbaseUrl . "/streams?user_id=" . $channelID), true);
        return $object['data'];
    }

    /**
     * Returns True of False whether the Channel is online or not
     *
     * @param string $channel Name of the Twitch Channel
     * @return bool
     */
    public function isChannelLive($channel)
    {
        $apiCall = $this->getStream($channel);
        if ($apiCall == null) {
            return false;
        } else {
            return true;
        }
    }

}
