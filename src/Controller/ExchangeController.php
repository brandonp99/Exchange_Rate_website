<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ExchangeController extends AbstractController
{
    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    public function renderTemplate(): Response
    {
        return $this->render('exchange.html.twig', []);
    }

    public function listAll(): Response
    {
        $response = $this->client->request(
            'GET',
            'https://api.exchangeratesapi.io/latest'
        );

        $statusCode = $response->getStatusCode();
        $content = $response->getContent();
        $currencies = json_decode($content, JSON_OBJECT_AS_ARRAY);

        return $this->render('exchange.html.twig', [
            'currencies' => $currencies
        ]);
    }

    public function getRateByCurrency($currency)
    {
        $response = $this->client->request(
            'GET',
            'https://api.exchangeratesapi.io/latest?symbols=' . $currency
        );

        $statusCode = $response->getStatusCode();
        $res = $response->getContent();
        $rate = json_decode($res, JSON_OBJECT_AS_ARRAY);

        return array_values($rate);

    }
}