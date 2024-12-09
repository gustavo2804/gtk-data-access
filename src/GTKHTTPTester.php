<?php

class GTKHTTPTester
{
    private string $host;
    private string $path = '';
    private string $method = 'GET';
    private array $queryParameters = [];
    private array $body = [];
    private array $headers = [];

    public function __construct(string $configKey)
    {
        if (strpos($configKey, 'http') != -1) 
        {
            $this->host = $configKey;
        }
        else
        {
            global $_GLOBALS;
            if (!isset($_GLOBALS[$configKey])) {
                throw new RuntimeException("Configuration key '$configKey' not found in globals");
            }
            $this->host = $_GLOBALS[$configKey];
        }
    }

    public function setPath(string $path): self
    {
        $this->path = $path;
        return $this;
    }

    public function setMethod(string $method): self
    {
        $this->method = strtoupper($method);
        return $this;
    }

    public function addQueryParameter(string $key, string $value): self
    {
        $this->queryParameters[$key] = $value;
        return $this;
    }

    public function setBody(array $body): self
    {
        $this->body = $body;
        return $this;
    }

    public function addHeader(string $key, string $value): self
    {
        $this->headers[$key] = $value;
        return $this;
    }

    public function request(): GTKHTTPResponse
    {
        $url = $this->buildUrl();
        
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $this->method);
        
        $formattedHeaders = [];
        
        if (!empty($this->headers)) {
            $formattedHeaders = [];
            foreach ($this->headers as $key => $value) {
                $formattedHeaders[] = "$key: $value";
            }
        }
        
        if (!empty($this->body)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($this->body));

            if (!isset($this->headers['Content-Type'])) 
            {
                $formattedHeaders[] = 'Content-Type: application/json';
            }

            if (!isset($this->headers['Content-Length'])) 
            {
                $formattedHeaders[] = 'Content-Length: ' . strlen(json_encode($this->body));
            }

            if (!isset($this->headers['Accept'])) 
            {
                $formattedHeaders[] = 'Accept: application/json';
            }
        }

        if (!empty($formattedHeaders)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $formattedHeaders);
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        curl_close($ch);
        
        if ($error) {
            throw new RuntimeException("cURL Error: $error");
        }
        
        return new GTKHTTPResponse($httpCode, $response);
    }

    private function buildUrl(): string
    {
        $url = rtrim($this->host, '/') . '/' . ltrim($this->path, '/');
        
        if (!empty($this->queryParameters)) {
            $url .= '?' . http_build_query($this->queryParameters);
        }
        
        return $url;
    }
}
