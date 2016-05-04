#!/usr/bin/php
<?php
namespace mmFramework;

$fileLocation = preg_replace('{tools.*$}', '', dirname(__FILE__));
require_once($fileLocation . "/init/global.php");

function echo_block($title, $value)
{
  echo_nice(str_pad($title. ': ', 15) . $value);
}


echo_block("Base", DIR_BASE);
echo_block("Project", $config->projectName);

echo_block("Timezone", $config->timezone);
echo_block("Language", $config->language);
echo_block("Mail", $config->hasMailConfigured);
if ($config->hasMailConfigured) {
  echo_block("Mailer", $config->mailer);
  echo_block("Error Mail", $config->errorEmail);
}
echo_block("Redis Host", $config->redisHost);
echo_block("MailOverRide", $config->mailOverRide);


//
// Test Mail
//
if ($config->hasMailConfigured) {
  $hError = new ErrorHandle(ErrorHandle::CLI | ErrorHandle::MAIL);

  $hError->no       = 4177;
  $hError->string   = 'Test Error';
  $hError->file     = __FILE__;
  $hError->line     = __LINE__;
  $hError->context  = array();
  $hError->mailTo   = $config->errorEmail;
  $hError->output();
  echo_block("Trigger Error", 'yes');
} else {
  echo_block("Trigger Error", 'no');
}
