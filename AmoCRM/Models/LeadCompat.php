<?php

namespace Mcrm\Models\Api\AmoCRM\Models;

class LeadCompat {
    private $baseDomain;
    private $accessToken;

    public function __construct($baseDomain, $accessToken) {
        $this->baseDomain = $baseDomain;
        $this->accessToken = $accessToken;
    }

    public function apiList($parameters = []) {
        $query = [];

        // 1) Старый поиск: ['query' => $ttn]
        if(!empty($parameters['query'])) {
            $query['query'] = $parameters['query'];
        }

        // 2) Старый фильтр: ['date_create' => ['from'=>..., 'to'=>...]]
        if(!empty($parameters['date_create']) && is_array($parameters['date_create'])) {
            $from = Http::toTs($parameters['date_create']['from'] ?? null);
            $to   = Http::toTs($parameters['date_create']['to'] ?? null);

            // v4: filter[created_at][from|to] (unix timestamp) :contentReference[oaicite:2]{index=2}
            if($from) { $query['filter[created_at][from]'] = $from; }
            if($to) { $query['filter[created_at][to]'] = $to; }
        }

        // опционально: limit/page
        if(!empty($parameters['limit'])) { $query['limit'] = (int)$parameters['limit']; }
        if(!empty($parameters['page'])) { $query['page'] = (int)$parameters['page']; }

        $data = Http::request($this->baseDomain, $this->accessToken, 'GET', '/api/v4/leads', $query);

        return $data['_embedded']['leads'] ?? [];
    }

    private function request($method, $path, $query = [], $body = null) {
        $url = 'https://'.$this->baseDomain.$path;
        if(!empty($query)) {
            $url .= '?'.http_build_query($query);
        }

        $headers = [
            'Authorization: Bearer '.$this->accessToken,
            'Accept: application/hal+json',
            'Content-Type: application/json',
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        if($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        }

        $out = curl_exec($ch);
        $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);

        if($out === false) {
            throw new \Exception('cURL error: '.$err);
        }

        $json = json_decode($out, true);

        if($http >= 300) {
            // тут полезно логировать $out целиком
            throw new \Exception('amoCRM HTTP '.$http.': '.$out);
        }

        if(!is_array($json)) {
            throw new \Exception('amoCRM response is not JSON: '.$out);
        }

        return $json;
    }
}