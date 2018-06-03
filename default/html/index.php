<?php
require_once("../init/config.php");

$tempateArray = array(
    'content' => 'index.tpl',
);

$template->setTemplate("base5.tpl", $tempateArray);
$template->assign('pageTitle', 'Demo');
echo $template->output();
