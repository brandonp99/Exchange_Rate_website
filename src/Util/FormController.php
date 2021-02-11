<?php

namespace App\Util;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use DateTime;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use App\Controller\ExchangeController;

class FormController extends AbstractController
{
   public function TransactionForm($transaction, ToolsController $tools, ExchangeController $exchangeController, Request $request, ValidatorInterface $validator) {

        $rates = $exchangeController->listAll();

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
            'choices' => $rates
        ])
        ->add('base_amount', MoneyType::class)
        ->add('target_currency', ChoiceType::class, [
            'choices' => $rates
        ])
        ->add('submit', SubmitType::class, ['label' => 'Submit'])
        ->getForm();

    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $transaction = $form->getData();

        $transaction->setTransactionTimestamp(new DateTime());
        $transaction->setExchangeRate($tools->getRateByCurrency($form->get('base_currency')->getData(), $form->get('target_currency')->getData()));
        $transaction->setRequestIp($tools->getUserIpAddr());
        $transaction->setTargetAmmount(($transaction->getBaseAmount() * $transaction->getExchangeRate()));



        $entityManager = $this->getDoctrine()->getManager();

        $entityManager->persist($transaction);
        $entityManager->flush();

        $errors = $validator->validate($transaction);
        if (count($errors) > 0) {
            return new Response((string) $errors, 400);
        }else{
            return $form;
        }
    }else {
        return $form;
    }
   }



   public function EditTransactionForm($transaction, ToolsController $tools, ExchangeController $exchangeController, Request $request, ValidatorInterface $validator, EntityManager $entityManager){

        $rates = $exchangeController->listAll();
        
        $form = $this->createFormBuilder($transaction)
            ->add('target_currency', ChoiceType::class, [
                'choices' => $rates
            ])
            ->add('submit', SubmitType::class, ['label' => 'Submit'])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $transaction->setTargetCurrency($form->get('target_currency')->getData());
            $transaction->setExchangeRate($tools->getRateByCurrency($transaction->getBaseCurrency(), $form->get('target_currency')->getData()));
            $transaction->setTargetAmmount(($transaction->getBaseAmount() * $transaction->getExchangeRate()));

            $entityManager->flush();

            $errors = $validator->validate($transaction);
            if (count($errors) > 0) {
                return new Response((string) $errors, 400);
            }else{
                return $form;
            }
        }else {
            return $form;
        }
   }

}