<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Http\Resources\LinkedInResource;

class LinkedInController extends Controller
{
    CONST HOME_URL  = 'https://www.linkedin.com/';
    CONST LOGIN_URL = 'https://www.linkedin.com/uas/login-submit';
    CONST LOGIN_OUT = 'https://www.linkedin.com/m/logout/';

    CONST COOKIE_FILE = 'linkedin.cookie';
    CONST TOKEN_FILE  = 'linkedin.token';

    /**
     * Fetch and return the linkedin api token.
     *
     * @param Request $info
     */
    public function store(Request $linkedin)
    {
        // Logout
        \Storage::put(self::COOKIE_FILE, '');

        // Goto Login Page.
        // $html = $this->send_request(self::LOGIN_OUT);
        $html = $this->send_request('https://www.linkedin.com/notifications/');
        $doc = new \DOMDocument();
        @$doc->loadHTML($html);
        $xpath = new \DomXPath($doc);

        $form_login_params = [];
        $input_elements = $xpath->query('//form[@class="login-form"]//input');

        foreach ($input_elements as $input) {
            $name  = $input->getAttribute('name');
            $value = $input->getAttribute('value');

            if (empty($name))
                continue;

            $form_login_params[$name] = $value;
        }

        $form_login_params['session_key']      = $linkedin->input('email');
        $form_login_params['session_password'] = $linkedin->input('password');

        $html = $this->send_request(self::LOGIN_URL, 'POST', [], $form_login_params);

        // Save token file in local.
        $cookies = $this->parse_cookies();
        \Storage::put(self::TOKEN_FILE, $cookies['leo_auth_token']);

        $data = new \stdClass();
        $data->email = $linkedin->input('email');
        $data->password = $linkedin->input('password');
        $data->token = $cookies['leo_auth_token'];

        $linkedin = new LinkedInResource($data);

        return $linkedin;
    }

    /**
     * Send HTTP request to site.
     */
    private function send_request($url, $method = 'GET', $header = [], $post_data = []) {
        $curl = curl_init();

        $curl_options = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_COOKIEJAR => storage_path('app/'.self::COOKIE_FILE),
            CURLOPT_COOKIEFILE => storage_path('app/'.self::COOKIE_FILE)
        ];

        if (!empty($post_data)) {
            $postvars = '';

            foreach($post_data as $key => $val) {
                $postvars .= "$key=$val&";
            }

            $curl_options[CURLOPT_POST] = true;
            $curl_options[CURLOPT_POSTFIELDS] = $postvars;
            $curl_options[CURLOPT_CUSTOMREQUEST] = 'POST';
        }

        curl_setopt_array($curl, $curl_options);

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        return $response;
    }

    private function parse_cookies() {
        // read the file
        $lines = file(storage_path('app/'.self::COOKIE_FILE));
        $cookies = [];

        // iterate over lines
        foreach ($lines as $line) {
            // we only care for valid cookie def lines
            if ($line[0] != '#' && substr_count($line, "\t") == 6) {

                // get tokens in an array
                $tokens = explode("\t", $line);

                // trim the tokens
                $tokens = array_map('trim', $tokens);

                $cookies[$tokens[5]] = $tokens[6];
            }
        }

        return $cookies;
    }
}