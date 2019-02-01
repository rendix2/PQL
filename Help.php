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
 * @author Tomáš Babický tomas.babicky@websta.de
 */
class Help
{

    public static function arrayObjectColumn(array $objects, $column)
    {
        $tmp = [];

        foreach ($objects as $object) {
            $tmp[] = $object->{$column};
        }

        return $tmp;
    }
}
