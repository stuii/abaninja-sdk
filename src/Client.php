<?php

    declare(strict_types=1);

    namespace Stui\AbaNinja;

    use Stui\AbaNinja\Enums\HttpMethod;
    use Stui\AbaNinja\Exceptions\AuthenticationException;
    use Stui\AbaNinja\Exceptions\ResponseException;

    class Client
    {
        public const BASE_URI = 'https://api.abaninja.ch';

        public function __construct(
            private string $apiKey
        )
        {
        }

        /**
         * @throws AuthenticationException
         * @throws ResponseException
         */
        public function send(string $url, array $data, HttpMethod $method): array
        {
            $url = str_starts_with($url, 'https') ? $url : self::BASE_URI . (
                str_starts_with($url, '/') ?: '/'
            );
            switch ($method) {
                case HttpMethod::DELETE:
                case HttpMethod::GET:
                    $url = $data !== [] ? $url . http_build_query($data) : $url;
                    break;
            }
            $apiRequest = curl_init($url);
            curl_setopt($apiRequest, CURLOPT_HTTPHEADER, array(
                    "Authorization: Bearer " . $this->getApiKey(),
                    "Content-Type: application/json"
                )
            );
            switch ($method){
                case HttpMethod::POST:
                    curl_setopt($apiRequest, CURLOPT_POST, true);
                    curl_setopt($apiRequest, CURLOPT_POSTFIELDS, json_encode($data));
                    break;
                case HttpMethod::DELETE:
                case HttpMethod::PATCH:
                case HttpMethod::PUT:
                    curl_setopt($apiRequest, CURLOPT_CUSTOMREQUEST, $method->value);
                    curl_setopt($apiRequest, CURLOPT_POSTFIELDS, json_encode($data));
                    break;
            }

            curl_setopt($apiRequest, CURLOPT_RETURNTRANSFER, 1);
            $response = curl_exec($apiRequest);
            $responseCode = curl_getinfo($apiRequest, CURLINFO_HTTP_CODE);

            if(json_encode(json_decode($response)) !== $response){
                throw new ResponseException('API returned invalid JSON response', 9901);
            }
            switch($responseCode){
                case 400:
                    var_dump($response);
                    throw new ResponseException('The sent request could not be understood by the API.', 9903);
                case 401:
                    throw new AuthenticationException('Could not authenticate. Please check your API key.', 9902);
                case 404:
                    throw new ResponseException('The requested resource could not be found by the API.', 9904);
            }

            return ['httpCode' => $responseCode, 'response' => json_decode($response, true)];
        }

        /**
         * @return string
         */
        private function getApiKey(): string
        {
            return $this->apiKey;
        }

        /**
         * @param string $apiKey
         */
        public function setApiKey(string $apiKey): void
        {
            $this->apiKey = $apiKey;
        }
    }