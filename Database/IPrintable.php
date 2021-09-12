<?php
/**
 *
 * Created by PhpStorm.
 * Filename: IPrintable.php
 * User: Tomáš Babický
 * Date: 08.09.2021
 * Time: 21:31
 */

namespace PQL;

interface IPrintable
{
    public function print(?int $level = null) : string;
}