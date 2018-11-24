<?php
use PHPUnit\Framework\TestCase;

Use App\Banknote;

class BanknoteTest extends TestCase
{
	public function test_get_amount()
	{
		$bn = new Banknote('10', 1);
		$this->assertEquals('10', $bn->getAmount());
	}

	public function test_get_quantity()
	{
		$bn = new Banknote('10', 3);
		$this->assertEquals(3, $bn->getQuantity());
	}

	public function test_set_quantity()
	{
		$bn = new Banknote('10', 3);
		$bn->setQuantity(20);

		$this->assertEquals(20, $bn->getQuantity());
	}

	public function test_set_invalid_quantity()
	{
		$bn = new Banknote('10', 0);
		$this->assertFalse($bn->isValidQuantity(-1));
		$this->assertFalse($bn->isValidQuantity('invalid'));
		$this->assertFalse($bn->isValidQuantity('10.1'));
		$this->assertFalse($bn->isValidQuantity(10.2));
		$this->assertFalse($bn->isValidQuantity('10,2'));
		$this->assertFalse($bn->isValidQuantity('R$10,2'));
		$this->assertFalse($bn->isValidQuantity(
			Banknote::MAX_BANKNOTE_QUANTITY + 1
		));
	}

	public function test_set_valid_quantity()
	{
		$bn = new Banknote('10', 3);
		$this->assertTrue($bn->isValidQuantity(10));
		$this->assertTrue($bn->isValidQuantity(20));
		$this->assertTrue($bn->isValidQuantity(1));
		$this->assertTrue($bn->isValidQuantity(0));
		$this->assertTrue($bn->isValidQuantity(Banknote::MAX_BANKNOTE_QUANTITY));
	}

	/**
	 * @expectedException \App\Errors\InvalidBanknoteQuantityException
	 **/
	public function test_set_negative_quantity_in_construct()
	{
		new Banknote('10', -1);
	}

	/**
	 * @expectedException \App\Errors\InvalidBanknoteQuantityException
	 **/
	public function test_set_quantity_exceeds_in_construct()
	{
		new Banknote('10', Banknote::MAX_BANKNOTE_QUANTITY + 1);
	}

	/**
	 * @expectedException \App\Errors\InvalidBanknoteQuantityException
	 **/
	public function test_set_alphanumeric_quantity_in_construct()
	{
		new Banknote('10', 'abc123');
	}

	public function test_valid_amount()
	{
		$bn = new Banknote('10', 1);	
		$this->assertTrue($bn->isValidAmount('2'));

		$bn = new Banknote('10', 1);	
		$this->assertTrue($bn->isValidAmount('5'));

		$bn = new Banknote('10', 1);	
		$this->assertTrue($bn->isValidAmount('10'));

		$bn = new Banknote('10', 1);	
		$this->assertTrue($bn->isValidAmount('20'));

		$bn = new Banknote('10', 1);	
		$this->assertTrue($bn->isValidAmount('50'));

		$bn = new Banknote('10', 1);	
		$this->assertTrue($bn->isValidAmount('100'));
	}

	public function test_invalid_amount()
	{
		$bn = new Banknote('10', 1);	
		$this->assertFalse($bn->isValidAmount(30));

		$bn = new Banknote('10', 1);	
		$this->assertFalse($bn->isValidAmount('acbc'));

		$bn = new Banknote('10', 1);	
		$this->assertFalse($bn->isValidAmount('60'));
	}

	public function test_add_quantity()
	{
		$bn = new Banknote('10', 1);

		$bn->addQuantity(10);
		$this->assertEquals(11, $bn->getQuantity());

		$bn->addQuantity(5);
		$this->assertEquals(16, $bn->getQuantity());
	}

	public function test_sub_quantity()
	{
		$bn = new Banknote('10', 10);

		$bn->subQuantity(5);
		$this->assertEquals(5, $bn->getQuantity());

		$bn->subQuantity(5);
		$this->assertEquals(0, $bn->getQuantity());
	}

	/**
	 * @expectedException \App\Errors\AddQuantityExceedsException
	 **/
	public function test_add_exceeds_quantity()
	{
		$bn = new Banknote('10', 10);
		$bn->addQuantity(Banknote::MAX_BANKNOTE_QUANTITY + 1);
	}

	/**
	 * @expectedException \App\Errors\SubQuantityExceedsException
	 **/
	public function test_sub_exceeds_quantity()
	{
		$bn = new Banknote('10', 10);
		$bn->subQuantity(11);
	}

	public function test_get_total_amount()
	{
		$bn = new Banknote('10', 3);
		$this->assertEquals(30, $bn->getTotalAmount());

		$bn = new Banknote('5', 5);
		$this->assertEquals(25, $bn->getTotalAmount());

		$bn = new Banknote('2', 10);
		$this->assertEquals(20, $bn->getTotalAmount());
	}
}