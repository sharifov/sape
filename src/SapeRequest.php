<?php

namespace App;

class SapeRequest {
	
	public array $data;
	private const BASE_URL = 'https://api-links.sape.ru/';
	private static self $self; 
	private \CurlHandle $ch;
	private string $auth_cookie;
	private string $token;
	
	private function __construct()
	{
		//
	}
	
	public static function init(string $auth_cookie): static {
		if (!isset(static::$self)) {
			static::$self = new static;
		}
		static::$self->auth_cookie = $auth_cookie;
		if (!isset(static::$self->ch)) {
			static::$self->ch = curl_init();
			curl_setopt(static::$self->ch, CURLOPT_RETURNTRANSFER, TRUE);
		}
		return static::$self;
    }
	
	private function setToken(): void
	{
		if (!empty($this->data['token'])) {
			$this->token = $this->data['token'];
		}
	}
	
	public function send(string $url): void
	{
		$this->ch = curl_init();
		$headers = [];
		if ($this->auth_cookie) {
			$headers[] = 'cookie: AUTH_TICKET=' . $this->auth_cookie;
		}
		if (isset($this->token)) {
			$headers[] = 'Authorization: Bearer ' . $this->token;
		}
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($this->ch, CURLOPT_URL, self::BASE_URL . $url);
		curl_setopt($this->ch, CURLOPT_HTTPHEADER, $headers);
		$this->data = json_decode(curl_exec($this->ch), true);
		curl_close($this->ch);
	}
	
	public function __call(string $url, array $args): void
	{
		if ($url) {
			$url = str_replace('_', '/', $url);
			$this->send($url);
			if ($args) {
				foreach ($args as $arg) {
					if (method_exists(__CLASS__, $arg)) {
						$this->{$arg}();
					}
				}
			}
		}
	}
}