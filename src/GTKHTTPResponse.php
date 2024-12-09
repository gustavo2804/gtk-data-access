<?php 


class GTKHTTPResponse
{
    private int $httpCode;
    private string $responseBody;

    public function __construct(int $httpCode, string $responseBody)
    {
        $this->httpCode = $httpCode;
        $this->responseBody = $responseBody;
    }

    public function httpCode(): int
    {
        return $this->httpCode;
    }

    public function body(): string
    {
        return $this->responseBody;
    }

    public function json(): ?array
    {
        return json_decode($this->responseBody, true);
    }
}
