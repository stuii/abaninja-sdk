<?php

    declare(strict_types=1);

    namespace Stui\AbaNinja;

    use JetBrains\PhpStorm\ArrayShape;
    use Stui\AbaNinja\Enums\HttpMethod;
    use Stui\AbaNinja\Exceptions\AuthenticationException;
    use Stui\AbaNinja\Exceptions\ResponseException;
    use Stui\AbaNinja\Exceptions\ScopeException;
    use Stui\AbaNinja\Exceptions\UnexpectedErrorException;

    class Client
    {
        public function __construct(
            private string $apiKey,
            private readonly string $baseUrl = 'https://api.abaninja.ch'
        )
        {
        }

        /**
         * @throws AuthenticationException
         * @throws ResponseException
         * @throws ScopeException
         * @throws UnexpectedErrorException
         */
        #[ArrayShape(['httpCode' => 'int', 'response' => 'stdClass'])]
        public function send(string $url, array $data = [], HttpMethod $method = HttpMethod::GET): array
        {
            $url = str_starts_with($url, 'https') ? $url : $this->baseUrl . (
                (str_starts_with($url, '/') ? '' : '/') . $url
            );
            switch ($method) {
                case HttpMethod::DELETE:
                case HttpMethod::GET:
                    $url = $data !== [] ? $url . '?' . http_build_query($data) : $url;
                    break;
            }
            $apiRequest = curl_init($url);
            curl_setopt($apiRequest, CURLOPT_HTTPHEADER, [
                    "Authorization: Bearer " . $this->getApiKey(),
                    "Content-Type: application/json"
                ]
            );
            switch ($method) {
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

            if (is_string($response)) {
                if (json_encode(json_decode($response)) !== $response) {
                    throw new ResponseException('API returned invalid JSON response', 9901);
                }
                $response = json_decode($response);
            }
            switch ($responseCode) {
                case 401:
                    throw new AuthenticationException('Could not authenticate. Please check your API token.', 9902);
                case 403:
                    throw new ScopeException('The provided API token does not fulfill the required scope requirements.', 9903);
                case 404:
                    throw new ResponseException('The requested resource could not be found by the API.', 9904);
                case 500:
                    $e = new UnexpectedErrorException('The API returned an unexpected error.', 9905);
                    $e->setData($response);
                    throw $e;
            }

            return ['httpCode' => $responseCode, 'response' => $response];
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