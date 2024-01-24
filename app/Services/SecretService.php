<?php

namespace App\Services;

use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;


class SecretService
{

    private $baseUrl = "https://stellarsecretapiprod.azurewebsites.net/api/";

    private $usernameKey = "APPSETTING_API_USERNAME_STELLAR_SECRET_API";

    private $passwordKey = "APPSETTING_API_PASSWORD_STELLAR_SECRET_API";

    /**
     * @param string $id
     * @param string $type
     * @return Response
     */
    public function add(string $username, string $password, string $auth_token): Response
    {
        $response = Http::withBasicAuth(getenv($this->usernameKey), getenv($this->passwordKey))
            ->post($this->baseUrl . "v1/wipeusercontroller/add",
                ['username' => $username, 'password' => $password, 'auth_token' => $auth_token]);
        return $response;
    }

    /**
     * @param string $auth_token
     * @return PromiseInterface|Response
     */
    public function findbytoken(string $auth_token): PromiseInterface|Response
    {
        $response = Http::withBasicAuth(getenv($this->usernameKey),getenv($this->passwordKey))
            ->get($this->baseUrl . "v1/wipeusercontroller/findbytoken?auth_token=" . $auth_token);
        return $response;
    }

    public function patch(array $data): PromiseInterface|Response
    {
        $response = Http::withBasicAuth(getenv($this->usernameKey),getenv($this->passwordKey))
            ->patch($this->baseUrl . "v1/wipeusercontroller/patch", $data);
        return $response;
    }

}
