<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \classes\SimpleCache as SimpleCache;
use \classes\JSON as JSON;

    // GET example api to show all data role
    $app->get('/', function (Request $request, Response $response) {
        $body = $response->getBody();
        $response = $this->cache->withEtag($response, $this->etag2hour.'-'.trim($_SERVER['REQUEST_URI'],'/'));
        if (SimpleCache::isCached(3600)){
            $datajson = SimpleCache::load();
        } else {
            $data = [
                'status' => 'success',
			    'code' => '200',
                'welcome' => 'Selamat Datang, ini adalah halaman default Rest API dari umrahalal.com',
                'concept' => 'Agar dapat menggunakan sistem kami. Anda harus mendaftar untuk mendapatkan token atau API Key.',
                'author' => [
                    'name' => 'M ABD AZIZ ALFIAN',
                    'email' => 'aalfiann@gmail.com',
                    'github' => 'https://github.com/aalfiann',
                    'linkedin' => 'https://www.linkedin.com/in/azizalfian'
                ]
            ];
            $blacklistparam = ["&_=","&query=","&search=","token","apikey","api_key","time","timestamp","time_stamp","etag","key","q","s","k","t"];
            $datajson = SimpleCache::save(JSON::encode($data,true,true),null,$blacklistparam,3600);
        }
        $body->write($datajson);
        return classes\Cors::modify($response,$body,200);
    });