<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Email message building and sending.
 *
 * @package		Kohana/PHPMailer
 * @category	 Email
 * @author		 Timophey Lanevich
 * @copyright	(c) 2014 Timophey Lanevich
 * @license		http://kohanaphp.com/license.html
 */
class Kohana_Email{

	/**
	 * @var	object	PHPMailer instance
	 */
	public $_mailer;
	private $_from_changed;
	protected $_config;

	/**
	 * Creates a PHPMailer instance.
	 *
	 * @return  object PHPMailer object
	 */
	public function mailer(){
		if($this->_mailer) return $this->_mailer;
		// else if it not exists
			// create instance of PHPMailer
			$this->_mailer = new PHPMailer;
			$this->config_load();
			return $this->_mailer;
		}

	/**
	* Load kohana config
	*
	* @param	 string	config name
	* @return	Email
	*/
	public function config_load($name='email'){
			// Load email configuration, make sure minimum defaults are set
			$this->_config = Kohana::$config->load($name)->as_array() + array(
				'driver'  => 'native',
				'options' => array(),
				'charset' => 'UTF-8',
				'from' => null
			);
			if($this->_config['driver']=='smtp'){
				$this->_config['options'] += array(
				'hostname'=>null,
				'username'=>null,
				'password'=>null,
				'port'=>25,
				'encryption'=>'',
				'debug' => 0,
				'debug_output' => function($str, $level) {
					Log::instance()->add(Log::DEBUG, 'Email(:level): :message',[':level'=>$level,':message'=>$str]);
					},
				);
				}
			// Extract configured options
			extract($this->_config, EXTR_SKIP);
			$this->_mailer->CharSet = $charset;
			// driver SMTP
			if($driver === 'smtp'){
				$this->_mailer->isSMTP();
				$this->_mailer->Host = $options['hostname'];
				if(isset($options['username']) && isset($options['password'])){
					$this->_mailer->SMTPAuth = true;
					$this->_mailer->Username = $options['username'];
					$this->_mailer->Password = $options['password'];
					}
				$this->_mailer->SMTPSecure = $options['encryption'];
				$this->_mailer->Port = $options['port'];
				$this->_mailer->SMTPDebug = $options['debug'];
				$this->_mailer->Debugoutput = $options['debug_output'];
				$this->_mailer->SMTPAutoTLS = false;
				$this->_mailer->SMTPOptions = array('ssl' => [
						'verify_peer' => false,
						'verify_peer_name' => false,
						'allow_self_signed' => true
						]);
			// driver sendmail
			}elseif($driver === 'sendmail'){
				$this->_mailer->isSendmail();
				}
			// set from addr
			//if($from) call_user_func_array([$this,'from'],(is_array($from)?((isAssoc($from)?[$from]:$from)$from):[$from]))
			if($from){
				$from_arrgs = is_array($from)?(Arr::is_assoc($from)?[$from]:$from) : [$from];
				call_user_func_array([$this,'from'],$from_arrgs);
				}
		return $this;
		}
	/**
	* Create a new email message.
	*
	* @param	 string	message subject
	* @param	 string	message body
	* @param	 string	body mime type
	* @return	Email
	*/
	public static function factory($subject = NULL, $message = NULL, $isHTML = NULL){
		return new Email($subject, $message, $isHTML);
	}

	/**
	 * Initialize a new PHPMailer, set the subject and body.
	 *
	 * @param	 string	message subject
	 * @param	 string	message body
	 * @param	 bool is message HTML
	 * @return	void
	 */	
	public function __construct($subject = NULL, $message = NULL, $isHTML = false){
		
		$this->mailer();
		
		if($subject){
			// Apply subject
			$this->_mailer->Subject = $subject;
			}
		if($message){
			// Apply message and type
			$this->_mailer->Body = $message;
			if($isHTML) $this->_mailer->AltBody = strip_tags($message);
			if($isHTML !== null) $this->_mailer->isHTML(false);
			}
		}
	/**
	 * Add one or more email recipients..
	 *
	 *		 // A single recipient
	 *		 $email->to('john.doe@domain.com', 'John Doe');
	 *
	 *		 // Multiple entries
	 *		 $email->to(array(
	 *				 'frank.doe@domain.com',
	 *				 'jane.doe@domain.com' => 'Jane Doe',
	 *		 ));
	 *
	 *		 $email->to(array(
	 *				 'frank.doe@domain.com',
	 *				 'jane.doe@domain.com' => 'Jane Doe',
	 *		 ),'CC');
	 *
	 * @param	 mixed		single email address or an array of addresses
	 * @param	 string	 full name
	 * @param	 string	 recipient type: Address, CC, BCC
	 * @return	Email
	 */

	public function to($email, $name = NULL, $type = 'Address'){
		if (is_array($email))
		{
			foreach ($email as $key => $value)
			{
				if (ctype_digit((string) $key))
				{
					// Only an email address, no name
					$this->to($value, NULL, $type);
				}
				else
				{
					// Email address and name
					$this->to($key, $value, $type);
				}
			}
		}
		else
		{
			// Call $this->_message->{add$Type}($email, $name)
			call_user_func(array($this->_mailer, 'add'.$type), $email, $name);
		}

		return $this;		
		}
	/**
	 * Add a "carbon copy" email recipient.
	 *
	 * @param	 string	 email address
	 * @param	 string	 full name
	 * @return	Email
	 */
	public function cc($email, $name = NULL){
		return $this->to($email, $name, 'CC');
	}

	/**
	 * Add a "blind carbon copy" email recipient.
	 *
	 * @param	 string	 email address
	 * @param	 string	 full name
	 * @return	Email
	 */
	public function bcc($email, $name = NULL){
		return $this->to($email, $name, 'BCC');
	}

	/**
	 * Add one or more email senders.
	 *
	 *		 // A single sender
	 *		 $email->from('john.doe@domain.com', 'John Doe');
	 *
	 *		 // Multiple entries
	 *		 $email->from(array(
	 *				 'frank.doe@domain.com',
	 *				 'jane.doe@domain.com' => 'Jane Doe',
	 *		 ));
	 *
	 * @param	 mixed		single email address or an array of addresses
	 * @param	 string	 full name
	 * @return	Email
	 */
	public function from($email, $name = NULL){
		if (is_array($email))
		{
			foreach ($email as $key => $value)
			{
				if (ctype_digit((string) $key))
				{
					// Only an email address, no name
					$this->from($value, NULL, $type);
				}
				else
				{
					// Email address and name
					$this->from($key, $value, $type);
				}
			}
		}
		else
		{
			if($this->_from_changed){
				// Call $this->_message->{add$Type}($email, $name)
				call_user_func(array($this->_mailer, 'addReplyTo'), $email, $name);
			}else{
				// Call $this->_message->setFrom($address, $name = '', $auto = true)
				call_user_func(array($this->_mailer, 'setFrom'), $email, $name);
				$this->_from_changed = true;
				}
		}

		return $this;
	}

	/**
	 * Add "reply to" email sender.
	 *
	 * @param	 string	 email address
	 * @param	 string	 full name
	 * @return	Email
	 */
	public function reply_to($email, $name = NULL){
		return $this->from($email, $name, 'ReplyTo');
	}

	/**
	 * Set the return path for bounce messages.
	 *
	 * @param	 string	email address
	 * @return	Email
	 */
	public function return_path($email){
		$this->_mailer->ReturnPath = $email;
		return $this;
	}

	/**
	 * Set the return path for bounce messages.
	 *
	 * @param	 string	email address
	 * @return	Email
	 */
	public function sender($email){
		$this->_mailer->Sender = $email;
		return $this;
	}

	/**
	 * Attach a file.
	 *
	 * @param string $path Path to the attachment.
	 * @param string $name Overrides the attachment name.
	 * @return	Email
	 */
	public function attach_file($path, $name = null){
		$this->_mailer->addAttachment($path, $name);
		return $this;
		}

		/**
		 * Create a message and send it.
		 * Uses the sending method specified by $Mailer.
		 * @throws phpmailerException
		 * @return boolean false on error - See the ErrorInfo property for details of the error.
		 */
	public function send(){
		return $this->_mailer->send();
		}
	}
