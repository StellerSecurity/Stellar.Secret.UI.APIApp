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
    public function add(array $data): Response
    {
        $response = Http::withBasicAuth(getenv($this->usernameKey), getenv($this->passwordKey))
            ->post($this->baseUrl . "v1/secretcontroller/add", $data);
        return $response;
    }

    /**
     * @param string $id
     * @return PromiseInterface|Response
     */
    public function view(string $id): PromiseInterface|Response
    {
        $response = Http::withBasicAuth(getenv($this->usernameKey),getenv($this->passwordKey))
            ->get($this->baseUrl . "v1/secretcontroller/secret?id={$id}");
        return $response;
    }

    public function delete(string $id): PromiseInterface|Response
    {
        $response = Http::withBasicAuth(getenv($this->usernameKey),getenv($this->passwordKey))
            ->delete($this->baseUrl . "v1/wipeusercontroller/delete?id={$id}");
        return $response;
    }

}
