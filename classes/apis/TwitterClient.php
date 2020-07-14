<?php

namespace Tohur\SocialConnect\Classes\Apis;

use October\Rain\Exception\ApplicationException;
use Tohur\Twitter\TwitterAPI;

class TwitterClient {

    /**
     * @var string Twitter API Base URL
     */
    public $twitterbaseUrl = 'https://api.twitter.com/1.1/';

    /**
     * @var string Twitter API Base URL End
     */
    public $twitterendUrl = '.json';
    public $twitter;

    public function __construct() {
        $twitterAPISettings = \Tohur\SocialConnect\Models\Settings::instance()->get('providers', []);
        if (!strlen($twitterAPISettings['Twitter']['access_token']))
            throw new ApplicationException('Twitter API access is not configured. Please configure it on the Social Connect Settings Twitter tab.');
// settings for twitter api connection
        $settings = array(
            'oauth_access_token' => $twitterAPISettings['Twitter']['access_token'],
            'oauth_access_token_secret' => $twitterAPISettings['Twitter']['access_secret'],
            'consumer_key' => $twitterAPISettings['Twitter']['identifier'],
            'consumer_secret' => $twitterAPISettings['Twitter']['secret']
        );

        $this->twitter = new TwitterAPI($settings);
    }

    /**
     * Get Latest Tweet
     *
     * @param string $name
     * @return string
     */
    public function getLatesttweet($name) {
        $url = $this->twitterbaseUrl.'statuses/user_timeline'.$this->twitterendUrl;

// twitter api endpoint request type
        $requestMethod = 'GET';

// twitter api endpoint data
        $getfield = '?screen_name='.$name.'&count=1';

// make our api call to twitter
 
        $this->twitter->setGetfield($getfield);
        $this->twitter->buildOauth($url, $requestMethod);
        $response = $this->twitter->performRequest(true, array(CURLOPT_SSL_VERIFYHOST => 0, CURLOPT_SSL_VERIFYPEER => 0));
        $tweets = json_decode($response, true);
//        $object = json_decode($this->twitterApi($this->twitterbaseUrl . "statuses/user_timeline" . $this->twitterendUrl . "?count=1&screen_name=" . $name), true);
        return $tweets;
    }

    /**
     * Post a Tweet with a possible attachment
     *
     * @param string $text
     * @param string $attachment
     * @return string
     */
    public function posttweet($text, $attachment) {
        $url = $this->twitterbaseUrl.'statuses/update'.$this->twitterendUrl;

// twitter api endpoint request type
        $requestMethod = 'POST';

// twitter api endpoint data
        $apiData = array(
            'status' => $text,
        );

// make our api call to twitter

        $this->twitter->buildOauth( $url, $requestMethod );
        $this->twitter->setPostfields( $apiData );
        $response = $this->twitter->performRequest( true, array( CURLOPT_SSL_VERIFYHOST => 0, CURLOPT_SSL_VERIFYPEER => 0 ) );
        return $response;
    }
}
