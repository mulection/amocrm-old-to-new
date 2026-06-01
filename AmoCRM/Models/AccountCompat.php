<?php

namespace Mcrm\Models\Api\AmoCRM\Models;

class AccountCompat {
    private $baseDomain;
    private $accessToken;

    public function __construct($baseDomain, $accessToken) {
        $this->baseDomain = $baseDomain;
        $this->accessToken = $accessToken;
    }

    public function apiCurrent() {
        $account = Http::request($this->baseDomain, $this->accessToken, 'GET', '/api/v4/account');

        $usersResp = Http::request($this->baseDomain, $this->accessToken, 'GET', '/api/v4/users', ['limit' => 250]);

        $users = $usersResp['_embedded']['users'] ?? [];

        // Совместимость: как раньше
        $account['users'] = $users;

        return $account;
    }

    public function apiGetSourcesV4($query = []){
        return Http::request(
            $this->baseDomain,
            $this->accessToken,
            'GET',
            '/api/v4/sources',
            $query
        );
    }

    public function apiGetEventTypesV4(){
        return Http::request(
            $this->baseDomain,
            $this->accessToken,
            'GET',
            '/api/v4/events/types'
        );
    }

    public function apiGetEventByIdV4($event_id){
        return Http::request(
            $this->baseDomain,
            $this->accessToken,
            'GET',
            '/api/v4/events/' . $event_id
        );
    }

    public function apiGetChatHistory($scope_id, $conversation_id){
        return Http::amojoRequest(
            $this->baseDomain,
            $this->accessToken,
            'GET',
            '/v2/origin/custom/' . $scope_id . '/chats/' . $conversation_id . '/history'
        );
    }

    public function apiGetTalkByIdV4($talk_id){
        return Http::request(
            $this->baseDomain,
            $this->accessToken,
            'GET',
            '/api/v4/talks/' . (int)$talk_id
        );
    }

    public function apiChatConnect($channel_id, $channel_secret, $chat_account_id, $title = 'ChatIntegration'){
        $path = '/v2/origin/custom/' . $channel_id . '/connect';

        $body = [
            'account_id' => $chat_account_id,
            'title' => $title,
            'hook_api_version' => 'v2',
        ];

        return Http::amojoRequest('POST', $path, $body, $channel_secret);
    }
}