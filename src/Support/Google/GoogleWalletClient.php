<?php

namespace Spatie\LaravelMobilePass\Support\Google;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Spatie\LaravelMobilePass\Exceptions\GoogleWalletRequestFailed;
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
        return $this->insertOrUpdate($resource, $id, $payload);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function insertObject(string $resource, string $id, array $payload): array
    {
        return $this->insertOrUpdate($resource, $id, $payload);
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

    /**
     * Replace the whole class. Fields absent from the payload are cleared on Google.
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function updateClass(string $resource, string $id, array $payload): array
    {
        return $this->update($resource, $id, $payload);
    }

    /**
     * Replace the whole object. Fields absent from the payload are cleared on Google.
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function updateObject(string $resource, string $id, array $payload): array
    {
        return $this->update($resource, $id, $payload);
    }

    /** @return array<string, mixed> */
    public function getClass(string $resource, string $id): array
    {
        return $this->get($this->resourcePath($resource, $id));
    }

    /** @return array<int, array<string, mixed>> */
    public function listClasses(string $resource): array
    {
        $issuerId = rawurlencode(GoogleCredentials::issuerId());

        $response = $this->get("/{$resource}?issuerId={$issuerId}");

        return $response['resources'] ?? [];
    }

    /**
     * Insert the resource, or fully replace it (PUT) when it already exists.
     *
     * A full update rather than a PATCH matters here: builders omit empty
     * fields from their payload, so a PATCH would never clear a field that
     * was removed since the previous save.
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    protected function insertOrUpdate(string $resource, string $id, array $payload): array
    {
        $endpoint = "/{$resource}";
        $response = $this->request()->post($this->url($endpoint), $payload + ['id' => $id]);

        if ($response->status() === 409) {
            return $this->update($resource, $id, $payload + ['id' => $id]);
        }

        return $this->parse($response, $endpoint);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    protected function update(string $resource, string $id, array $payload): array
    {
        $endpoint = $this->resourcePath($resource, $id);

        return $this->parse(
            $this->request()->put($this->url($endpoint), $payload),
            $endpoint
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    protected function patch(string $resource, string $id, array $payload): array
    {
        $endpoint = $this->resourcePath($resource, $id);

        return $this->parse(
            $this->request()->patch($this->url($endpoint), $payload),
            $endpoint
        );
    }

    protected function resourcePath(string $resource, string $id): string
    {
        return '/'.$resource.'/'.rawurlencode($id);
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
            throw GoogleWalletRequestFailed::fromResponse($response, $endpoint);
        }

        return $response->json() ?? [];
    }
}
