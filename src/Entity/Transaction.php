<?php

namespace App\Entity;

use App\Repository\TransactionRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TransactionRepository::class)
 */
class Transaction
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $payment_method;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $transaction_type;

    /**
     * @ORM\Column(type="datetime")
     */
    private $transaction_timestamp;

    /**
     * @ORM\Column(type="float")
     */
    private $base_amount;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $base_currency;

    /**
     * @ORM\Column(type="float")
     */
    private $target_ammount;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $target_currency;

    /**
     * @ORM\Column(type="float")
     */
    private $exchange_rate;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $request_ip;

    
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPaymentMethod(): ?string
    {
        return $this->payment_method;
    }

    public function setPaymentMethod(string $payment_method): self
    {
        $this->payment_method = $payment_method;

        return $this;
    }

    public function getTransactionType(): ?string
    {
        return $this->transaction_type;
    }

    public function setTransactionType(string $transaction_type): self
    {
        $this->transaction_type = $transaction_type;

        return $this;
    }

    public function getTransactionTimestamp(): ?string
    {
        $result = $this->transaction_timestamp->format('Y-m-d H:i:s');
        if ($result) {
            return $result;
        }else {
            echo "Unknown Time";
        }
    }

    public function setTransactionTimestamp(\DateTimeInterface $transaction_timestamp): self
    {
        $this->transaction_timestamp = $transaction_timestamp;

        return $this;
    }

    public function getBaseAmount(): ?float
    {
        return $this->base_amount;
    }

    public function setBaseAmount(float $base_amount): self
    {
        $this->base_amount = $base_amount;

        return $this;
    }

    public function getBaseCurrency(): ?string
    {
        return $this->base_currency;
    }

    public function setBaseCurrency(string $base_currency): self
    {
        $this->base_currency = $base_currency;

        return $this;
    }

    public function getExchangeRate(): ?float
    {
        return $this->exchange_rate;
    }

    public function setExchangeRate(float $exchange_rate): self
    {
        $this->exchange_rate = $exchange_rate;

        return $this;
    }

    public function getRequestIp(): ?string
    {
        return $this->request_ip;
    }

    public function setRequestIp(string $request_ip): self
    {
        $this->request_ip = $request_ip;

        return $this;
    }

    public function getTargetAmmount(): ?float
    {
        return $this->target_ammount;
    }

    public function setTargetAmmount(float $target_ammount): self
    {
        $this->target_ammount = $target_ammount;

        return $this;
    }

    public function getTargetCurrency(): ?string
    {
        return $this->target_currency;
    }

    public function setTargetCurrency(string $target_currency): self
    {
        $this->target_currency = $target_currency;

        return $this;
    }
}
