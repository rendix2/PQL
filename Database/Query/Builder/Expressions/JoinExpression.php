<?php
/**
 *
 * Created by PhpStorm.
 * Filename: JoinedTable.php
 * User: Tomáš Babický
 * Date: 27.08.2021
 * Time: 13:18
 */

namespace PQL\Query\Builder;

use Exception;
use Nette\NotImplementedException;
use PQL\Query\Builder\Expressions\AbstractExpression;
use PQL\Query\Builder\Expressions\ICondition;
use PQL\Query\Builder\Expressions\IExpression;
use PQL\Query\Builder\Expressions\IFromExpression;
use PQL\Query\Builder\Expressions\WhereCondition;

class JoinExpression extends AbstractExpression implements IExpression
{
    private IFromExpression $joinExpression;

    /**
     * @var WhereCondition[] $joinConditions
     */
    private array $joinConditions;

    /**
     * JoinedTable constructor.
     *
     * @param IFromExpression  $joinExpression
     * @param WhereCondition[] $joinConditions
     * @param null|string      $alias
     *
     * @throws Exception
     */
    public function __construct(IFromExpression $joinExpression, array $joinConditions, ?string $alias = null)
    {
        parent::__construct($alias);

        foreach ($joinConditions as $joinCondition) {
            if (!($joinCondition instanceof ICondition)) {
                $message = sprintf('Join condition id not valid');

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
    }

    /**
     * @return IFromExpression
     */
    public function getJoinExpression(): IFromExpression
    {
        return $this->joinExpression;
    }

    /**
     * @return WhereCondition[]
     */
    public function getJoinConditions(): array
    {
        return $this->joinConditions;
    }

    public function evaluate()
    {
        throw new NotImplementedException();
    }
}