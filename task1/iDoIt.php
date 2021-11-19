<?php

//Напишите запрос, возвращающий имя и число указанных телефонных номеров девушек в возрасте от 18 до 22 лет.
//Оптимизируйте таблицы и запрос при необходимости.

$connection = new PDO("pgsql:" . "host="."127.0.0.1" . " dbname=" . "p2b", "admin", "123", [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);//псс, вы этого не видели, так делать низя

//от 18 до 22 - берем текущий год, вычитаем 18 лет, получаем от 18. 22 аналогично
// ps знаю, что все это можно сделать в sql, но я программист и мне лень)

$currentYear = date("Y", time());
$aFewMomentsAgo18 = strtotime("31.12.".(int)$currentYear-18);//изи не строгая типизирование в конкатинации
$aFewMomentsAgo22 = strtotime("01.01.".(int)$currentYear-22);//izi
$howManyPhoneNumbersOnGirl = $connection->prepare("SELECT name, count(case1.phone_numbers.user_id) FROM case1.users INNER JOIN case1.phone_numbers ON case1.users.id = case1.phone_numbers.user_id WHERE users.birth_date>:min AND users.birth_date<:max AND users.gender = 2 GROUP BY name");
$howManyPhoneNumbersOnGirl->bindValue("max", $aFewMomentsAgo18);
$howManyPhoneNumbersOnGirl->bindValue("min", $aFewMomentsAgo22);
$listing = $howManyPhoneNumbersOnGirl->execute();
$listing = $howManyPhoneNumbersOnGirl->fetchAll();
var_dump($howManyPhoneNumbersOnGirl);//тут в консоль выведется результат

/**
 * А теперь то, что сделал бы я с таблицами для оптимизации работы)
 * 1) разбил бы номер на 2 части, код страны/региона + номер и сделал бы эти 2 поля уникальными, чтобы не дать возможности вбить один и тот же номер дважды,
 * 2) для ускорения работы закешировал бы count, тк это долгая операция,
 * 3) можно притянуть за уши и убрать inner join, если сделам процедурку, которая при insert в phone numbers будет обновлять счетчик в users.
 */
