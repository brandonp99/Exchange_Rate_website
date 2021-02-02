<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Transaction;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\Cache\Adapter\RedisTagAwareAdapter;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use App\Controller\ExchangeController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class TransactionController extends AbstractController
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

    /**
     * @Route("/transaction", name="transaction")
     */
    public function createTransaction(ValidatorInterface $validator, ExchangeController $exchangeController, Request $request): Response
    {
        $rates = $exchangeController->listAll();

        $rates['rates']['EUR'] = 1.0;

        $keys = array_keys($rates['rates']);
        $newRates = array_combine($keys, $keys);
        

        $transaction = new Transaction();

        $form = $this->createFormBuilder($transaction)
            ->add('payment_method', ChoiceType::class, [
                'choices' => [
                    'Bank Transfer' => 'Bank Transfer',
                    'Card' => 'Card'
                ],
            ])
            ->add('transaction_type', ChoiceType::class, [
                'choices' => [
                    'Deposit' => 'Deposit',
                    'Withdraw' => 'Withdrawal'
                ],
            ])
            ->add('base_currency', ChoiceType::class, [
                'choices' => $newRates
            ])
            ->add('base_amount', MoneyType::class)
            ->add('target_currency', ChoiceType::class, [
                'choices' => $newRates
            ])
            ->add('submit', SubmitType::class, ['label' => 'Submit'])
            ->getForm();
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $transaction = $form->getData();

            $transaction->setTransactionTimestamp(new DateTime());
            $transaction->setExchangeRate($this->getRateByCurrency($form->get('base_currency')->getData(), $form->get('target_currency')->getData()));
            $transaction->setRequestIp($this->getUserIpAddr());
            $transaction->setTargetAmmount(($transaction->getBaseAmount() * $transaction->getExchangeRate()));



            $entityManager = $this->getDoctrine()->getManager();

            $entityManager->persist($transaction);
            $entityManager->flush();

            $errors = $validator->validate($transaction);
            if (count($errors) > 0) {
                return new Response((string) $errors, 400);
            }else{
                return $this->render('exchange.html.twig', [
                    'form' => $form->createView(),
                    'currencies' => $rates,
                    'message' => 'Saved new transaction with id '.$transaction->getId()
                ], new Response(json_encode($transaction), 200));
            }
        }

        return $this->render('exchange.html.twig', [
            'form' => $form->createView(),
            'currencies' => $rates,
            'message' => ''
        ]);
    }

    public function EditTransaction(ValidatorInterface $validator, ExchangeController $exchangeController, Request $request): Response{
        $rates = $exchangeController->listAll();

        $rates['rates']['EUR'] = 1.0;

        $keys = array_keys($rates['rates']);
        $newRates = array_combine($keys, $keys);

        $id = $request->attributes->get('id');

        $id = array(
            'id' => $id
        );
        $entityManager = $this->getDoctrine()->getManager();

        $transaction = $entityManager
            ->getRepository(Transaction::class)
            ->find($id);
        
        if (!$transaction) {
            throw $this->createNotFoundException(
                'No product found for id ' . array_values($id)
            );
        }

        $form = $this->createFormBuilder($transaction)
            ->add('target_currency', ChoiceType::class, [
                'choices' => $newRates
            ])
            ->add('submit', SubmitType::class, ['label' => 'Submit'])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $transaction->setTargetCurrency($form->get('target_currency')->getData());
            $transaction->setExchangeRate($this->getRateByCurrency($transaction->getBaseCurrency(), $form->get('target_currency')->getData()));
            $transaction->setTargetAmmount(($transaction->getBaseAmount() * $transaction->getExchangeRate()));

            $entityManager->flush();

            $errors = $validator->validate($transaction);
            if (count($errors) > 0) {
                return new Response((string) $errors, 400);
            }else{
                return $this->render('userTransactions.html.twig', [
                    'form' => $form->createView(),
                    'transactions' => $exchangeController->showTransactions($request),
                    'message' => 'Saved transaction with id '.$transaction->getId()
                ], new Response(json_encode($transaction), 200));
            }
        }

        return $this->render('userTransactions.html.twig', [
            'form' => $form->createView(),
            'transactions' => $exchangeController->showTransactions($request),
            'message' => ''
        ]);
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
}
