<?php
/**
 *
 * Created by PhpStorm.
 * Filename: JoinedTable.php
 * User: Tomáš Babický
 * Date: 27.08.2021
 * Time: 13:18
 */

namespace PQL\Database\Query\Builder;

use Exception;
use Nette\NotImplementedException;
use PQL\Database\Query\Builder\Expressions\AbstractExpression;
use PQL\Database\Query\Builder\Expressions\ICondition;
use PQL\Database\Query\Builder\Expressions\IFromExpression;
use PQL\Database\Query\Builder\Expressions\WhereCondition;

/**
 * Class JoinExpression
 *
 * @package PQL\Database\Query\Builder
 */
class JoinExpression extends AbstractExpression
{
    /**
     * @var IFromExpression $joinExpression
     */
    private IFromExpression $joinExpression;

    /**
     * @var ICondition[] $joinConditions
     */
    private array $joinConditions;

    /**
     * JoinedTable constructor.
     *
     * @param IFromExpression  $joinExpression
     * @param WhereCondition[] $joinConditions
     * @param string|null      $alias
     *
     * @throws Exception
     */
    public function __construct(
        IFromExpression $joinExpression,
        array $joinConditions,
        ?string $alias = null
    ) {
        parent::__construct($alias);

        foreach ($joinConditions as $joinCondition) {
            if (!($joinCondition instanceof ICondition)) {
                $message = sprintf('Join "%s" condition id not valid', get_class($joinCondition));

                throw new Exception($message);
            }
        }

        $this->joinExpression = $joinExpression;
        $this->joinConditions = $joinConditions;
    }

    public function __destruct()
    {
        foreach ($this as $key => $value) {
            unset($this->{$key});
        }

        parent::__destruct();
    }

    /**
     * @return IFromExpression
     */
    public function getJoinExpression() : IFromExpression
    {
        return $this->joinExpression;
    }

    /**
     * @return ICondition[]
     */
    public function getJoinConditions() : array
    {
        return $this->joinConditions;
    }

    public function evaluate() : string
    {
        throw new NotImplementedException();
    }

    public function print(?int $level = null) : string
    {
        throw new NotImplementedException();
    }
}