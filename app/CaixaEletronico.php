<?php
namespace App;

use App\Banknote;

class CaixaEletronico
{
	const MIN_WITHDRAW_AMOUNT 	 = 1;
	const MAX_WITHDRAW_AMOUNT 	 = 3000;

	private $banknotes = [];
	private $totalAmount = 0;

	public function setBanknotes($banknotes)
	{
		$this->banknotes = $banknotes;
		$this->setTotalAmount(
			$this->getBanknotesAmount($banknotes)
		);
	}

	public function getBanknotes()
	{
		return $this->banknotes;
	}

	public function getBanknoteByAmount($amount)
	{
		foreach ($this->getBanknotes() as $banknote) {
			if ($banknote->getAmount() == $amount) {
				return $banknote;
			}
		}

		return false;
	}

	public function addBanknotes($banknotes)
	{
		foreach ($banknotes as $banknote) {
			$banknoteFound = $this->getBanknoteByAmount($banknote->getAmount());
			if ($banknoteFound) {
				$banknoteFound->addQuantity($banknote->getQuantity());
				continue;
			}
			$newBanknotes = $this->getBanknotes();
			$newBanknotes[] = $banknote;
		}
		$this->setBanknotes($newBanknotes);
	}

	public function subBanknotes($banknotes)
	{
		foreach ($banknotes as $banknote) {
			$banknoteFound = $this->getBanknoteByAmount($banknote->getAmount());
			$banknoteFound->subQuantity($banknote->getQuantity());
		}
		$this->clearEmpty();
	}

	public function clearEmpty()
	{
		$banknotes = $this->getBanknotes();

		foreach ($banknotes as $index => $banknote) {
			if (!$banknote->getQuantity()) {
				unset($banknotes[$index]);
			}
		}

		$this->setBanknotes($banknotes);
	}

	public function getBanknotesAmount($banknotes)
	{
		$sum = 0;
		foreach ($banknotes as $banknote) {
			$sum += $banknote->getTotalAmount();
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

	public function sortBanknotesDesc($banknotes)
	{
		$banknotesWithKeys = [];
		foreach ($banknotes as $banknote) {
			$banknotesWithKeys[$banknote->getAmount()] = $banknote;
		}
		krsort($banknotesWithKeys);
		return array_values($banknotesWithKeys);
	}

	public function isAmountDivisibleToBanknotes($amount)
	{
		$amountDivisible = 0;
		$banknotesDivisible = [];
		$banknotes = $this->sortBanknotesDesc($this->getBanknotes());

		foreach ($banknotes as $banknote) {
			if (!$banknote->getQuantity() || $banknote->getAmount() > $amount) {
				continue;
			}

			for ($i = 0; $i < $banknote->getQuantity(); $i++) {
				if ($amountDivisible + $banknote->getAmount() > $amount
					|| ($banknote->getAmount() < 10 && ($amount - $amountDivisible) % $banknote->getAmount())) {
					break;
				}

				if (isset($banknotesDivisible[$banknote->getAmount()])) {
					$banknotesDivisible[$banknote->getAmount()]->addQuantity(1);
				} else {
					$banknotesDivisible[$banknote->getAmount()]
						= new Banknote($banknote->getAmount(), 1);
				}

				if (($amountDivisible += $banknote->getAmount()) == $amount) {
					return array_values($banknotesDivisible);
				}
			}
		}

		return false;
	}

	public function withdraw($amount)
	{
		$withdrawNotes = $this->isAmountDivisibleToBanknotes($amount);
		$this->subBanknotes($withdrawNotes);
		return $withdrawNotes;
	}
}