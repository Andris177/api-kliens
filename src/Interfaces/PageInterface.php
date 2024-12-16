<?php
namespace App\Interfaces;
 
interface PageInterface
{
    static function head();
 
    static function nav();
 
    static function tableBody(array $entities);
 
    static function table(array $entities);
 
    static function searchbar();
}