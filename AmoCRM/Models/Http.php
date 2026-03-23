<?php

namespace Mcrm\Models\Api\AmoCRM\Models;

class Http {
    public static function request($baseDomain, $accessToken, $method, $path, $query = [], $body = null) {
        $url = 'https://'.$baseDomain.$path;
        if(!empty($query)) {
            $url .= '?'.http_build_query($query);
        }

        $headers = [
            'Authorization: Bearer '.$accessToken,
            'Accept: application/hal+json',
            'Content-Type: application/json',
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_HEADER, true); // важно: вернем заголовки+тело
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        // ВАЖНО для macOS/XAMPP: чтобы не было "пустого ответа" из-за SSL
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

        if($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body, JSON_UNESCAPED_UNICODE));
        }

        $out = curl_exec($ch);
        $errno = curl_errno($ch);
        $err = curl_error($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);

        if($out===false) {
            throw new \Exception('cURL error '.$errno.': '.$err.' URL: '.$url);
        }

        $headerSize = $info['header_size'] ?? 0;
        $rawHeaders = substr($out, 0, $headerSize);
        $rawBody = substr($out, $headerSize);

        $http = (int)($info['http_code'] ?? 0);

        if($http===204) {
            return [];
        }

        $json = json_decode($rawBody, true);

        if($http >= 300) {
            throw new \Exception('amoCRM bad response. HTTP='.$http.' URL='.$url.' Headers='.trim($rawHeaders).' Body='.trim($rawBody));
        }

        if(!is_array($json)) {
            throw new \Exception('amoCRM response is not JSON. HTTP='.$http.' URL='.$url.' Body='.trim($rawBody));
        }

        return $json;
    }

    public static function toTs($value) {
        if($value===null || $value==='') {
            return null;
        }
        if(is_int($value) || ctype_digit((string)$value)) {
            return (int)$value;
        }
        $ts = strtotime((string)$value);
        return $ts ? $ts : null;
    }
}