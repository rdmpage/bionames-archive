<?php

// repair thumbnails (assuming images already processed)

require_once (dirname(__FILE__) . '/pdf.php');
require_once (dirname(__FILE__) . '/utils.php');

$sha1 = 'ea5133a44246624cd6000bea9b356c702bc3f321';
$sha1 = 'fae17dae68d0282af342fac8ca5a24d496aa46ba';
$sha1 = '0368663f82de6e424ae1831ececf1d7b095f651c';
$sha1 = 'fe3e4104dfb1435fcf8bc72661a9fdd543569293';
$sha1 = '8110ceb66522c5c96b209e15e8d1247f6d0542df';
$sha1 = 'c16a83383b64653f26f845558eb4bac5b9aeb439';
$sha1 = 'de3de92d63742178c141a0566f666ae292ba2146';

$sha1 = '01857b01563fd7886227b12881e3386a3e93f6c7';

$sha1 = '0c75c899b5f83fa5a7d6ee9151610b1c8cd432a7';

$sha1 = 'f2d5939966153fae241c5f527c8969915a41db11';

$sha1 = 'ae81bf057335f5854de3adc89f409307df8a96dd';

$sha1 = '2dde5fdd4c3ea48bbff1b2a83e1aed8d9eb25fc7';

$sha1 = '5d59d7bf08fb9fa956c5ec9be9bad8728b4fc5d5';
$sha1 = 'e3b08b555bc0ad82e0e6678b1713647b1c1b8412';
$sha1 = 'adf47e5130c614a8819fd632781a8dc4237dc984';

$sha1 = '8401f872b7f8ea94c81546c20f3c498a61598b59';

$sha1 = '1b25b98a8731e8a9f38b02f3e7cd3e099ce4a742';

$sha1 = '671238e955b2822c637436004f11b05531e050f9';

$sha1 = 'd942161e356d2a0e93bd65d5adcc5e8a4153f604';

$sha1 = 'dae462e656792c736b236d8ebf3a5d9bb5b63938';

$sha1 = '74bf825b0e8075d137295963dd555b406de8ba1f';

$sha1 = 'af98cc4beebeceedb87ab4941643c4a69bd36efc';
$sha1 = '10b4ed54c4544878b952131c3083bdaaaf3ec4a9';

$sha1 = '69c548bec4b7a8b3b542b95801e01e8cb5af2ba2';



{
	pdf_to_text($sha1, 'pdf');
}

?>