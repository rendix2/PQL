<?php
/**
 * Created by PhpStorm.
 * User: Tom
 * Date: 1. 2. 2019
 * Time: 15:33
 */

/**
 * Class Help
 *
 * @author rendix2
 */
class Help
{
    /**
     * @param array  $objects
     * @param string $column
     *
     * @return array
     */
    public static function arrayObjectColumn(array $objects, $column)
    {
        $tmp = [];

        foreach ($objects as $object) {
            if ($object instanceof Row) {
                $tmp[] = $object->get()->{$column};
            } else {
                $tmp[] = $object->{$column};
            }            
        }

        return $tmp;
    }

    /**
     * @param mixed $var
     *
     * @return string
     */
    public static function getType($var)
    {
        $type = gettype($var);

        switch ($type) {
            case 'integer':
                return 'int';
            case 'boolean':
                return 'bool';
            default:
                return $type;
        }
    }
}
