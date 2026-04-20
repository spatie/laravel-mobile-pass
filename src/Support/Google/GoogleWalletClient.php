<?php

namespace Spatie\LaravelMobilePass\Support\Google;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Spatie\LaravelMobilePass\Exceptions\GoogleWalletApiError;
use Throwable;

class GoogleWalletClient
{
    public function __construct(protected GoogleJwtSigner $signer) {}

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function insertClass(string $resource, string $id, array $payload): array
    {
        return $this->insertOrPatch($resource, $id, $payload);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function insertObject(string $resource, string $id, array $payload): array
    {
        return $this->insertOrPatch($resource, $id, $payload);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function patchClass(string $resource, string $id, array $payload): array
    {
        return $this->patch($resource, $id, $payload);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function patchObject(string $resource, string $id, array $payload): array
    {
        return $this->patch($resource, $id, $payload);
    }

    /** @return array<string, mixed> */
    public function getClass(string $resource, string $id): array
    {
        return $this->get("/{$resource}/{$id}");
    }

    /** @return array<string, mixed> */
    public function getObject(string $resource, string $id): array
    {
        return $this->get("/{$resource}/{$id}");
    }

    /** @return array<int, array<string, mixed>> */
    public function listClasses(string $resource): array
    {
        $issuerId = GoogleCredentials::issuerId();

        $response = $this->get("/{$resource}?issuerId={$issuerId}");

        return $response['resources'] ?? [];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    protected function insertOrPatch(string $resource, string $id, array $payload): array
    {
        $endpoint = "/{$resource}";
        $response = $this->request()->post($this->url($endpoint), $payload + ['id' => $id]);

        if ($response->status() === 409) {
            return $this->patch($resource, $id, $payload);
        }

        return $this->parse($response, $endpoint);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    protected function patch(string $resource, string $id, array $payload): array
    {
        $endpoint = "/{$resource}/{$id}";

        return $this->parse(
            $this->request()->patch($this->url($endpoint), $payload),
            $endpoint
        );
    }

    /** @return array<string, mixed> */
    protected function get(string $endpoint): array
    {
        return $this->parse($this->request()->get($this->url($endpoint)), $endpoint);
    }

    protected function request(): PendingRequest
    {
        return Http::withToken($this->signer->accessToken())
            ->acceptJson()
            ->retry(3, 200, fn (Throwable $exception) => $this->shouldRetry($exception), throw: false);
    }

    protected function shouldRetry(Throwable $exception): bool
    {
        if ($exception instanceof ConnectionException) {
            return true;
        }

        if ($exception instanceof RequestException) {
            return $exception->response->serverError();
        }

        return false;
    }

    protected function url(string $endpoint): string
    {
        return rtrim((string) config('mobile-pass.google.api_base_url'), '/').$endpoint;
    }

    /** @return array<string, mixed> */
    protected function parse(Response $response, string $endpoint): array
    {
        if ($response->failed()) {
            throw GoogleWalletApiError::fromResponse($response, $endpoint);
        }

        return $response->json() ?? [];
    }
}
