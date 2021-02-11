<?php
namespace App\Controller;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\Cache\Adapter\RedisTagAwareAdapter;
use Symfony\Contracts\Cache\ItemInterface;
use App\Entity\Transaction;
use Symfony\Component\HttpFoundation\Request;
use App\Util\ToolsController;

class ExchangeController extends AbstractController
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

    public function listAll()
    {

        $content = $this->cache->get('currencies', function (ItemInterface $item) {
            $item->expiresAfter(30);
        
             $response = $this->client->request(
                    'GET',
                    'https://api.exchangeratesapi.io/latest'
                );
        
             $statusCode = $response->getStatusCode();
                $content = $response->getContent();
        
            return $content;
        });
        
        $currencies = json_decode($content, JSON_OBJECT_AS_ARRAY);

        $currencies['rates']['EUR'] = 1.0;

        $keys = array_keys($currencies['rates']);
        $newRates = array_combine($keys, $keys);



        return $newRates;
    }

    public function showTransactions(Request $request, ToolsController $tools)
    {
        $ip = array(
            'request_ip' => $tools->getUserIpAddr()
        );

        $transactions = $this->getDoctrine()
            ->getRepository(Transaction::class)
            ->findBy($ip, null, $request->query->get('limit'), $request->query->get('offset'));
        
        if (!$transactions) {
            throw $this->createNotFoundException(
                'no transactions found'
            );
        }
        return $transactions;
    }
}