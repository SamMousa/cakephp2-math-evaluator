<?php

    App::import('Lib','Math.Math');
    /**
    * Class containing test cases for the math library.
    */
    class MathTest extends CakeTestCase
    {

        /**
        * Setup function called before every test case.
        *
        */
        public function setUp()
        {
            parent::setUp();

        }

        /**
        * Function that tests division.
        * Should also try to find out if the priority of division is handled correctly.
        *
        */
        public function testDivision()

        {
            $this->assertTrue(Math::evalExpr('10 / 4') === 2.5);
            $this->assertTrue(Math::evalExpr('10 / 2') === 10 / 2);
            $this->assertTrue(Math::evalExpr('10 + 10 / 4') === 10 + 10 / 4);
            $this->assertTrue(Math::evalExpr('10 / 10 + 4') === 10 / 10 + 4);

            //$this->assertTrue(Math::evalExpr('4 * 10 / 4') == 4 * 10 / 4);
            //$this->assertTrue(Math::evalExpr('10 / 4') == 10 / 4);
        }


        /**
        * Tests division by zero always returns false.
        */
        public function testDivisionZero()
        {
            $this->assertFalse(Math::evalExpr('10/(5-5)'));
            $this->assertFalse(Math::evalExpr('10/0'));
            $this->assertFalse(Math::evalExpr('10-5/0'));
            $this->assertFalse(Math::evalExpr('(4+3)/0'));
            $this->assertFalse(Math::evalExpr('0/0'));
        }

        /**
        * Test variables.
        *
        */
        public function testVariables()
        {
            $params = array('a' => 15);
            $this->assertEquals(Math::evalExpr('{a}', $params), $params['a']);
        }


    }
?>
