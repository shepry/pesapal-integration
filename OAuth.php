<?php
/**
 * Simple OAuth library
 * Based on: https://github.com/fernandezpablo85/safepay/blob/master/lib/OAuth.php
 */

class OAuthConsumer {
    public $key;
    public $secret;

    function __construct($key, $secret) {
        $this->key = $key;
        $this->secret = $secret;
    }

    function __toString() {
        return "OAuthConsumer[key=$this->key,secret=$this->secret]";
    }
}

class OAuthToken {
    public $key;
    public $secret;

    function __construct($key, $secret) {
        $this->key = $key;
        $this->secret = $secret;
    }

    function __toString() {
        return "OAuthToken[key=$this->key,secret=$this->secret]";
    }
}

class OAuthSignatureMethod_HMAC_SHA1 {
    function get_name() {
        return "HMAC-SHA1";
    }

    function build_signature($request, $consumer, $token) {
        $base_string = $request->get_signature_base_string();
        $key_parts = array(
            rawurlencode($consumer->secret),
            ($token) ? rawurlencode($token->secret) : ""
        );
        $key = implode("&", $key_parts);
        return base64_encode(hash_hmac("sha1", $base_string, $key, true));
    }

    function check_signature($request, $consumer, $token, $signature) {
        $built = $this->build_signature($request, $consumer, $token);
        return $built == $signature;
    }
}

class OAuthRequest {
    private $parameters;
    private $http_method;
    private $http_url;

    public static function from_consumer_and_token($consumer, $token, $http_method, $http_url, $parameters = null) {
        $req = new OAuthRequest($http_method, $http_url, $parameters);
        $req->set_parameter("oauth_version", "1.0");
        $req->set_parameter("oauth_nonce", md5(mt_rand()));
        $req->set_parameter("oauth_timestamp", time());
        $req->set_parameter("oauth_consumer_key", $consumer->key);
        $req->set_parameter("oauth_signature_method", "HMAC-SHA1");
        if ($token) {
            $req->set_parameter("oauth_token", $token->key);
        }
        return $req;
    }

    function __construct($http_method, $http_url, $parameters = null) {
        $this->parameters = ($parameters) ? $parameters : array();
        $this->http_method = $http_method;
        $this->http_url = $http_url;
    }

    function set_parameter($name, $value) {
        $this->parameters[$name] = $value;
    }

    function get_signature_base_string() {
        $params = $this->parameters;
        ksort($params);

        $encoded_params = array();
        foreach ($params as $k => $v) {
            $encoded_params[] = rawurlencode($k) . "=" . rawurlencode($v);
        }

        return strtoupper($this->http_method) . "&" . rawurlencode($this->http_url) . "&" . rawurlencode(implode("&", $encoded_params));
    }

    function sign_request($signature_method, $consumer, $token) {
        $signature = $signature_method->build_signature($this, $consumer, $token);
        $this->set_parameter("oauth_signature", $signature);
    }

    function to_url() {
        $out = $this->http_url . "?";
        $params = array();
        foreach ($this->parameters as $k => $v) {
            $params[] = rawurlencode($k) . "=" . rawurlencode($v);
        }
        $out .= implode("&", $params);
        return $out;
    }
}
