<?php


    class Math
    {
        static private $precedences = array(
            'sentinel',
            'binary.+',
            'binary.-',
            'unary.-',
            'binary.*',
            'binary./',
            'binary.^'
        );

        static private $binary = array(
            '*',
            '/',
            '+',
            '-',
            '^'
        );

        static private $unary = array(
            '-'


        );

        /**
        * Checks if $operator has a higher precendence than the last operator that
        * was put on the stack.
        */
        static private function hasPrecedence($operator, $operatorStack)
        {
            $index1 = array_search($operator, self::$precedences);
            $index2 = array_search(end($operatorStack), self::$precedences);

            return $index1 > $index2;
        }
        /**
        * Substitutes variables in an expression with their values.
        * @return False if unused variables occur, the substituted expression otherwsie.
        * @param string $expression
        * @param array $params
        */
        static private  function substitute($expression, $params = array())
        {

            // Replace parameters in the expression.
            foreach ($params as $name => $value)
            {
                $expression = str_replace('{' . $name .'}', $value, $expression);
            }

            return $expression;
        }

        /**
        * Parses an expression enclosed by brackets.
        *
        * @param string $expression The expression
        * @param array $stack The current stack
        */
        static private  function parseBrackets(&$expression, &$stack)
        {
            $tokens = array();
            if (preg_match('/^[[:space:]]*\((.+)\)(.*)$/', $expression, $tokens) == 1)
            {
                // We parse the expression inside the brackets separately.
                $subResult = self::doParse($tokens[1]);
                if ($subResult !== false)
                {
                    $stack[] = $subResult;
                    $expression = $tokens[2];
                    return true;
                }
                else
                {
                    return false;
                }


            }
        }

        static private  function parseValue(&$expression, &$operandStack, &$operatorStack)
        {
            if (preg_match('/^[[:space:]]*([[:digit:]]*\.?[[:digit:]]+)(.*)$/', $expression, $tokens) == 1)
            {
                array_push($operandStack, $tokens[1]);
                $expression = $tokens[2];
                return true;
            }
            else
            {
                return false;
            }
        }
        /**
        * Parses a token.
        *
        * @param string $expression The expression
        * @param array $operandStack The current stack
        * @return True unless we must abort parsing.
        */
        static private  function parseToken(&$expression, &$operandStack, &$operatorStack)
        {
            $tokens = array();
            $ok = self::parseValue($expression, $operandStack, $operatorStack) ||
            self::parseBrackets($expression, $operandStack, $operatorStack) ||
            (self::parseUnOp($expression, $operandStack, $operatorStack) && self::parseToken($expression, $operandStack, $operatorStack));
            return $ok;
        }


        /**
        * Parses a binary operator.
        *
        * @param string $expression The expression
        * @param array $stack The current stack
        * @return True unless we must abort parsing.
        */
        static private function parseBinOp(&$expression, &$operandStack, &$operatorStack)
        {
            $tokens = array();
            $expression = trim($expression);
            // Find the string.
            if (in_array($expression[0], self::$binary))
            {
                // Check the precedence of the operator.
                $op = "binary.{$expression[0]}";
                $expression = substr($expression, 1);

                if (!self::hasPrecedence($op, $operatorStack))
                {
                    // Precedence
                    $result = self::buildBinary($operandStack, $operatorStack);
                    array_push($operatorStack, $op);
                    return $result;
                }
                else
                {
                    array_push($operatorStack, $op);
                }

                return true;
            }
            return false;
        }

        static private function buildBinary(&$operandStack, &$operatorStack)
        {
            if (count($operandStack) >= 2 && count($operatorStack) >=1 && (substr(end($operatorStack), 0, 6) == 'binary'))
            {
                $right = array_pop($operandStack);
                $left = array_pop($operandStack);
                array_push($operandStack, array(array_pop($operatorStack) => array($left, $right)));
                return true;
            }
            else
            {
                return false;
            }
        }

        static private function buildUnary(&$operandStack, &$operatorStack)
        {
            if (count($operandStack) >= 1 && count($operatorStack) >=1 && (substr(end($operatorStack), 0, 5) == 'unary'))
            {
                $right = array_pop($operandStack);

                array_push($operandStack, array(array_pop($operatorStack) => array($right)));
                return true;
            }
            else
            {
                return false;
            }
        }
        /**
        * Parses a binary operator.
        *
        * @param string $expression The expression
        * @param array $stack The current stack
        * @return True unless we must abort parsing.
        */
        static private  function parseUnOp(&$expression, &$operandStack, &$operatorStack)
        {
            $tokens = array();
            // Find the string.
            $expression = trim($expression);
            if (in_array($expression[0], self::$unary))
            {

                // Check the precedence of the operator.
                $op = "unary.{$expression[0]}";
                array_push($operatorStack, $op);
                $expression = substr($expression, 1);
                if (self::hasPrecedence($op, $operatorStack))
                {
                    // Lower so construct tree.
                    return self::buildUnary($operandStack, $operatorStack);
                }

                return true;
            }
            return false;
        }

        /**
        * Parses an expression into an Abstract Syntax Tree.
        *
        * @param string $expression
        * @return Array on success, false otherwise.
        */
        static public function doParse($expression)
        {
            $operatorStack = array('sentinel');
            $operandStack = array();
            if (self::parse($expression, $operandStack, $operatorStack))
            {
                if (isset($operandStack[0]))
                {
                    return $operandStack[0];
                }
                else
                {
                    return $operandStack;

                }
            }
            else
            {
                return false;
            }




        }
        /**
        * Parses an expression into a tree for evaluation.
        *
        * @param string $expression
        * @param array $stack The current stack.
        * @return array
        */
        static private function parse(&$expression, &$operandStack, &$operatorStack)
        {
            $count = 0;

            $ok = self::parseToken($expression, $operandStack, $operatorStack);
            while (strlen($expression) > 0 && $ok && ($count < 20))
            {
                $count ++;
                $ok = self::parseBinOp($expression, $operandStack, $operatorStack) && self::parseToken($expression, $operandStack, $operatorStack);

            }

            if ($expression == '')
            {
                $ok  = true;
            }


            while (count($operatorStack) >= 2 && $ok)
            {
                $ok = self::buildUnary($operandStack, $operatorStack) || self::buildBinary($operandStack, $operatorStack);
            }
            return $ok;

        }



        /**
        * Parses an expression.
        *
        * @param string $expression The expression to be parsed.
        * @param array $params An array containing values for variables.
        * @param bool $equation If true will return an equation instead of just the answer. EG. 3 + 4 = 7.
        * @return mixed Returns the resulting value or an equation.
        */
        static public function evalExpr($expression, $params = array(), $equation = false)
        {

            $expr = self::substitute($expression, $params);
            $tree = self::doParse($expr);
            if ($tree !== false)
            {
                if ($equation)
                {
                    return $expression . ' = ' . self::evalTree($tree);
                }
                else
                {
                    return self::evalTree($tree);
                }
            }
            else
            {
                return false;
            }

        }

        /**
        * Evaluates an Abstract Syntax Tree recursively.
        *
        * @param array $params An array containing the AST.
        * @return mixed Returns the resulting value.
        */
        static public function evalTree($ast = null, $continue = true)
        {
            if (is_array($ast) && count($ast) == 1)
            {
                $key = key($ast);
                // Calculate subTrees
                $left = self::evalTree($ast[$key][0]);
                if (isset($ast[$key][1]))
                {
                    $right = self::evalTree($ast[$key][1]);
                }
                // Check if evaluation for one of the subtrees failed.
                if ($left === false || $right === false)
                {
                    return false;
                }
                switch (key($ast))
                {
                case 'binary.*':
                    return $left * $right;
                case 'binary.+':
                    return $left + $right;
                case 'binary./':
                    if ($right == 0)
                    {
                        //trigger_error('Division by 0. Returning false as result.');
                        return false;
                    }
                    else
                    {
                        return $left / $right;
                    }
                case 'binary.-':
                    return $left - $right;
                case 'binary.^':
                    return pow($left, $right);
                case 'unary.-':
                    return -1 * $left;
                }
            }
            else
            {
                return $ast;
            }
        }
    }
?>
