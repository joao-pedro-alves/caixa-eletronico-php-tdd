<?php
namespace App;

use App\Errors\InvalidBanknotesException;

class CaixaEletronico
{
	const MIN_WITHDRAW_AMOUNT 	 = 1;
	const MAX_WITHDRAW_AMOUNT 	 = 3000;
	const MAX_BANKNOTES_QUANTITY = 500;
	const VALID_BANKNOTES_AMOUNT = ['2', '5', '10', '20', '50', '100'];

	private $banknotes = [];

	private $totalAmount = 0;

	public function setBanknotes($banknotes)
	{
		if (!$this->isValidBanknotes($banknotes)) {
			throw new InvalidBanknotesException();
		}
		
		$this->banknotes = $banknotes;
		$this->setTotalAmount(
			$this->getBanknotesAmount($banknotes)
		);
	}

	public function getBanknotes()
	{
		return $this->banknotes;
	}

	public function addBanknotes($banknotes)
	{
		$currentBanknotes = $this->getBanknotes();

		foreach ($banknotes as $note => $quantity) {
			if (isset($currentBanknotes[$note])) {
				$currentBanknotes[$note] += $quantity;
			} else {
				$currentBanknotes[$note] = $quantity;
			}
		}

		$this->setBanknotes($currentBanknotes);
	}

	public function subBanknotes($banknotes)
	{
		$currentBanknotes = $this->getBanknotes();

		foreach ($banknotes as $note => $quantity) {
			if (!($currentBanknotes[$note] -= $quantity)) {
				unset($currentBanknotes[$note]);
			}
		}

		$this->setBanknotes($currentBanknotes);
	}

	public function getBanknotesAmount($banknotes)
	{
		$sum = 0;
		foreach ($banknotes as $note => $quantity) {
			$sum += $note * $quantity;
		}

		return $sum;
	}

	public function setTotalAmount($amount)
	{
		$this->totalAmount = $amount;
	}

	public function getTotalAmount()
	{
		return $this->totalAmount;
	}

	public function isValidWithdrawAmount($amount)
	{
		return (bool)preg_match('/^\d+$/', $amount);
	}

	public function isWithDrawAmountBetweenLimits($amount)
	{
		return $amount >= static::MIN_WITHDRAW_AMOUNT
			&& $amount <= static::MAX_WITHDRAW_AMOUNT;
	}

	public function hasAmount($amount)
	{
		return ($amount <= $this->totalAmount);
	}

	public function isAmountDivisibleToBanknotes($amount)
	{
		$amountDivisible = 0;
		$banknotes = $this->getBanknotes();
		$banknotesDivisible = [];
		krsort($banknotes);

		foreach ($banknotes as $note => $quantity) {
			if (!$quantity || $note > $amount) {
				continue;
			}

			for ($i = 0; $i < $quantity; $i++) {
				if ($amountDivisible + $note > $amount
					|| ($note < 10 && ($amount - $amountDivisible) % $note)) {
					break;
				}

				if (isset($banknotesDivisible[$note])) {
					$banknotesDivisible[$note]++;
				} else {
					$banknotesDivisible[$note] = 1;
				}

				if (($amountDivisible += $note) == $amount) {
					return $banknotesDivisible;
				}
			}
		}

		return false;
	}

	public function isValidBanknotes($banknotes)
	{
		foreach ($banknotes as $note => $quantity)
		{
			if (!in_array($note, static::VALID_BANKNOTES_AMOUNT)) {
				return false;
			}

			if ($quantity < 0 || $quantity > static::MAX_BANKNOTES_QUANTITY) {
				return false;
			}
		}

		return true;
	}

	public function withdraw($amount)
	{
		$withdrawNotes = $this->isAmountDivisibleToBanknotes($amount);
		$this->subBanknotes($withdrawNotes);
		return $withdrawNotes;
	}
}