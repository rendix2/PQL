<?php
/**
 *
 * Created by PhpStorm.
 * Filename: AbstractFunction.php
 * User: Tomáš Babický
 * Date: 16.09.2021
 * Time: 2:03
 */

namespace PQL\Database\Query\Builder\Expressions;

use Exception;

/**
 * Class AbstractFunction
 *
 * @package PQL\Database\Query\Builder\Expressions
 */
abstract class AbstractFunction extends AbstractExpression implements IFunction
{
    /**
     * @var IExpression[] $arguments
     */
    private array $arguments;

    /**
     * @var int $countArguments
     */
    private int $countArguments;

    /**
     * @var string $lowerName
     */
    private string $lowerName;

    /**
     * @var string $upperName
     */
    private string $upperName;

    /**
     * @param string      $name
     * @param array       $arguments
     * @param string|null $alias
     *
     * @throws Exception
     */
    public function __construct(string $name, array $arguments, ?string $alias = null)
    {
        parent::__construct($alias);

        $this->lowerName = mb_strtolower($name);
        $this->upperName = mb_strtoupper($name);

        foreach ($arguments as $argument) {
            if (!($argument instanceof IExpression)) {
                $message = 'Argument is not Expression';

                throw new Exception($message);
            }
        }

        $this->arguments = $arguments;
        $this->countArguments = count($arguments);
    }

    public function __destruct()
    {
        foreach ($this as $key => $value) {
            unset($this->{$key});
        }

        parent::__destruct();
    }

    /**
     * @return string
     */
    public function getLowerName() : string
    {
        return $this->lowerName;
    }

    /**
     * @return string
     */
    public function getUpperName() : string
    {
        return $this->upperName;
    }

    /**
     * @return IExpression[]
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * @return int
     */
    public function getCountArguments() : int
    {
        return $this->countArguments;
    }

    /**
     * @return string
     */
    public function evaluate() : string
    {
        $arguments = array_map(
            static function (IExpression $expression) {
                return $expression->evaluate();
            }, $this->arguments
        );

        $arguments = implode(', ', $arguments);

        return sprintf('%s(%s)', $this->upperName, $arguments);
    }

    /**
     * @param int|null $level
     *
     * @return string
     */
    public function print(?int $level = null): string
    {
        $arguments = array_map(
            static function (IExpression $expression) {
                return $expression->print();
            }, $this->arguments
        );

        $arguments = implode(', ', $arguments);

        return sprintf('%s(%s)', $this->upperName, $arguments);
    }
}
