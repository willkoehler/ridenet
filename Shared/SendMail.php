<?
require_once('aws_ses/SimpleEmailService.php');
require_once('aws_ses/SimpleEmailServiceMessage.php');
require_once('aws_ses/SimpleEmailServiceRequest.php');

//----------------------------------------------------------------------------------
//  SendMail()
//
//  Sends email message using Amazon SES
//
//  PARAMETERS:
//    to      - address to send email to. May contain multiple addresses separated by
//              commas or semicolons
//    subject - subject line of email
//    body    - text for body of the email
//    from    - address email will come from (will also be in reply-to field)
//
//  RETURN: none
//-----------------------------------------------------------------------------------
function SendMail($to, $subject, $body, $from)
{
    global $cAWSAccessKey, $cAWSSecretAccessKey;    // defined in AWS-Credentials.php
    global $cRedirectEmailsTo;

    $region_endpoint = SimpleEmailService::AWS_US_EAST_1;
    $trigger_errors = true;
    $signature_version = SimpleEmailService::REQUEST_SIGNATURE_V4;

    $ses = new SimpleEmailService($cAWSAccessKey, $cAWSSecretAccessKey, $region_endpoint, $trigger_errors, $signature_version);
    $m = new SimpleEmailServiceMessage();
    if(isset($cRedirectEmailsTo))
    {
        $subject = "[$to] $subject";
        $to = $cRedirectEmailsTo;
    }
    $to = preg_split("/[;,]+/", $to);     // convert $to from comma/semicolon separated list into array
    foreach($to as $recipient)
    {
        $recipient = trim($recipient);
        if($recipient) { $m->addTo($recipient); }
    }
    $m->setFrom($from);
    $m->setSubject($subject);
    $m->setMessageFromString($body);
    $result = $ses->sendEmail($m);
    if($result==false)
    {
      trigger_error("Failed to send email. SUBJECT: \"$subject\". TO: " . join(',', $to), E_USER_WARNING);
    }
    return ($result==false) ? false : true;
}

?>