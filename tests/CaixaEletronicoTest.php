<?php
use PHPUnit\Framework\TestCase;

Use App\CaixaEletronico;

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
			'10' => 5,
			'20' => 3
		]);

		$this->assertEquals([
			'10' => 5,
			'20' => 3
		], $this->bank->getBanknotes());
	}

	/**
	 * @expectedException \App\Errors\InvalidBanknotesException
	 **/
	public function test_set_invalid_banknotes_amount()
	{
		$this->bank->setBanknotes(['30' => 3]);
	}

	/**
	 * @expectedException \App\Errors\InvalidBanknotesException
	 **/
	public function test_set_invalid_banknotes_quantity()
	{
		$this->bank->setBanknotes([
			'20' => CaixaEletronico::MAX_BANKNOTES_QUANTITY + 1
		]);
	}

	public function test_add_banknotes_quantity()
	{
		$this->bank->setBanknotes([
			'10' => 5,
			'20' => 3
		]);

		$this->bank->addBanknotes([
			'10' => 2,
			'50' => 1
		]);

		$this->assertEquals([
			'10' => 7,
			'50' => 1,
			'20' => 3
		], $this->bank->getBanknotes());
	}

	public function test_sub_banknotes_quantity()
	{
		$this->bank->setBanknotes([
			'10' => 5,
			'20' => 3
		]);

		$this->bank->subBanknotes([
			'10' => 2,
			'20' => 3
		]);

		$this->assertEquals([
			'10' => 3,
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
			'5' => 1,
			'10' => 1,
			'20' => 1
		];

		$this->assertEquals(35, $this->bank->getBanknotesAmount($banknotes));
	}

	public function test_set_total_amount_when_change_banknotes()
	{
		$this->bank->setBanknotes([
			'10' => 1,
			'20' => 2
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
			'50' => 1,
			'20' => 1
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
			'10' => 5,
			'50' => 5,
			'20' => 5,
			'5' => 5,
			'2' => 5,
		]);

		$this->assertEquals(
			['50' => 1],
			$this->bank->isAmountDivisibleToBanknotes(50)
		);
		$this->assertEquals(
			['20' => 1],
			$this->bank->isAmountDivisibleToBanknotes(20)
		);
		$this->assertEquals(
			['10' => 1],
			$this->bank->isAmountDivisibleToBanknotes(10)
		);
		$this->assertEquals(
			['20' => 1, '10' => 1],
			$this->bank->isAmountDivisibleToBanknotes(30)
		);
		$this->assertEquals(
			['50' => 1, '10' => 1],
			$this->bank->isAmountDivisibleToBanknotes(60)
		);
		$this->assertEquals(
			['50' => 1, '20' => 1, '10' => 1],
			$this->bank->isAmountDivisibleToBanknotes(80)
		);
		$this->assertEquals(
			['50' => 1, '20' => 1, '2' => 3],
			$this->bank->isAmountDivisibleToBanknotes(76)
		);
	}

	public function test_withdraw()
	{
		$this->bank->setBanknotes([
			'2' => 10,
			'5' => 10,
			'10' => 10,
			'20' => 10,
			'50' => 10,
			'100' => 10
		]);

		$withdrawNotes = $this->bank->withdraw(288);
		$this->assertEquals([
			'100' => 2,
			'50' => 1,
			'20' => 1,
			'10' => 1,
			'2' => 4
		],$withdrawNotes);

		$this->assertEquals([
			'100' => 8,
			'50' => 9,
			'20' => 9,
			'10' => 9,
			'5' => 10,
			'2' => 6
		], $this->bank->getBanknotes());
	}

	public function test_invalid_banknotes()
	{
		$this->assertFalse($this->bank->isValidBanknotes(['1' => 5]));
		$this->assertFalse($this->bank->isValidBanknotes(['5' => -1]));
		$this->assertFalse($this->bank->isValidBanknotes(['30' => 1]));
		$this->assertFalse($this->bank->isValidBanknotes([
			'100' => 1,
			'40' => 1
		]));
		$this->assertFalse($this->bank->isValidBanknotes(['invalid' => 1]));
		$this->assertFalse($this->bank->isValidBanknotes(
			['100' => CaixaEletronico::MAX_BANKNOTES_QUANTITY + 1]
		));
	}

	public function test_valid_banknotes()
	{
		$this->assertTrue($this->bank->isValidBanknotes(['2' => 5]));
		$this->assertTrue($this->bank->isValidBanknotes(['5' => 10]));
		$this->assertTrue($this->bank->isValidBanknotes(['10' => 1]));
		$this->assertTrue($this->bank->isValidBanknotes(['20' => 200]));
		$this->assertTrue($this->bank->isValidBanknotes(['50' => 1]));
		$this->assertTrue($this->bank->isValidBanknotes(['100' => 3]));
		$this->assertTrue($this->bank->isValidBanknotes([
			'100' => CaixaEletronico::MAX_BANKNOTES_QUANTITY
		]));
	}
}	