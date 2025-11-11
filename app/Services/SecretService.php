<?php

namespace App\Services;

use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;


class SecretService
{

    private string $baseUrl = "https://stellarsecretapiprod.azurewebsites.net/api/";

    private string $usernameKey = "APPSETTING_API_USERNAME_STELLAR_SECRET_API";

    private string $passwordKey = "APPSETTING_API_PASSWORD_STELLAR_SECRET_API";

    /**
     * @param array $data
     * @return Response
     */
    public function add(array $data): Response
    {
        $response = Http::retry(5, 100)->withBasicAuth(getenv($this->usernameKey), getenv($this->passwordKey))
            ->post($this->baseUrl . "v1/secretcontroller/add", $data);
        return $response;
    }

    /**
     * @param string $id
     * @return PromiseInterface|Response
     */
    public function view(string $id): PromiseInterface|Response
    {
        $response = Http::retry(5, 100)->withBasicAuth(getenv($this->usernameKey),getenv($this->passwordKey))
            ->get($this->baseUrl . "v1/secretcontroller/secret?id={$id}");
        return $response;
    }

    public function delete(string $id): PromiseInterface|Response
    {
        $response = Http::retry(5, 100)->withBasicAuth(getenv($this->usernameKey),getenv($this->passwordKey))
            ->delete($this->baseUrl . "v1/secretcontroller/delete", ['id' => $id]);
        return $response;
    }

}
