<?php

namespace Tohur\SocialConnect\Classes\Apis;

use October\Rain\Exception\ApplicationException;

class TwitchAPI {

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
    function helixTokenRequest($url) {
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);

        curl_close($ch);
        return $result;
    }

    /**
     * Do Kraken API setup with given url
     *
     * @param string $url
     * @return string
     */
    function krakenApi($url, $acessToken = null, $bot = null) {
        if ($bot != null) {
            if (\Schema::hasTable('tohur_bot_owners')) {
                $botAPISettings = \Tohur\Bot\Models\Settings::instance()->get('bot', []);
                if (!strlen($botAPISettings['Twitch']['client_id']))
                    throw new ApplicationException('Twitch API access is not configured. Please configure it on the Social Connect Settings Twitch tab.');
                $client_id = $botAPISettings['Twitch']['client_id'];
                $client_secret = $botAPISettings['Twitch']['client_secret'];
            }
        } else {
            $twitchAPISettings = \Tohur\SocialConnect\Models\Settings::instance()->get('providers', []);
            if (!strlen($twitchAPISettings['Twitch']['client_id']))
                throw new ApplicationException('Twitch API access is not configured. Please configure it on the Social Connect Settings Twitch tab.');
            $client_id = $twitchAPISettings['Twitch']['client_id'];
            $client_secret = $twitchAPISettings['Twitch']['client_secret'];
        }
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
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Accept: application/vnd.twitchtv.v5+json',
            'Client-ID: ' . $client_id,
            'Authorization: OAuth ' . $token
        ));

        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }

    /**
     * Do Helix API setup with given url
     *
     * @param string $url
     * @return string
     */
    function krakenApiPost($url, $data, $acessToken = null, $bot = null) {
        if ($bot != null) {
            if (\Schema::hasTable('tohur_bot_owners')) {
                $botAPISettings = \Tohur\Bot\Models\Settings::instance()->get('bot', []);
                if (!strlen($botAPISettings['Twitch']['client_id']))
                    throw new ApplicationException('Twitch API access is not configured. Please configure it on the Social Connect Settings Twitch tab.');
                $client_id = $botAPISettings['Twitch']['client_id'];
                $client_secret = $botAPISettings['Twitch']['client_secret'];
            }
        } else {
            $twitchAPISettings = \Tohur\SocialConnect\Models\Settings::instance()->get('providers', []);
            if (!strlen($twitchAPISettings['Twitch']['client_id']))
                throw new ApplicationException('Twitch API access is not configured. Please configure it on the Social Connect Settings Twitch tab.');
            $client_id = $twitchAPISettings['Twitch']['client_id'];
            $client_secret = $twitchAPISettings['Twitch']['client_secret'];
        }
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
        $Postdata = $data;
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $Postdata);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Accept: application/vnd.twitchtv.v5+json',
            'Client-ID: ' . $client_id,
            'Authorization: OAuth ' . $token,
            'Content-Type: application/json'
        ));
        curl_exec ($ch);
        curl_close($ch);
    }

    /**
     * Do Helix API setup with given url
     *
     * @param string $url
     * @return string
     */
    function helixApi($url, $acessToken = null, $bot = null) {
        if ($bot != null) {
            if (\Schema::hasTable('tohur_bot_owners')) {
                $botAPISettings = \Tohur\Bot\Models\Settings::instance()->get('bot', []);
                if (!strlen($botAPISettings['Twitch']['client_id']))
                    throw new ApplicationException('Twitch API access is not configured. Please configure it on the Social Connect Settings Twitch tab.');
                $client_id = $botAPISettings['Twitch']['client_id'];
                $client_secret = $botAPISettings['Twitch']['client_secret'];
            }
        } else {
            $twitchAPISettings = \Tohur\SocialConnect\Models\Settings::instance()->get('providers', []);
            if (!strlen($twitchAPISettings['Twitch']['client_id']))
                throw new ApplicationException('Twitch API access is not configured. Please configure it on the Social Connect Settings Twitch tab.');
            $client_id = $twitchAPISettings['Twitch']['client_id'];
            $client_secret = $twitchAPISettings['Twitch']['client_secret'];
        }
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
     * Do Helix API setup with given url
     *
     * @param string $url
     * @return string
     */
    function helixApiPost($url, $data, $acessToken = null, $bot = null) {
        if ($bot != null) {
            if (\Schema::hasTable('tohur_bot_owners')) {
                $botAPISettings = \Tohur\Bot\Models\Settings::instance()->get('bot', []);
                if (!strlen($botAPISettings['Twitch']['client_id']))
                    throw new ApplicationException('Twitch API access is not configured. Please configure it on the Social Connect Settings Twitch tab.');
                $client_id = $botAPISettings['Twitch']['client_id'];
                $client_secret = $botAPISettings['Twitch']['client_secret'];
            }
        } else {
            $twitchAPISettings = \Tohur\SocialConnect\Models\Settings::instance()->get('providers', []);
            if (!strlen($twitchAPISettings['Twitch']['client_id']))
                throw new ApplicationException('Twitch API access is not configured. Please configure it on the Social Connect Settings Twitch tab.');
            $client_id = $twitchAPISettings['Twitch']['client_id'];
            $client_secret = $twitchAPISettings['Twitch']['client_secret'];
        }
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
        $Postdata = $data;
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $Postdata);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Client-ID: ' . $client_id,
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json'
        ));
        curl_exec ($ch);
        curl_close($ch);
    }

    /**
     * Get Videolist with given Type, Limit and Offset
     *
     * @param string $type
     * @param int $limit
     * @param int $offset
     * @return string
     */
    public function getVideoList($channel, $limit = 10, $offset = 0, $broadcastType = 'archive') {
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
    public function getClipList($channel, $limit = 10, $offset = 0, $period = 'all') {
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
    public function getBitsLeaderboard($acessToken = null, $bot = null, $limit = 10, $period = 'all') {
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
    public function getChannelinfo($channel) {
        $user = $this->getUser($channel);
        $channelID = $user[0]['id'];
        $object = json_decode($this->helixApi($this->helixbaseUrl . "/channels?broadcaster_id=" . $channelID), true);
        return $object['data'];
    }

    /**
     * Update Current Channel info
     *
     * @param string $type
     * @param int $limit
     * @param int $offset
     * @return string
     */
    public function updateChannelinfo($channel, $title, $game, $acessToken = null, $bot = null) {
        $user = $this->getUser($channel);
        $channelID = $user[0]['id'];
        $gamelookup = $this->getGame($game);
        $gameID = $gamelookup[0]['id'];
        $data = '{"game_id":"' . $gameID . '", "title":"' . $title . '", "broadcaster_language":"en"}';
        $post = $this->helixApiPost($this->helixbaseUrl . "/channels?broadcaster_id=" . $channelID, $data, $acessToken, $bot);
        return $post;
    }

    /**
     * Set Current Channel tags
     *
     * @param string $type
     * @param int $limit
     * @param int $offset
     * @return string
     */
    public function updateChanneltags($channel, $tags, $acessToken = null, $bot = null) {
        $user = $this->getUser($channel);
        $channelID = $user[0]['id'];
        $data = '{"tag_ids": ["621fb5bf-5498-4d8f-b4ac-db4d40d401bf","79977fb9-f106-4a87-a386-f1b0f99783dd"]}';
        $post = $this->helixApiPost($this->helixbaseUrl . "/streams/tags?broadcaster_id=" . $channelID, $data, $acessToken, $bot);
        return $post;
    }

    /**
     * Get User
     *
     * @param string $type
     * @param int $limit
     * @param int $offset
     * @return string
     */
    public function getUser($channel) {
        $object = json_decode($this->helixApi($this->helixbaseUrl . "/users?login=" . $channel), true);
        return $object['data'];
    }

    /**
     * Kraken Get User
     *
     * @param string $type
     * @param int $limit
     * @param int $offset
     * @return string
     */
    public function krakengetUser($channel) {
        $object = json_decode($this->krakenApi($this->krakenbaseUrl . "/users/".$channel), true);
        return $object;
    }

    /**
     * Get Game
     *
     * @param string $type
     * @param int $limit
     * @param int $offset
     * @return string
     */
    public function getGame($game) {
        $formated = urlencode($game);
        $object = json_decode($this->helixApi($this->helixbaseUrl . "/games?name=" . $formated), true);
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
    public function getSubcount($channel, $acessToken = null, $bot = null) {
        $user = $this->getUser($channel);
        $channelID = $user[0]['id'];
        $object = json_decode($this->krakenApi($this->krakenbaseUrl . "/channels/{$channelID}/subscriptions", $acessToken, $bot), true);
        return $object['_total'];
    }

    /**
     * Get Channel Follow Count
     *
     * @param string $type
     * @param int $limit
     * @param int $offset
     * @return string
     */
    public function getFollowcount($channel) {
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
    public function getLatestfollower($channel) {
        $user = $this->getUser($channel);
        $channelID = $user[0]['id'];
        $object = json_decode($this->helixApi($this->helixbaseUrl . "/users/follows?to_id=" . $channelID), true);
        return $object['data'][0]['from_name'];
    }

    /**
     * Returns the follow relationship between a channel ($toId) and user ($fromId).
     *
     * @param string $toId User ID of the channel
     * @param string $fromId User ID of the user.
     * @return string
     */
    public function getFollowRelationship($channel, $targetUser) {
        $user = $this->getUser($channel);
        $channelID = $user[0]['id'];
        $follower = $this->getUser($targetUser);
        $followerID = $follower[0]['id'];
        $object = json_decode($this->helixApi($this->helixbaseUrl . "/users/follows?to_id=" . $channelID . "&from_id=" . $followerID), true);
        return $object['data'];
    }

    /**
     * Get channel Stream Information
     *
     * @param string $type
     * @param int $limit
     * @param int $offset
     * @return string
     */
    public function getStream($channel) {
        $user = $this->getUser($channel);
        $channelID = $user[0]['id'];
        $object = json_decode($this->helixApi($this->helixbaseUrl . "/streams?user_id=" . $channelID), true);
        return $object['data'];
    }

    /**
     * Get Channel Chatters
     *
     * @param string $type
     * @param int $limit
     * @param int $offset
     * @return string
     */
    public function getChatusers($channel) {
        $url = 'https://tmi.twitch.tv/group/user/' . $channel . '/chatters';
        $json = file_get_contents($url);
        $object = json_decode($json, false);
        return $object;
    }

    /**
     * Get Channel Chatters
     *
     * @param string $type
     * @param int $limit
     * @param int $offset
     * @return string
     */
    public function getHosts($channel) {
        $user = $this->getUser($channel);
        $channelID = $user[0]['id'];
        $url = 'https://tmi.twitch.tv/hosts?include_logins=1&target=' . $channelID;
        $json = file_get_contents($url);
        $object = json_decode($json, true);
        return $object['hosts'];
    }

    /**
     * Returns the amount of channels that is currently hosting a channel (or an error message).
     *
     * @param  Request $request
     * @param  string  $channel
     * @return Response
     */
    public function hostscount($channel) {
        $hosts = $this->getHosts($channel);
        return count($hosts);
    }

    /**
     * Returns True of False whether the Channel is online or not
     *
     * @param string $channel Name of the Twitch Channel
     * @return bool
     */
    public function isChannelLive($channel) {
        $apiCall = $this->getStream($channel);
        if ($apiCall == null) {
            return false;
        } else {
            return true;
        }
    }

}
