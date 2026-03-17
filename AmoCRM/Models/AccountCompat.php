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
}