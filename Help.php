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
}
