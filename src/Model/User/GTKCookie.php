<?php

class GTKCookie {
    // Time constants
    public const MINUTE = 60;
    public const HOUR = 3600;
    public const DAY = 86400;
    public const WEEK = 604800;
    public const MONTH = 2592000;  // 30 days

    // Cookie names
    public const COOKIE_SESSION = 'gtk_session';
    public const COOKIE_USER_ID = 'gtk_user_id';
    public const COOKIE_REMEMBER = 'gtk_remember';

    // Domain types
    public const DOMAIN_ROOT  = true;
    public const DOMAIN_EXACT = false;

    // The SameSite attribute can have three possible values:
	// 
	// 	"Strict"  : Not sent in cross-site requests. It means the cookie is only sent if the request originates from the same site as the domain that set the cookie. This provides a high level of protection against CSRF attacks but may impact functionality in scenarios where legitimate cross-site requests are needed.
	// 	"Lax"	  : Not sent in cross-site requests initiated by external websites through HTTP methods other than "GET". For example, cookies will be sent in cross-site requests that are triggered by clicking on links or loading images from external sites. This provides some protection against CSRF attacks while maintaining compatibility with common scenarios.
	// 	"None"	  : Sent in all cross-site requests. This value is typically used in conjunction with the "Secure" attribute, indicating that the cookie should only be sent over HTTPS connections. This allows the cookie to be sent in cross-site requests that are essential for certain functionalities, such as embedded content or OAuth flows. However, it should be used with caution and proper security measures to prevent abuse.
    public const SAMESITE_STRICT = 'Strict';
    public const SAMESITE_LAX    = 'Lax';
    public const SAMESITE_NONE   = 'None';

    // Default values
    private bool   $_setForRootDomain         = true;
    private int    $defaultExpiry            = self::HOUR;
    private string $path                     = '/';
    private bool   $secure                   = true; // Secure means the cookie is only sent over HTTPS connections
    private bool   $_canAccessWithJavascript = false;
    private string $sameSite                 = self::SAMESITE_STRICT;
    private int    $rememberMeExpiry         = self::MONTH;

    public function __construct() 
    {
    }

    public function setSecure(bool $secure): self
    {
        $this->secure = $secure;
        return $this;
    }

    public function setPath(string $path): self
    {
        $this->path = $path;
        return $this;
    }

    public function setSameSite(string $sameSite): self
    {
        $this->sameSite = $sameSite;
        return $this;
    }
    
    

    /**
     * Extract root domain from host
     */
    private function getRootDomain(string $host): string {
        // Remove port number if present
        $host = preg_replace('/:\d+$/', '', $host);

        // Special handling for .local domains
        if (str_ends_with($host, '.local')) 
        {
            return '.' . $host;
        }

        $parts = explode('.', $host);
        $count = count($parts);
        
        $specialTlds = [
            'co.uk', 
            'com.au', 
            'co.nz', 
            ".do", 
            ".com.do",
            ".net.do",
            ".org.do",
            ".web.do",
            ".io",
            ".es",
            ".com.es",
            ".net.es",
            ".org.es",
            ".web.es",
            ".info",
            ".com.info",
            ".net.info",
            ".org.info",
            ".web.info",
            ".biz",
            ".com.biz",
            ".net.biz",
            ".org.biz",
        ];

        if ($count > 2 && in_array($parts[$count-2] . '.' . $parts[$count-1], $specialTlds)) {
            return '.' . $parts[$count-3] . '.' . $parts[$count-2] . '.' . $parts[$count-1];
        }
        
        // Standard cases
        return '.' . implode('.', array_slice($parts, -2));
    }

    public function setForRootDomain(bool $useRootDomain): self 
    {
        $this->_setForRootDomain = $useRootDomain;
        return $this;
    }

    public function setRememberMeFor(int|bool $duration = true): self {
        if (is_bool($duration) && $duration) 
        {
            $this->rememberMeExpiry = self::MONTH;
        } 
        elseif (is_int($duration)) 
        {
            $this->rememberMeExpiry = $duration;
        }
        return $this;
    }

    public function setCanAccessWithJavascript(bool $canAccessWithJavascript): self
    {
        $this->_canAccessWithJavascript = $canAccessWithJavascript;
        return $this;
    }

    public function set(string $name, string $value, $options = []): bool {
        $debug = true;

        $expiry = $options["expires"] ?? time() + $this->defaultExpiry;


        $httponly = $options["httponly"] ?? $this->_canAccessWithJavascript;

        $samesite = $options["samesite"] ?? $this->sameSite;

        $domain = null;

        if ($this->_setForRootDomain)
        {
            $domain = $this->getRootDomain($_SERVER['HTTP_HOST']);
        } 
        else 
        {
            $domain = $_SERVER['HTTP_HOST'];
        }

        if ($this->_canAccessWithJavascript)
        {
            $httponly = false;
        }
        else
        {
            $httponly = true;
        }

        // Determine if connection is secure based on HTTPS or secure port
        $isSecureConnection = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
                (isset($_SERVER['SERVER_PORT']) && in_array($_SERVER['SERVER_PORT'], [443, 4433]));

        $secure = $options["secure"] ?? $isSecureConnection;


       // print_r($expiry);
        //die();

        $useOptions = false;
        $success    = false;

        if ($debug)
        {
            gtk_log("GTKCookie::set - Setting cookie: $name");
            gtk_log("GTKCookie::set - Value: $value");
            gtk_log("GTKCookie::set - Expiry: $expiry");
            gtk_log("GTKCookie::set - Path: $this->path");
            gtk_log("GTKCookie::set - Domain: $domain");
            gtk_log("GTKCookie::set - Secure: $secure");
            gtk_log("GTKCookie::set - HttpOnly: $httponly");
            gtk_log("GTKCookie::set - SameSite: $samesite");
        }

        $success = setcookie(
            $name,
            $value,
            $expiry,
            $this->path,
            $domain,
                $secure,
                $httponly,
            );
        

        if (!$success)
        {
            error_log("GTKCookie::set - Failed to set cookie: $name");
            throw new Exception("Failed to set cookie: $name");
        }

        return $success;
    }

    public function delete(string $name): bool {
        return $this->set($name, '', time() - self::HOUR);
    }

    public static function deleteCurrentSessionCookie()
	{
		unset($_COOKIE['AuthCookie']);
		setcookie('AuthCookie', '', -1, '/'); 
	}

    public function clearSession(): bool {
        $success = true;
        $success &= $this->delete(self::COOKIE_SESSION);
        $success &= $this->delete(self::COOKIE_USER_ID);
        $success &= $this->delete(self::COOKIE_REMEMBER);
        return $success;
    }

    public function getSessionId(): ?string {
        return $_COOKIE[self::COOKIE_SESSION] ?? null;
    }

    public function getUserId(): ?string {
        return $_COOKIE[self::COOKIE_USER_ID] ?? null;
    }

    public function setOptions(array $options): self {
        if (isset($options['defaultExpiry'])) { $this->defaultExpiry = $options['defaultExpiry']; }
        if (isset($options['path']))          { $this->path = $options['path'];                   }
        if (isset($options['secure']))        { $this->secure = $options['secure'];               }
        if (isset($options['httpOnly']))      { $this->httpOnly = $options['httpOnly'];           }
        if (isset($options['sameSite']))      { $this->sameSite = $options['sameSite'];           }
        return $this;
    }

    public static function getAuthCookie()
    {
        return $_COOKIE['AuthCookie'] ?? null;
    }

    public static function setAuthCookie(string $value, ?int $expiry = null)
    {
        $expiry = $expiry ?? time() + 60 * 60 * 24 * 30; // 30 days

        $gtkCookie = new GTKCookie();

        // print_r($expiry);
        //die();

        return $gtkCookie->set("AuthCookie", $value, [
            'expires'   => $expiry,
            'path' 	    => '/', 
            'secure'    => true, // consider not using secure cookies in development
            'httponly'  => true,
            'samesite'  => 'Strict'
        ]);
    }

    public static function clearAuthCookie()
    {
        unset($_COOKIE['AuthCookie']);
		setcookie('AuthCookie', '', -1, '/'); 
    }

    public static function clearAllCookies()
    {
		// Loop through all cookies and unset them
		foreach ($_COOKIE as $cookie_name => $cookie_value) 
		{
			setcookie($cookie_name, "", time() - 3600, "/");
		}
    }
}