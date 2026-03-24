<?php

namespace Mcrm\Models\Api\AmoCRM;

class ClientCompat {
    private $baseDomain;
    private $accessToken;

    public function __construct($baseDomain, $accessToken) {
        if(strpos($baseDomain, '.')===false) {
            $baseDomain = $baseDomain.'.amocrm.ru';
        }
        $this->baseDomain = $baseDomain;
        $this->accessToken = $accessToken;
    }

    public function __get($name){
        $name = strtolower($name);

        if($name === 'lead' || $name === 'leads'){
            return new Models\LeadCompat($this->baseDomain, $this->accessToken);
        }

        if($name === 'contact' || $name === 'contacts'){
            return new Models\ContactCompat($this->baseDomain, $this->accessToken);
        }

        if($name === 'account'){
            return new Models\AccountCompat($this->baseDomain, $this->accessToken);
        }

        throw new \Exception('Model not exists: '.$name);
    }
}