<?php

function debug($value,$die=false)
{
    echo "<pre>";
    var_dump($value);
    echo "</pre>";

    if ($die) {
        die();
    }
}