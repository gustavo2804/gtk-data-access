<?php

class GTKSingletonClass
{

}

class Glang extends GTKSingletonClass
{
    public $translations;
    public $language;
    public static function getSingleton()
    {
        static $singleton = null;

        if (!$singleton)
        {
            $singleton = new self(null);
        }

        return $singleton;
    }
    public static function setLanguage($lang)
    {
        self::getSingleton($lang)->language = $lang;
    }
    public static function get($key, $options = null, $lang = null)
    {
        return self::getSingleton()->internalGet($key, $options, $lang);
    }
    public function __construct($translations)
    {
        global $_GLOBALS;

        if (!$translations)
        {
            $translations = $_GLOBALS["languages"];
        }

        if (isset($_GLOBALS["default_language"]))
        {
            $this->language = $_GLOBALS["default_language"];
        }

        if (!$translations)
        {
            error_log("Starting translation without key");
            $translations = [];
        }

        $this->translations = $translations;

        return $this;
    }
    public function internalGet($key, $options = null, $lang = null)
    {
        $debug = false;

        $lookupLang  = null;
        $translation = null;

        
        if ($lang)
        {
            $lookupLang = $lang;
        }
        else
        {
            $lookupLang = $this->language;
        }

        if (!isset($this->translations[$lookupLang]))
        {
            throw new Exception("Requesting unloaded language");
        }
        

        if (isset($translations[$lookupLang][$key]))
        {
            $translation = $this->translations[$lookupLang][$key];
        }

        

        if (!$translation)
        {
            if ($lookupLang != $this->language)
            {
                $translation = $this->translations[$this->language][$key];
            }
        
        }

        if (!$translation)
        {
            foreach ($this->translations as $translationInLanguage)
            {                
                if (isset($translationInLanguage[$key]))
                {
                    $item = $translationInLanguage[$key];

                    if (is_callable($item))
                    {
                        return $item($options);
                    }
                    elseif (is_string($item))
                    {
                        return $item;
                    }
                    else
                    {
                        return $item;
                    }
                }
            }
        }


        
        if (!$translation)
        {
            if (isset($options["allowReturnOfNull"]))
            {
                return null;
            }
            // throw new Exception("No transaltion of any kind found for key: $key");
            return $key;
        }

        return $translation;
    }

}
