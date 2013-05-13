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
$sha1 = '8697961e501340f7eb27162d4db48ab0ce779b7c';
$sha1 = 'de3de92d63742178c141a0566f666ae292ba2146';

$sha1 = '79975d6bd366b85299ecf6cac95b404650c383d0';

{
	pdf_to_thumbnails($sha1, 'pdf');
}

?>