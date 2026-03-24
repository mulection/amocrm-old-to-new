<?php

namespace Mcrm\Models\Api\AmoCRM\Models;

class ContactCompat{
    private $baseDomain;
    private $accessToken;

    public function __construct($baseDomain, $accessToken){
        $this->baseDomain = $baseDomain;
        $this->accessToken = $accessToken;
    }

    public function apiGetChatsV4($contact_id, $channel = null){
        $query = [
            'filter[contact_id]' => (int)$contact_id
        ];

        if($channel){
            $query['filter[channel]'] = $channel;
        }

        return Http::request(
            $this->baseDomain,
            $this->accessToken,
            'GET',
            '/api/v4/contacts/chats',
            $query
        );
    }

    public function apiGetByIdV4($contact_id, $with = []){
        $query = [];

        if(!empty($with)){
            $query['with'] = implode(',', $with);
        }

        return Http::request(
            $this->baseDomain,
            $this->accessToken,
            'GET',
            '/api/v4/contacts/' . (int)$contact_id,
            $query
        );
    }

    public function apiGetEventsV4($contact_id, $page = 1, $limit = 50){
        $query = [
            'page' => (int)$page,
            'limit' => (int)$limit,
            'filter[entity]' => 'contact',
            'filter[entity_id]' => (int)$contact_id
        ];

        return Http::request(
            $this->baseDomain,
            $this->accessToken,
            'GET',
            '/api/v4/events',
            $query
        );
    }
}