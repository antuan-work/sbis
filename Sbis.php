<?php

class Sbis
{
    private $url_sbis_login = 'https://online.sbis.ru/oauth/service/';
    private $url_sbis_service = 'https://online.sbis.ru/service/?srv=1';
    private $session_id;
    private $token;
    private $config;

    public function __construct(array $config)
    {
        if(empty($config['app_secret'])) {
            throw new Exception('Empty config');
        }

        $this->config = $config;
    }


    private function get_session_id() : string
    {
        if(empty($this->session_id)) {
            $this->_login();
        }

        return $this->session_id;
    }


    private function get_token() : string
    {
        if(empty($this->token)) {
            $this->_login();
        }

        return $this->token;
    }

    /**
     * Авторизация
     *
     * @return array
     */
    private function _login() : void
    {
        $data = $this->_exec_request_login($this->config);
        $this->session_id = $data['sid'];
        $this->token = $data['token'];
    }


    /**
     * Список документов
     *
     * @return array
     */
    public function get_doc(string $doc_uuid) : array
    {
        $post = [
            'jsonrpc' => '2.0',
            'method' => 'СБИС.ПрочитатьДокумент',
            'params' => [
                'Документ' => [
                    'Идентификатор' => $doc_uuid
                ]
            ],
            'id' => 0
        ];

        $_result = $this->_exec_request_service($post);
        $result = $_result['result'];

        return $result;
    }

    public function get_file(string $file_url)
    {
        return $this->_exec_request_file($file_url);
    }


    /**
     * CURL-запрос для авторизации
     *
     * @param array|null $post
     *
     * @return array|null
     */
    private function _exec_request_login(array $post = null) : ?array
    {
        $ch = curl_init();
        $headers = [
            'Content-Type: application/json; charset=utf-8',
            'User-Agent: StroyservisBot/1.0'
        ];
        curl_setopt_array($ch, [
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_URL            => $this->url_sbis_login,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($post, JSON_UNESCAPED_UNICODE),
            CURLOPT_HTTPHEADER     => $headers
        ]);
        $response = curl_exec($ch);
        curl_close($ch);

        return !empty($response) ? json_decode($response, true) : null;
    }

    /**
     * CURL-запрос для основных функций
     *
     * @param array|null $post
     *
     * @return array|null
     */
    private function _exec_request_service(array $post = null) : ?array
    {
        $ch = curl_init();
        $headers = [
            'Content-Type: application/json; charset=utf-8',
            'User-Agent: StroyservisBot/1.0',
            'X-SBISSessionID: ' . $this->get_session_id(),
            'X-SBISAccessToken: ' . $this->get_token(),
        ];
        curl_setopt_array($ch, [
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_URL            => $this->url_sbis_service,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($post, JSON_UNESCAPED_UNICODE),
            CURLOPT_HTTPHEADER     => $headers
        ]);
        $response = curl_exec($ch);
        curl_close($ch);

        return !empty($response) ? json_decode($response, true) : null;
    }

    /**
     * CURL-запрос для файлов
     *
     * @param string $url
     *
     * @return array|null
     */
    private function _exec_request_file(string $url) : ?string
    {
        $ch = curl_init();
        $headers = [
            'Content-Type: application/json; charset=utf-8',
            'User-Agent: StroyservisBot/1.0',
            'X-SBISSessionID: ' . $this->get_session_id(),
            'X-SBISAccessToken: ' . $this->get_token(),
        ];
        curl_setopt_array($ch, [
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_URL            => $url,
            CURLOPT_POST           => false,
            CURLOPT_HTTPHEADER     => $headers
        ]);
        $response = curl_exec($ch);
        curl_close($ch);

        return !empty($response) ? $response : null;
    }
}