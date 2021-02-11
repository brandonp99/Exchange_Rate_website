<?php

namespace App\tests\Util;

use App\Controller\ExchangeController;
use App\Util\ToolsController;
use PHPUnit\Framework\TestCase;
use App\Util\FormController;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Entity\Transaction;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use DateTime;

class FormControllerTest extends TestCase{

    public function testTransactionForm(){
        $formController = new FormController();

        $client = $this->createMock(HttpClientInterface::class);
        $client->expects($this->atLeastOnce())->method($this->anything());

        $validator = $this->createMock(ValidatorInterface::class);
        $validator->expects($this->atLeastOnce())->method($this->anything());

        $transactionForm = $formController->TransactionForm(new Transaction(), new ToolsController($client), new ExchangeController($client), new Request(), $validator);

        $this->assertNotNull($transactionForm);
    }

    public function testEditTransactionForm(){
        $formController = new FormController();

        $client = $this->createMock(HttpClientInterface::class);
        $client->expects($this->atLeastOnce())->method($this->anything());

        $validator = $this->createMock(ValidatorInterface::class);
        $validator->expects($this->atLeastOnce())->method($this->anything());

        $transaction = new Transaction();
        $transaction->setPaymentMethod('Card')
                    ->setTransactionType('Deposit')
                    ->setTransactionTimestamp(new DateTime())
                    ->setBaseAmount(20)
                    ->setBaseCurrency('GBP')
                    ->setTargetAmmount('20')
                    ->setTargetCurrency('GBP')
                    ->setExchangeRate(1)
                    ->setRequestIp('::1');

        $transactionForm = $formController->TransactionForm($transaction, new ToolsController($client), new ExchangeController($client), new Request(), $validator);

        $this->assertNotNull($transactionForm);
    }
}