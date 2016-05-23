#!/usr/bin/php
<?php
namespace mmFramework;

$fileLocation = preg_replace('{tools.*$}', '', dirname(__FILE__));
require_once($fileLocation . "/init/global.php");

function echo_block($title, $value)
{
  echo_nice(str_pad($title. ': ', 18) . $value);
}


echo_block("Base", DIR_BASE);
echo_block("Project", $config->projectName);

echo_block("Timezone", $config->timezone);
echo_block("Language", $config->language);

if ($config->hasMailConfigured) {
  echo_block("Mail configured", 'yes');
  echo_block("Mailer", $config->mailer);
  echo_block("Error Mail", $config->errorEmail);
} else {
  echo_block("Mail configured", 'no');
}

echo_block("Redis Host", $config->redisHost);

if (empty($config->mailOverRide)) {
  echo_block("MailOverRide", '---');
} else {
  echo_block("MailOverRide", $config->mailOverRide);
}

//
// Database information
//
echo_nice('');
foreach ($config->db as $dbKey => $dbConf) {
  if ($dbConf->dbConnector != 'none') {
    echo_block("Db Config", $dbKey);
    echo_block("Connector", $dbConf->dbConnector);
    echo_block("Host", $dbConf->dbHost);
    echo_block("Username", $dbConf->dbUser);
    echo_block("Password", $dbConf->dbPassword);
    echo_block("Database", $dbConf->dbName);
    echo_nice('');
  }
}


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
