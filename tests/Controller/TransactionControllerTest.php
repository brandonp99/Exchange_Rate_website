<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TransactionControllerTest extends WebTestCase
{
    public function testHomePage()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/exchange');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testTransactionsPage()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/exchange/userTransactions');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }
}