<?php
// https://github.com/rdio/rdio-simple/tree/master/php
/* Rdio specific functions */
class RdioAPI {
    function __construct($consumer, $token=NULL) {
        $this->consumer = $consumer;
        $this->token = $token;
    }

    function getArtistKeyKeyFromName($result, $artist_name) {
        $artist_key = "";
        foreach($result as $value) {
            if (strtolower($value->name) == strtolower($artist_name)) {
               $artist_key =  $value->key;
               break;
            }
        }
        return $artist_key;
    }
    
    private function __signed_post($url, $params) {
        $auth = $this->om($this->consumer, $url, $params, $this->token);
        $curl = curl_init($url);
        $postbody = http_build_query($params);
        //curl_setopt($curl, CURLOPT_VERBOSE, TRUE);
        curl_setopt($curl, CURLOPT_POST, TRUE);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postbody);
        curl_setopt($curl, CURLOPT_HTTPHEADER,
          array('Content-type: application/x-www-form-urlencoded',
          'Authorization: '.$auth));
        $body = curl_exec($curl);
        curl_close($curl);
        return $body;
    }

    function begin_authentication($callback_url) {
        $response = $this->__signed_post('http://api.rdio.com/oauth/request_token',
          array('oauth_callback'=>$callback_url));
        $parsed = array();
        parse_str($response, $parsed);
        $this->token = array($parsed['oauth_token'], $parsed['oauth_token_secret']);
        return $parsed['login_url'] . '?oauth_token=' . $parsed['oauth_token'];
    }

    function complete_authentication($verifier) {
        $response = $this->__signed_post('http://api.rdio.com/oauth/access_token',
          array('oauth_verifier'=>$verifier));
        $parsed = array();
        parse_str($response, $parsed);
        $this->token = array($parsed['oauth_token'], $parsed['oauth_token_secret']);
    }

    function call($method, $params=array()) {
        $params['method'] = $method;
        return json_decode($this->__signed_post('http://api.rdio.com/1/', $params));
    }

    function __om_escape($s) {
      return str_replace('%7E', '~', rawurlencode($s));
    }   

  
    function om($consumer, $url, $params, $token=NULL, $method='POST', $realm=NULL, $timestamp=NULL, $nonce=NULL) {
      # the method must be upper-case
      $method = strtoupper($method);

      # normalize the URL
      $parts = parse_url($url);
      # the scheme and the host are lower-cased
      $normalized_url = strtolower($parts['scheme']) . '://' .
        strtolower($parts['host']);
      # include non-default port numbers
      if (array_key_exists('port', $parts) && (
        (strtolower($parts['scheme']) == 'http' && $parts['port'] != 80) ||
        (strtolower($parts['scheme']) == 'https' && $parts['port'] != 443))) {
          $normalized_url .= ':' . $parts['port'];
      }
      # the path goes in as-is
      $normalized_url .= $parts['path'];

      # add query-string params (if any) to the params list since they must be
      # included in the signature
      if (array_key_exists('query', $parts)) {
        parse_str($parts['query'], $url_params);
        $params = array_merge($params, $url_params);
      }

      # add OAuth params
      $params['oauth_version'] = '1.0';
      if ($timestamp == NULL) {
        $params['oauth_timestamp'] = ''.time();
      } else {
        $params['oauth_timestamp'] = $timestamp;
      }
      if ($nonce == NULL) {
        $params['oauth_nonce'] = ''.rand(0,1000000);
      } else {
        $params['oauth_nonce'] = $nonce;
      }
      $params['oauth_signature_method'] = 'HMAC-SHA1';
      $params['oauth_consumer_key'] = $consumer[0];

      # the consumer secret is the first half of the HMAC-SHA1 key
      $hmac_key = $consumer[1] . '&';

      if ($token != NULL) {
        # include a token in params
        $params['oauth_token'] = $token[0];
        # and the token secret in the HMAC-SHA1 key
        $hmac_key .= $token[1];
      }

      # sort the params by key
      ksort($params, SORT_STRING);

      # escape the params and combine them into a string
      $normalized_params = "";
      foreach ($params as $key=>$value) {
        $normalized_params .= '&' . $this->__om_escape($key) . '=' . $this->__om_escape($value);
      }
      $normalized_params = substr($normalized_params, 1);

      # build the signature base string
      $signature_base_string = $this->__om_escape($method) . '&' .
        $this->__om_escape($normalized_url) . '&' . $this->__om_escape($normalized_params);

      # HMAC-SHA1
      $oauth_signature = base64_encode(hash_hmac("sha1", $signature_base_string,
        $hmac_key, TRUE));

      # Build the Authorization header
      $authorization_params = array();
      if ($realm) {
        array_push($authorization_params, 'realm="' . $this->__om_escape($realm) . '"');
      }
      array_push($authorization_params,
        'oauth_signature="' . $oauth_signature . '"');

      $oauth_parameters = array(
        'oauth_version', 'oauth_timestamp', 'oauth_nonce', 'oauth_signature_method',
        'oauth_signature', 'oauth_consumer_key', 'oauth_token'
      );
      foreach ($params as $key=>$value) {
        if (in_array($key, $oauth_parameters)) {
          array_push($authorization_params,
            $this->__om_escape($key) . '="' . $this->__om_escape($value) . '"');
        }
      }

      return 'OAuth ' . implode(', ', $authorization_params);
    }
};