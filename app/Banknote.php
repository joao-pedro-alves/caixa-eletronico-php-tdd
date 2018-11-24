<?php
namespace App;

use App\Errors\InvalidBanknoteQuantityException;
use App\Errors\SubQuantityExceedsException;
use App\Errors\AddQuantityExceedsException;
use App\Errors\InvalidBanknoteAmountException;

class Banknote
{
	const MAX_BANKNOTE_QUANTITY = 500;
	const VALID_AMOUNT = ['2', '5', '10', '20', '50', '100'];

	private $amount;
	private $quantity;

	public function __construct($amount, $quantity)
	{
		$this->setAmount($amount);
		$this->setQuantity($quantity);
	}

	public function setAmount($amount)
	{
		if (!$this->isValidAmount($amount)) {
			throw new InvalidBanknoteAmountException;
		}
		$this->amount = $amount;
	}

	public function getAmount()
	{
		return $this->amount;
	}

	public function getQuantity()
	{
		return $this->quantity;
	}

	public function isValidQuantity($quantity)
	{
		return (bool)preg_match('/^\d+$/', $quantity)
			&& ($quantity >= 0 && $quantity <= static::MAX_BANKNOTE_QUANTITY);
	}

	public function isValidAmount($amount)
	{
		return in_array($amount, static::VALID_AMOUNT);
	}

	public function setQuantity($quantity)
	{
		if (!$this->isValidQuantity($quantity)) {
			throw new InvalidBanknoteQuantityException;
		}
		$this->quantity = $quantity;
	}

	public function addQuantity($quantity)
	{
		$add = $this->getQuantity() + $quantity;
		if ($add > static::MAX_BANKNOTE_QUANTITY) {
			throw new AddQuantityExceedsException;
		}
		$this->setQuantity($add);
	}

	public function subQuantity($quantity)
	{
		$sub = $this->getQuantity() - $quantity;

		if ($sub < 0) {
			throw new SubQuantityExceedsException;
		}
		$this->setQuantity($sub);
	}

	public function getTotalAmount()
	{
		return $this->getAmount() * $this->getQuantity();
	}
}