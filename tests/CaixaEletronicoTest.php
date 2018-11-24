<?php
use PHPUnit\Framework\TestCase;

Use App\CaixaEletronico;
Use App\Banknote;

class CaixaEletronicoTest extends TestCase
{
	private $bank;

	public function setUp()
	{
		$this->bank = new CaixaEletronico();
	}

	public function test_set_banknotes()
	{
		$this->bank->setBanknotes([
			new Banknote('10', 5),
			new Banknote('20', 3)
		]);

		$this->assertEquals([
			new Banknote('10', 5),
			new Banknote('20', 3)
		], $this->bank->getBanknotes());
	}

	/**
	 * @expectedException \App\Errors\InvalidBanknoteAmountException
	 **/
	public function test_set_invalid_banknotes_amount()
	{
		$this->bank->setBanknotes([new Banknote('30', 3)]);
	}

	/**
	 * @expectedException \App\Errors\InvalidBanknoteQuantityException
	 **/
	public function test_set_invalid_banknotes_quantity()
	{
		$this->bank->setBanknotes([
			new Banknote('20', Banknote::MAX_BANKNOTE_QUANTITY + 1)
		]);
	}

	public function test_get_banknote_by_amount()
	{
		$bn1 = new Banknote('10', 5);
		$bn2 = new Banknote('20', 3);

		$this->bank->setBanknotes([$bn1, $bn2]);
		$this->assertEquals(
			$bn1,
			$this->bank->getBanknoteByAmount('10')
		);

		$this->assertFalse($this->bank->getBanknoteByAmount('50'));
	}

	public function test_add_banknotes_quantity()
	{
		$this->bank->setBanknotes([
			new Banknote('10', 5),
			new Banknote('20', 3)
		]);

		$this->bank->addBanknotes([
			new Banknote('10', 2),
			new Banknote('50', 1)
		]);

		$this->assertEquals([
			new Banknote('10', 7),
			new Banknote('20', 3),
			new Banknote('50', 1)
		], $this->bank->getBanknotes());
	}

	public function test_sub_banknotes_quantity()
	{
		$this->bank->setBanknotes([
			new Banknote('10', 5),
			new Banknote('20', 3)
		]);

		$this->bank->subBanknotes([
			new Banknote('10', 2),
			new Banknote('20', 3)
		]);

		$this->assertEquals([
			new Banknote('10', 3)
		], $this->bank->getBanknotes());
	}

	public function test_set_total_amount()
	{
		$this->bank->setTotalAmount(10);
		$this->assertEquals(10, $this->bank->getTotalAmount());
	}

	public function test_sum_banknotes()
	{
		$banknotes = [
			new Banknote('5', 1),
			new Banknote('10', 1),
			new Banknote('20', 1)
		];

		$this->assertEquals(35, $this->bank->getBanknotesAmount($banknotes));
	}

	public function test_set_total_amount_when_change_banknotes()
	{
		$this->bank->setBanknotes([
			new Banknote('10', 1),
			new Banknote('20', 2)
		]);

		$this->assertEquals(50, $this->bank->getTotalAmount());
	}

	public function test_invalid_withdraw_amount()
	{
		$this->assertFalse($this->bank->isValidWithdrawAmount('invalid_value'));
		$this->assertFalse($this->bank->isValidWithdrawAmount('10,10'));
		$this->assertFalse($this->bank->isValidWithdrawAmount('r$10'));
		$this->assertFalse($this->bank->isValidWithdrawAmount('1.000'));
		$this->assertFalse($this->bank->isValidWithdrawAmount(10.3));		
		$this->assertFalse($this->bank->isValidWithdrawAmount(-1));		
	}

	public function test_valid_withdraw_amount()
	{
		$this->assertTrue($this->bank->isValidWithdrawAmount(10));
		$this->assertTrue($this->bank->isValidWithdrawAmount('13'));
		$this->assertTrue($this->bank->isValidWithdrawAmount(100));	
		$this->assertTrue($this->bank->isValidWithdrawAmount(100000));	
	}

	public function test_invalid_withdraw_amount_limit()
	{
		$this->assertFalse($this->bank->isWithDrawAmountBetweenLimits(
			CaixaEletronico::MIN_WITHDRAW_AMOUNT - 1
		));
		$this->assertFalse($this->bank->isWithDrawAmountBetweenLimits(-1));
		$this->assertFalse($this->bank->isWithDrawAmountBetweenLimits(
			CaixaEletronico::MAX_WITHDRAW_AMOUNT + 1
		));
	}

	public function test_valid_withdraw_amount_limit()
	{
		$this->assertTrue($this->bank->isWithDrawAmountBetweenLimits(
			CaixaEletronico::MIN_WITHDRAW_AMOUNT
		));
		$this->assertTrue($this->bank->isWithDrawAmountBetweenLimits(100));
		$this->assertTrue($this->bank->isWithDrawAmountBetweenLimits(
			CaixaEletronico::MAX_WITHDRAW_AMOUNT
		));
	}

	public function test_amount_greater_than_total()
	{
		$this->bank->setTotalAmount(10);

		$this->assertFalse($this->bank->hasAmount(11));
		$this->assertFalse($this->bank->hasAmount(20));
		$this->assertFalse($this->bank->hasAmount(100));
	}

	public function test_amount_less_or_equal_than_total()
	{
		$this->bank->setTotalAmount(10);

		$this->assertTrue($this->bank->hasAmount(10));
		$this->assertTrue($this->bank->hasAmount(9));
		$this->assertTrue($this->bank->hasAmount(0));
	}

	public function test_banknotes_not_divisible()
	{
		$this->bank->setBanknotes([
			new Banknote('50', 1),
			new Banknote('20', 1)
		]);

		$this->assertFalse($this->bank->isAmountDivisibleToBanknotes(60));
		$this->assertFalse($this->bank->isAmountDivisibleToBanknotes(30));
		$this->assertFalse($this->bank->isAmountDivisibleToBanknotes(40));
		$this->assertFalse($this->bank->isAmountDivisibleToBanknotes(10));
		$this->assertFalse($this->bank->isAmountDivisibleToBanknotes(5));
	}

	public function test_banknotes_divisible()
	{
		$this->bank->setBanknotes([
			new Banknote('10', 5),
			new Banknote('50', 5),
			new Banknote('20', 5),
			new Banknote('5', 5),
			new Banknote('2', 5)
		]);

		$this->assertEquals(
			[new Banknote('50', 1)],
			$this->bank->isAmountDivisibleToBanknotes(50)
		);
		$this->assertEquals(
			[new Banknote('20', 1)],
			$this->bank->isAmountDivisibleToBanknotes(20)
		);
		$this->assertEquals(
			[new Banknote('10', 1)],
			$this->bank->isAmountDivisibleToBanknotes(10)
		);
		$this->assertEquals(
			[new Banknote('20', 1), new Banknote('10', 1)],
			$this->bank->isAmountDivisibleToBanknotes(30)
		);
		$this->assertEquals(
			[new Banknote('50', 1), new Banknote('10', 1)],
			$this->bank->isAmountDivisibleToBanknotes(60)
		);
		$this->assertEquals(
			[new Banknote('50', 1), new Banknote('20', 1), new Banknote('10', 1)],
			$this->bank->isAmountDivisibleToBanknotes(80)
		);
		$this->assertEquals(
			[new Banknote('50', 1), new Banknote('20', 1), new Banknote('2', 3)],
			$this->bank->isAmountDivisibleToBanknotes(76)
		);
	}

	public function test_sort_banknotes_by_desc()
	{
		$bn = [
			new Banknote('10', 5),
			new Banknote('2', 5),
			new Banknote('50', 5),
			new Banknote('5', 5),
			new Banknote('20', 5)
		];

		$this->assertEquals([
			new Banknote('50', 5),
			new Banknote('20', 5),
			new Banknote('10', 5),
			new Banknote('5', 5),
			new Banknote('2', 5)
		], $this->bank->sortBanknotesDesc($bn));
	}

	public function test_remove_empty_banknotes()
	{
		$this->bank->setBanknotes([
			new Banknote('10', 2),
			new Banknote('20', 0)
		]);
		$this->bank->clearEmpty();

		$this->assertEquals([
			new Banknote('10', 2)
		], $this->bank->getBanknotes());
	}

	public function test_withdraw()
	{
		$this->bank->setBanknotes([
			new Banknote('2', 10),
			new Banknote('5', 10),
			new Banknote('10', 10),
			new Banknote('20', 10),
			new Banknote('50', 10),
			new Banknote('100', 10)
		]);

		$withdrawNotes = $this->bank->withdraw(288);
		$this->assertEquals([
			new Banknote('100', 2),
			new Banknote('50', 1),
			new Banknote('20', 1),
			new Banknote('10', 1),
			new Banknote('2', 4)
		],$withdrawNotes);

		$this->assertEquals([
			new Banknote('2', 6),
			new Banknote('5', 10),
			new Banknote('10', 9),
			new Banknote('20', 9),
			new Banknote('50', 9),
			new Banknote('100', 8)
		], $this->bank->getBanknotes());
	}
}	