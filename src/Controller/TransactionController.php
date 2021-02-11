<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\Transaction;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Controller\ExchangeController;
use Symfony\Component\HttpFoundation\Request;
use App\Util\FormController;
use App\Util\ToolsController;

class TransactionController extends AbstractController
{

    public function createTransaction(ValidatorInterface $validator, ExchangeController $exchangeController, Request $request, ToolsController $tools, FormController $formController): Response
    {
        $transaction = new Transaction();

        $form = $formController->TransactionForm($transaction, $tools, $exchangeController, $request, $validator);

        return $this->render('exchange.html.twig', [
            'form' => $form->createView(),
            'currencies' => $exchangeController->listAll(),
            'message' => $form ? 'new transaction created of ID ' . $transaction->getId() : ''
        ], new Response(json_encode($transaction), 200));
    }

    public function EditTransaction(ValidatorInterface $validator, ExchangeController $exchangeController, Request $request, ToolsController $tools, FormController $formController): Response{

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
                'No product found for id ' . implode(array_values($id))
            );
        }

        $form = $formController->EditTransactionForm($transaction, $tools, $exchangeController, $request, $validator, $entityManager);
        

        return $this->render('userTransactions.html.twig', [
            'form' => $form->createView(),
            'transactions' => $exchangeController->showTransactions($request, $tools),
            'message' => ''
        ], new Response(json_encode($transaction), 200));
    }
}
