<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class FilesecretService
{

    private string $baseUrl = "https://stellarsecretapiprod.azurewebsites.net/api/";

    private string $usernameKey = "APPSETTING_API_USERNAME_STELLAR_SECRET_API";

    private string $passwordKey = "APPSETTING_API_PASSWORD_STELLAR_SECRET_API";


    public function delete(array $fileIds) {
        $response = Http::retry(3, 100)->withBasicAuth(getenv($this->usernameKey), getenv($this->passwordKey))
            ->delete($this->baseUrl . "v1/filesecretcontroller/delete", ['fileIds' => $fileIds]);
        return $response;
    }

    public function find(array $fileIds) {
        $response = Http::retry(3, 100)->withBasicAuth(getenv($this->usernameKey), getenv($this->passwordKey))
            ->post($this->baseUrl . "v1/filesecretcontroller/find", ['fileIds' => $fileIds]);
        return $response;
    }

}
