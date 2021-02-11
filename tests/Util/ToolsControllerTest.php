<?php

namespace App\tests\Util;

use PHPUnit\Framework\TestCase;
use App\Util\ToolsController;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ToolsControllerTest extends TestCase{

    public function testGetRateByCurrency(){
        $client = $this->createMock(HttpClientInterface::class);
        $client->expects($this->atLeastOnce())->method($this->anything());
        
        $tools = new ToolsController($client);

        $rates = $tools->getRateByCurrency('GBP', 'GBP');

        $this->assertEquals(1.0, $rates);
    }

    public function testGetUsrIpAddr(){
        $client = $this->createMock(HttpClientInterface::class);
        $client->expects($this->atLeastOnce())->method($this->anything());
        
        $tools = new ToolsController($client);

        $ip = $tools->getUserIpAddr();

        $this->assertStringContainsString('::1', $ip);
    }

    public function testgetAlltransactions(){
        $client = $this->createMock(HttpClientInterface::class);
        $client->expects($this->atLeastOnce())->method($this->anything());
        
        $tools = new ToolsController($client);

        $res = $tools->getAll();

        $this->assertNotNull($res);
    }

}