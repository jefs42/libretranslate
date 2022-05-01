<?php
/*
 * Copyright (C) 2022 fortyTwoIT <Jeffrey Shilt>
 *
 * This software may be modified and distributed under the terms
 * of the GNU Affero General Public License. See the LICENSE file for details.
*/
declare(strict_types=1);
namespace Jefs42;

class LibreTranslate
{
    /** @var string The API Key to be used for requests */
    private $apiKey = '';

    /** @var string The API base URL */
    private $apiBase = 'http://localhost';

    /** @var int The port at apiBase to use   */
    private $apiPort = 5000;

    /** @var string Local path to ltmanage, if available */
    private $LTManage = null;

    /** @var boolean If the `ltmanage` binary is available on the server or not */
    public $canManage = false;

    private $sourceLanguage = 'en';

    private $targetLanguage = 'es';

    private $lastError = '';

    private $Settings = [];
    private $Languages = [];



    public function __construct($host = null, $port = null, $source = null, $target = null) {
        // set API base, remove trailing slash
        if (!is_null($host)) {
            $this->apiBase = rtrim( $host, '/\\' );;
        }

        // set API port
        if (!is_null($port)) {
            $this->apiPort = (int)$port;
        }
        // Test connection
        try {
            $this->_doRequest('/');
        } catch (Exception $e) {
            return $e;
        }

        // Go ahead and set info for server settings and available languages
        $this->Settings();
        $this->Languages();

        if (!is_null($source)) {
            $this->setSource($source);
        }
        if (!is_null($target)) {
            $this->setTarget($target);
        }

        // If hosting LibreTranslate locally, check if ltmanage is available for API Key management
        if (function_exists('exec')){
            exec('which ltmanage 2> /dev/null', $binPath, $resCode);
            if (!empty($binPath) && is_array($binPath)) {
                $this->LTManage = $binPath[0];
                $this->canManage = true;
            }
        }
    }

    /* set Api Key */
    public function setApiKey($apiKey) {
        $this->apiKey = $apiKey;
    }
    /* set Source language */
    public function setSource($lang) {
        if (!in_array($lang, array_keys($this->Languages))){
            throw new \Exception($lang . " is not an available language.");
        }
        $this->sourceLanguage = $lang;
    }
    /* set Source language */
    public function setTarget($lang) {
        if (!in_array($lang, array_keys($this->Languages))){
            throw new \Exception($lang . " is not an available language.");
        }
        $this->targetLanguage = $lang;
    }

    /* set both... */
    public function setLanguages($source, $target) {
        $this->setSource($source);
        $this->setLanguage($target);
    }

    /*
        Get server's current settings
        Returns: array
    */
    function Settings() {
        $settings = $this->_doRequest('/frontend/settings', [], 'GET');
        $this->Settings = (array)$settings;
        return (array)$this->Settings;
    }

    /*
        Get server's available languages
        Returns: stdClass array
    */
    function Languages() {
        $this->Languages = [];
        $languages = $this->_doRequest('/languages', [], 'GET');
        foreach ($languages as $language) {
            if (isset($language->code)) {
                $this->Languages[$language->code] = $language->name;
            }
        }
        return $this->Languages;
    }

    //=========== Detect Language ======
    public function Detect($text) {
        $data['q'] = $text;
        if (!is_null($this->apiKey)) {
            $data['api_key'] = $this->apiKey;
        }
        $endpoint = '/detect' . '?' . http_build_query($data);
        $response = $this->_doRequest($endpoint);
        // sort by confidence? return all, or most likely...Or (for now, just return first result)
        if (is_array($response)) {
            return $response[0]->language;
        } else {
            // return false, or throw error....
            return false;
        }
    }

    /*========== Translate ========
    @string,@array $text Single string of text to translate, or array of multiple texts to translate
    @string $source Source language (optional, will use default/current source language)
    @string $target Target language (optional, will use default/current target language)
    Returns: string or array
    */
    public function Translate($text, $source = null, $target = null) {
        // TODO: if source or target passed, validate against known available languages
        $isMulti = false; // check if text passed is single or array
        if (is_array($text)) {
            $isMulti = true;
            $text = urlencode(json_encode($text));
        }

        $data = [
            'q' => $text,
            'format' => 'html',
            'source' => !is_null($source) ? $source : $this->sourceLanguage,
            'target' => !is_null($target) ? $target : $this->targetLanguage
        ];
        if (!is_null($this->apiKey)) {
            $data['api_key'] = $this->apiKey;
        }

        $response = $this->_doRequest('/translate', $data);

        if (is_object($response) && isset($response->translatedText)) {
            // return array of translations if input was array
            if ($isMulti) {
                return (json_decode(urldecode($response->translatedText),true));
            }
            // else return single translation
            return $response->translatedText;
        } else {
            if (isset($response->error)) {
                throw new \Exception($response->error);
            }
        }
    }


    //=========== Translate File ======
    /*
    @string $file Full path to file to be translated
    Returns: string
    */
    public function translateFiles($file, $source = null, $target = null){
        if (!is_file($file)) {
            throw new \Exception("File $file not found.");
        } else {
            $data = [
                'file' => new \CURLFile($file),
                'format' => 'html',
                'source' => !is_null($source) ? $source : $this->sourceLanguage,
                'target' => !is_null($target) ? $target : $this->targetLanguage
            ];
            if (!is_null($this->apiKey)) {
                $data['api_key'] = $this->apiKey;
            }
            $response = $this->_doRequest('/translate_file', $data);
            if (is_object($response) && isset($response->translatedFileUrl)) {
                // fetch file from returned URL location
                $fh = curl_init($response->translatedFileUrl);
                curl_setopt($fh, CURLOPT_RETURNTRANSFER, true);
                $translatedFile = curl_exec($fh);
                if (curl_errno($fh) == 0) {
                    return $translatedFile;
                } else {
                    throw new \Exception(curl_error($fh), curl_errno($fh));
                }
            } else {
                if (isset($response->error)) {
                    throw new \Exception($response->error);
                }
            }
        }
    }


    //============= Suggest ========
    /*
    @string $original Source text
    @string $translation Suggested translation
    @string $source Source language (optional, will use default/current source language)
    @string $target Target language (optional, will use default/current target language)
    */
    function Suggest($original, $suggestion, $source = null, $target = null) {
            $data = [
                'q' => $original,
                's' => $suggestion,
                'source' => !is_null($source) ? $source : $this->sourceLanguage,
                'target' => !is_null($target) ? $target : $this->targetLanguage
            ];
            if (!is_null($this->apiKey)) {
                $data['api_key'] = $this->apiKey;
            }
            $data = http_build_query($data);
            $response = $this->_doRequest('/suggest', $data);
            if (is_object($response) && isset($response->success)) {
                return $response->success;
            } else {
                if (isset($response->error)) {
                    throw new \Exception($response->error);
                }
            }
    }



    //====== LTManage - list keys ===========
    function listKeys() {
        if ($this->canManage) {
            $keyList = [];
            exec($this->LTManage . " keys", $keys, $resultCode);
            foreach ($keys as $list) {
                list($key, $req_limit) = explode(":", $list);
                $keyList[] = [
                    'key' => $key,
                    'req_limit' => $req_limit
                ];
            }
            return $keyList;
        } else {
            throw new \Exception("ltmanage command not found");
        }
    }

    //====== LTManage - add key ===========
    // $req_limit: optional, override default server setting
    function addKey($req_limit = null) {
        if ($this->canManage) {
            exec($this->LTManage . " keys add " . $req_limit, $newKey, $resultCode);
            if (is_array($newKey) && !empty($newKey)) {
                return $newKey[0];
            }

        } else {
            throw new \Exception("ltmanage command not found");
        }
    }

    //====== LTManage - remove key ===========
    function removeKey($api_key) {
        if ($this->canManage) {
            $keys = $this->listKeys();
            $keyList = [];
            foreach ($keys as $key) {
                $keyList[] = $key['key'];
            }
            if (!in_array($api_key, $keyList)) {
                throw new \Exception("API Key to delete does not exist.");
            }
            exec($this->LTManage . " keys remove " . $api_key, $result, $resultCode);
            return true;
        } else {
            throw new \Exception("ltmanage command not found");
        }
    }



    public function getError() {
        return (!empty($this->lastError) ? $this->lastError : null);
    }

    //====== send request to libretranslate server
    // TODO: connection issue or libretranslate error message....
    private function _doRequest($endpoint, $data = [], $type = 'POST') {
        $this->lastError = '';
        $finalEndpoint = $this->apiBase . ( !is_null($this->apiPort) ? ':' . $this->apiPort : '' ) . $endpoint;
        $ch = \curl_init($finalEndpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false,
            CURLOPT_CUSTOMREQUEST => $type,
            CURLOPT_POSTFIELDS => $data,
        ]);
        $response = curl_exec($ch);
        $responseInfo = curl_getinfo($ch);
        if (curl_errno($ch) != 0) {
            throw new \Exception(curl_error($ch), curl_errno($ch));
        }
        return json_decode($response);
    }
}
