<?php

namespace App\Util;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\Cache\Adapter\RedisTagAwareAdapter;

class ToolsController extends AbstractController
{
    private $cache;

    public function __construct(HttpClientInterface $client)
    {

        $this->client = $client;

        $redis = RedisAdapter::createConnection(
            'redis://localhost:3679'
        );

        $this->cache = new RedisTagAwareAdapter($redis);
    }

    public static function getUserIpAddr(){
        $client  = @$_SERVER['HTTP_CLIENT_IP'];
        $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
        $remote  = $_SERVER['REMOTE_ADDR'];

        if(filter_var($client, FILTER_VALIDATE_IP))
        {
            $ip = $client;
        }
        elseif(filter_var($forward, FILTER_VALIDATE_IP))
        {
            $ip = $forward;
        }
        else
        {
            $ip = $remote;
        }

        return $ip;
    }

    public function getRateByCurrency($base, $target)
    { 

            $response = $this->client->request(
                'GET',
                'https://api.exchangeratesapi.io/latest?base=' .  $base . "&symbols=" . $target
            );

            $statusCode = $response->getStatusCode();
            $res = $response->getContent();

        $decodedRes = json_decode($res, JSON_OBJECT_AS_ARRAY);
        $deconstructedRes = array_values($decodedRes['rates']);

        return floatval($deconstructedRes[0]);

    }

    public function getAll(){
        $transactions = $this->getDoctrine()
            ->getRepository(Transaction::class)
            ->findAll();

        if (!$transactions) {
            throw $this->createNotFoundException(
                'No transactions were found'
            );
        }

        return $transactions;

    }

}

