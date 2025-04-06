<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class FatSecretApiService
{
    private Client $client;
    private string $baseUrl = 'https://platform.fatsecret.com/rest';
    private string $oauthUrl = 'https://oauth.fatsecret.com/connect/token';

    public function __construct()
    {
        $this->client = new Client([
            'timeout' => 10,
            'http_errors' => false
        ]);
    }

    private function getAccessToken(): string
    {
        $response = $this->client->post($this->oauthUrl, [
            'form_params' => [
                'grant_type' => 'client_credentials',
                'client_id' => env('FAT_SECRET_CLIENT_ID'),
                'client_secret' => env('FAT_SECRET_CLIENT_SECRET')
            ]
        ]);

        $data = json_decode($response->getBody(), true);

        return $data['access_token'] ?? throw new \Exception('Failed to get token');
    }

    public function searchFood(string $query, int $page = 0, int $maxResults = 20): array
    {
        try {
            $token = $this->getAccessToken();

            $response = $this->client->get($this->baseUrl . '/foods/search/v1', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token
                ],
                'query' => [
                    'search_expression' => $query,
                    'page_number' => $page,
                    'max_results' => min($maxResults, 50),
                    'format' => 'json'
                ]
            ]);

            $data = json_decode($response->getBody(), true);

            return [
                'items' => array_map(function($item) {
                    return [
                        'id' => $item['food_id'],
                        'name' => $item['food_name'],
                        'brand' => $item['brand_name'] ?? null,
                        'description' => $item['food_description']
                    ];
                }, $data['foods']['food'] ?? [])
            ];

        } catch (GuzzleException $e) {
            throw new \Exception("API Error: " . $e->getMessage());
        }
    }
}
