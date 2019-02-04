<?php

interface ITable
{
    public function getColumns();
    
    public function getRows($object = false);
    
}

