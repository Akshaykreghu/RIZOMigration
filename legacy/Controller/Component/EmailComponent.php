<?php  
/** 
 * This is a component to send email from CakePHP using PHPMailer 
 * @link http://bakery.cakephp.org/articles/view/94 
 * @see http://bakery.cakephp.org/articles/view/94 
 */ 
 App::import('Vendor', 'phpmailer/PHPMailerAutoload');

class EmailComponent extends Component {
    var $components = array('Session');
	var $controller;
	var $primary = array();
	
	function initialize(Controller $controller){
	    $this->controller = $controller;
	}
	//called after Controller::beforeFilter()
	function startup(Controller $controller) {
	}
	//called after Controller::beforeRender()
	function beforeRender(Controller $controller) {
	}
	//called after Controller::render()
	function shutdown(Controller $controller) {
	}
	//called before Controller::redirect()
	function beforeRedirect(Controller $controller, $url, $status=null, $exit=true) {
	}
	function redirectSomewhere($value) {
	// utilizing a controller method
	    $this->controller->redirect($value);
	}
	
  /** 
   * Send email using SMTP Auth by default. 
   */ 
    var $from         = 'info@forsight.com'; 
    var $fromName     = "Forsight"; 
    var $smtpUserName = 'developer.binesh@gmail.com';  // SMTP username 
    var $smtpPassword = 'developerbinesh'; // SMTP password 
    var $smtpHostNames= "smtp.gmail.com";  // specify main and backup server 
    var $text_body = null; 
    var $html_body = null; 
    var $to = "bineshbabu.t@gmail.com"; 
    var $toName = "Binesh Babu"; 
    var $subject = "Test Mail From Forsight"; 
    var $cc = null; 
    var $bcc = null; 
    var $template = 'registration'; 
    var $attachments = null; 


    function bodyText() { 
    /** This is the body in plain text for non-HTML mail clients 
     */ 
      ob_start(); 
      $temp_layout = $this->controller->layout; 
      $this->controller->layout = '';  // Turn off the layout wrapping 
      $this->controller->render($this->template);  
      $mail = ob_get_clean(); 
      $this->controller->layout = $temp_layout; // Turn on layout wrapping again 
      return $mail; 
    } 

    function bodyHTML() { 
    /** This is HTML body text for HTML-enabled mail clients 
     */ 
      ob_start(); 
      $temp_layout = $this->controller->layout; 
      $this->controller->layout = 'email';  //  HTML wrapper for my html email in /app/views/layouts 
      $this->controller->render($this->template);  
      $mail = ob_get_clean(); 
      $this->controller->layout = $temp_layout; // Turn on layout wrapping again 
      return $mail; 
    } 

    function attach($filename, $asfile = '') { 
      if (empty($this->attachments)) { 
        $this->attachments = array(); 
        $this->attachments[0]['filename'] = $filename; 
        $this->attachments[0]['asfile'] = $asfile; 
      } else { 
        $count = count($this->attachments); 
        $this->attachments[$count+1]['filename'] = $filename; 
        $this->attachments[$count+1]['asfile'] = $asfile; 
      } 
    } 


    function send() 
    { 
    //vendor('phpmailer'.DS.'class.phpmailer'); 
	// vendor('phpmailer'.DS.'PHPMailerAutoload');
	error_reporting(E_STRICT);

    date_default_timezone_set('America/Toronto');
	 
   $mail = new PHPMailer(); 

    $mail->IsSMTP();            // set mailer to use SMTP 
    $mail->SMTPAuth = true;     // turn on SMTP authentication 
    $mail->Host   = $this->smtpHostNames; 
    $mail->Username = $this->smtpUserName; 
    $mail->Password = $this->smtpPassword; 
    $mail->SMTPSecure = "tls";
    $mail->Port = 587;
    $mail->From     = $this->from; 
    $mail->FromName = $this->fromName; 
    $mail->AddAddress($this->to, $this->toName ); 
    $mail->AddReplyTo($this->from, $this->fromName ); 

    $mail->CharSet  = 'UTF-8'; 
    $mail->WordWrap = 50;  // set word wrap to 50 characters 

    if (!empty($this->attachments)) { 
      foreach ($this->attachments as $attachment) { 
        if (empty($attachment['asfile'])) { 
          $mail->AddAttachment($attachment['filename']); 
        } else { 
          $mail->AddAttachment($attachment['filename'], $attachment['asfile']); 
        } 
      } 
    } 

    $mail->IsHTML(true);  // set email format to HTML 

    $mail->Subject = $this->subject; 
    $mail->Body    =  "Hi How are you";// $this->bodyHTML(); 
    $mail->AltBody = "Alt Text"; //$this->bodyText(); 

    $result = $mail->Send(); 

    if($result == false ) $result = $mail->ErrorInfo; 


//echo $result;
    return $result; 
    } 
} 
?>