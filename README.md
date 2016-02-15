# Kohana Email module

Factory-based email class. This class is a simple wrapper around [PHPMailer](https://github.com/PHPMailer/PHPMailer).

## How to install
clone with `--recursive` option

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
$is_html = false;
$message = Email::factory($email_subject, $email_body, $is_html)
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

or like this
```php
$email_body = '<p>This is <em>my</em> body, it is <strong>nice</strong>.</p>';
$message = Email::factory('Hi there!', $email_body, true);
$message->from('sender@example.com');
$message->to('first.recipient@example.com');
$message->send();
```

Additional senders can be added using the `from()` and `reply_to()` methods. If multiple sender addresses are specified, you need to set the actual sender of the message using the `sender()` method. Set the bounce recipient by using the `return_path()` method.

To access and modify the [PHPMailer message](https://github.com/PHPMailer/PHPMailer) directly, use the `mailer()` method.

For advanced using, read classes/Kohana/Email.php - class is self-documented

## Configuration
```php
return array(
	'driver' => 'native', // native, sendmail, smtp
	/*
	 * Driver options:
	 * @param   null    native: no options
	 * @param   string  sendmail: executable path, with -bs or equivalent attached
	 * @param   array   smtp: hostname, (username), (password), (port), (encryption), (debug) 0-4 ,function($str, $level)(debug_output)
	 */
	'options' => array(),
	'from' => array('noreply@example.com','Example noreply mailer'),
	'charset' => 'UTF-8' // charset
);
```
Configuration is stored in `config/email.php` by default. Options are dependant upon transport method used. Consult PHPMailer documentation for options available to each transport.

Enjoy!
