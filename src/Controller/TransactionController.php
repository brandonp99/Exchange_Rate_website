<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Transaction;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TransactionController extends AbstractController
{
    /**
     * @Route("/transaction", name="transaction")
     */
    public function createTransaction(ValidatorInterface $validator, $paymentMethod, $transactionType, $baseAmmount, $baseCurrency, $exchangeRate, $requestIp): Response
    {
        $entityManager = $this->getDoctrine()->getManager();

        $transaction = new Transaction();
        $transaction->setPaymentMethod($paymentMethod);
        $transaction->setTransactionType($transactionType);
        $transaction->setTransactionTimestamp(new DateTime());
        $transaction->setBaseAmount($baseAmmount);
        $transaction->setBaseCurrency($baseCurrency);
        $transaction->setExchangeRate($exchangeRate);
        $transaction->setRequestIp($requestIp);

        $entityManager->persist($transaction);

        $entityManager->flush();

        $errors = $validator->validate($transaction);
        if (count($errors) > 0) {
            return new Response((string) $errors, 400);
        }else{
            return new Response('Saved new transaction with id '.$transaction->getId());
        }
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
