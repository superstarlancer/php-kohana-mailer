# Kohana Email module
PHPMailer driver for kohana

## How to install
clone with --recursive option
Put all files into `modules/email` directory and enable module in `bootstrap.php`:
```php
Kohana::modules(array(
	...
	'email' => MODPATH.'email',
	...
));
```
Then copy `MODPATH/email/config/email.php` to `APPPATH/config/email.php`.
Well done!

## Using
Simple use case::
```php
$email_subject = 'Hi there!';
$email_body = 'Hi, guys! This is my awesome email.';

$message = Email::factory($email_subject, $email_body,$is_html)
	->from('sender@example.com')
	->to('first.recipient@example.com')
	->to('second.recipient@example.com', 'Mr. Recipient', 'CC')
	->to(array(
		'fifth.recipient@example.com',
		'sixth.recipient@example.com'
		)
	)
	->to(array(
		'seventh@example.com'=>'Mr. Recipient',
		'eighth@example.com'=>'Mr. Recipient'
		))
	->attach_file('/path/to/filename.ext','filename_to_send.ext');
$result = $message->send();
if($result) Session::instance()->set('message','Email sent successfully');
```

For advancer using, read classes/Kohana/Email.php - class if self-documented

Enjoy!
