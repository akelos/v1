<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

// +----------------------------------------------------------------------+
// | Akelos Framework - http://www.akelos.org                             |
// +----------------------------------------------------------------------+
// | Copyright (c) 2002-2006, Akelos Media, S.L.  & Bermi Ferrer Martinez |
// | Released under the GNU Lesser General Public License, see LICENSE.txt|
// +----------------------------------------------------------------------+

/**
 * @package AkelosFramework
 * @subpackage AkActionMailer
 * @author Bermi Ferrer <bermi a.t akelos c.om>
 * @copyright Copyright (c) 2002-2006, Akelos Media, S.L. http://www.akelos.org
 * @license GNU Lesser General Public License <http://www.gnu.org/copyleft/lesser.html>
 */

require_once(AK_LIB_DIR.DS.'AkBaseModel.php');
require_once(AK_LIB_DIR.DS.'AkActionMailer'.DS.'AkMail.php');
require_once(AK_LIB_DIR.DS.'AkActionMailer'.DS.'AkMailParser.php');
require_once(AK_LIB_DIR.DS.'AkActionMailer'.DS.'AkActionMailerQuoting.php');

ak_define('MAIL_EMBED_IMAGES_AUTOMATICALLY_ON_EMAILS', false);
ak_define('ACTION_MAILER_DEFAULT_CHARSET', AK_CHARSET);
ak_define('ACTION_MAILER_EOL', "\r\n");
ak_define('ACTION_MAILER_EMAIL_REGULAR_EXPRESSION', "([a-z0-9_\-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([a-z0-9\-]+\.)+))([a-z]{2,4}|[0-9]{1,3})(\]?)");
ak_define('ACTION_MAILER_RFC_2822_DATE_REGULAR_EXPRESSION', "(?:(Mon|Tue|Wed|Thu|Fri|Sat|Sun), *)?(\d\d?) (Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec) (\d\d\d\d) (\d{2}:\d{2}(?::\d\d)) (UT|GMT|EST|EDT|CST|CDT|MST|MDT|PST|PDT|[A-Z]|(?:\+|\-)\d{4})");

/**
* AkActionMailer allows you to send email from your application using a mailer model and views.
*
* = Mailer Models
*
* To use AkActionMailer, you need to create a mailer model.
*   
*   $ script/generate mailer Notifier
*
* The generated model inherits from AkActionMailer. Emails are defined by creating methods within the model which are then 
* used to set variables to be used in the mail template, to change options on the mail, or 
* to add attachments.
*
* Examples:
*
*  class Notifier extends AkActionMailer
*  {
*    function signupNotification($Recipient)
*    {
*      $this->setRecipients($Recipient->getEmailAddressWithName());
*      $this->setFrom("system@example.com");
*      $this->setSubject("New account information");
*      $this->setBody(array('account' => $Recipient ));
*    }
*  }
*
* Mailer methods have the following configuration methods available.
*
* * <tt>setRecipients</tt> - Takes one or more email addresses. These addresses are where your email will be delivered to. Sets the <tt>To:</tt> header.
* * <tt>setSubject</tt> - The subject of your email. Sets the <tt>Subject:</tt> header.
* * <tt>setFrom</tt> - Who the email you are sending is from. Sets the <tt>From:</tt> header.
* * <tt>setCc</tt> - Takes one or more email addresses. These addresses will receive a carbon copy of your email. Sets the <tt>Cc:</tt> header.
* * <tt>setBcc</tt> - Takes one or more email address. These addresses will receive a blind carbon copy of your email. Sets the <tt>Bcc</tt> header.
* * <tt>setSentOn</tt> - The date on which the message was sent. If not set, the header wil be set by the delivery agent.
* * <tt>setContentType</tt> - Specify the content type of the message. Defaults to <tt>text/plain</tt>.
* * <tt>setHeaders</tt> - Specify additional headers to be set for the message, e.g. <tt>$this->setHeaders(array('X-Mail-Count' => 107370));</tt>.
*
* The <tt>setBody</tt> method has special behavior. It takes an array which generates an instance variable
* named after each key in the array containing the value that that key points to.
*
* So, for example, <tt>setBody(array("account" => $Recipient));</tt> would result
* in an instance variable <tt>$account</tt> with the value of <tt>$Recipient</tt> being accessible in the 
* view.
*
*
* = Mailer views
*
* Like AkAkActionController, each mailer class has a corresponding view directory
* in which each method of the class looks for a template with its name.
* To define a template to be used with a mailing, create an <tt>.tpl</tt> file with the same name as the method
* in your mailer model. For example, in the mailer defined above, the template at 
* <tt>app/views/notifier/signup_notification.tpl</tt> would be used to generate the email.
*
* Variables defined in the model are accessible as instance variables in the view.
*
* Emails by default are sent in plain text, so a sample view for our model example might look like this:
*
*   Hi {account.name},
*   Thanks for joining our service! Please check back often.
*
* You can even use Action View helpers in these views. For example:
*
*   You got a new note!
*   <?=$text_helper->truncate($note->body, 25);?>
* 
*
* = Generating URLs for mailer views
*
* If your view includes URLs from the application, you need to use Ak::urlFor in the mailing method instead of the view.
* Unlike controllers from Action View, the mailer instance doesn't have any context about the incoming request. That's
* why you need to jump this little hoop and supply all the details needed for the URL. Example:
*
*   function signupNotification($Recipient)
*   {
*       // This is the same as calling each individual setter
*       $this->setAttributes(array(
*           'recipients' => $Recipient->getEmailAddressWithName(),
*           'from'       => "system@example.com",
*           'subject'    => "New account information",
*           'body'       => array(
*               'account'   => $Recipient,
*               'home_page' => Ak::urlFor(array('host' => "example.com", 'controller' => "welcome", 'action' => "greeting"))
*           )
*       ));
*   }
*
* You can now access @home_page in the template and get http://example.com/welcome/greeting.
*
* = Sending mail
*
* Once a mailer action and template are defined, you can deliver your message or create it and save it 
* for delivery later:
*
*   Notifier::deliver('signup_notification', $David); // sends the email
*   $Mail = Notifier::create('signupNotification', $David); // => A PEAR::Mail object
*   Notifier::deliver($Mail);
* 
* You never instantiate your mailer class. Rather, your delivery instance
* methods are automatically wrapped in class methods that are called statically
* The <tt>signup_notification</tt> method defined above is
* delivered by invoking <tt>$Notifier =& new Notifier(); $Notifier->signupNotification(); $Notifier->deliver();</tt>.
*
*
* = HTML email
*
* To send mail as HTML, make sure your view (the <tt>.tpl</tt> file) generates HTML and
* set the content type to html.
*
*   class ApplicationMailer extends AkActionMailer
*   {
*       function signupNotification($Recipient)
*       {
*           $this->setAttributes(array(
*               'recipients' => $Recipient->getEmailAddressWithName(),
*               'from'       => "system@example.com",
*               'subject'    => "New account information",
*               'body'       => array('account'   => $Recipient),
*               'content_type' => text/html" //    Here's where the magic happens 
*           ));
*       }
*   }
*
*
* = Multipart email
*
* You can explicitly specify multipart messages:
*
*   class ApplicationMailer extends AkActionMailer
*   {
*       function signupNotification($Recipient)
*       {
*           $this->setAttributes(array(
*               'recipients' => $Recipient->getEmailAddressWithName(),
*               'from'       => "system@example.com",
*               'subject'    => "New account information"
*           ));
* 
*           $this->addPart(array(
*               'content_type' => "text/html",
*               'body' => $this->renderMessage('signup-as-html', 'account' => $recipient)));
* 
*           $this->addPart("text/plain", array(
*               'transfer_encoding' = "base64",
*               'body' => $this->renderMessage('signup-as-plain', 'account' => $recipient)));
*       }
*   }

*  
* Multipart messages can also be used implicitly because AkActionMailer will automatically
* detect and use multipart templates, where each template is named after the name of the action, followed
* by the content type. Each such detected template will be added as separate part to the message.
* 
* For example, if the following templates existed:
* * signup_notification.text.plain.tpl
* * signup_notification.text.html.tpl
*  
* Each would be rendered and added as a separate part to the message,
* with the corresponding content type. The same body array is passed to
* each template.
*
*
* = Attachments
*
* Attachments can be added by using the +addAttachment+ method.
*
* Example:
*
*   class ApplicationMailer extends AkActionMailer
*   {
*       // attachments
*       function signupNotification($Recipient)
*       {
*           $this->setAttributes(array(
*               'recipients' => $Recipient->getEmailAddressWithName(),
*               'from'       => "system@example.com",
*               'subject'    => "New account information"
*           ));
*
*           $this->addAttachment(array(
*               'content_type' => 'image/jpeg',
*               'body' => Ak::file_get_contents("an-image.jpg")));
* 
*           $this->addAttachment('application/pdf', generate_your_pdf_here());
*       }
*   }
*
*
* = Configuration options
*
* These options are specified on the class level, as class attriibutes <tt>$AkActionMailerInstance->templateRoot = "/my/templates";</tt>
*
* * <tt>templateRoot</tt> - template root determines the base from which template references will be made.
*
* * <tt>server_settings</tt> -  Allows detailed configuration of the server:
*   * <tt>address</tt> Allows you to use a remote mail server. Just change it from its default "localhost" setting.
*   * <tt>port</tt> On the off chance that your mail server doesn't run on port 25, you can change it.
*   * <tt>domain</tt> If you need to specify a HELO domain, you can do it here.
*   * <tt>user_name</tt> If your mail server requires authentication, set the username in this setting.
*   * <tt>password</tt> If your mail server requires authentication, set the password in this setting.
*   * <tt>authentication</tt> If your mail server requires authentication, you need to specify the authentication type here. 
*     This is a symbol and one of :plain, :login, :cram_md5
*
* * <tt>delivery_method</tt> - Defines a delivery method. Possible values are 'smtp' (default), 'php', and 'test'.
*
* * <tt>perform_deliveries</tt> - Determines whether AkActionMailer::deliver(*) methods are actually carried out. By default they are,
*   but this can be turned off to help functional testing.
*
* * <tt>deliveries</tt> - Keeps an array of all the emails sent out through the Action Mailer with delivery_method 'test'. Most useful
*   for unit and functional testing.
*
* * <tt>default_charset</tt> - The default charset used for the body and to encode the subject. Defaults to UTF-8. You can also 
*   pick a different charset from inside a method with <tt>$this->charset</tt>.
* * <tt>default_content_type</tt> - The default content type used for the main part of the message. Defaults to "text/plain". You
*   can also pick a different content type from inside a method with <tt>$this->content_type</tt>. 
* * <tt>default_mime_version</tt> - The default mime version used for the message. Defaults to "1.0". You
*   can also pick a different value from inside a method with <tt>$this->mime_version</tt>.
* * <tt>default_implicit_parts_order</tt> - When a message is built implicitly (i.e. multiple parts are assembled from templates
*   which specify the content type in their filenames) this variable controls how the parts are ordered. Defaults to
*   array("text/html", "text/enriched", "text/plain"). Items that appear first in the array have higher priority in the mail client
*   and appear last in the mime encoded message. You can also pick a different order from inside a method with
*   <tt>$this->implicit_parts_order</tt>.
*/
class AkActionMailer extends AkBaseModel
{
    var $templateRoot;
    var $server_settings = array(
    'address'        => 'localhost',
    'port'           => 25,
    'domain'         => 'localhost.localdomain',
    'user_name'      => null,
    'password'       => null,
    'authentication' => null
    );

    var $delivery_method = 'php';
    var $perform_deliveries = true;
    var $deliveries = array();
    var $default_charset = AK_ACTION_MAILER_DEFAULT_CHARSET;
    var $default_content_type = 'text/plain';
    var $default_mime_version = '1.0';
    var $default_implicit_parts_order = array('text/html', 'text/enriched', 'text/plain');
    var $helpers = array('mail');
    var $_MailDriver;
    var $_defaultMailDriverName = 'AkMail';

    function __construct($Driver = null)
    {
        if(empty($Driver)){
            $this->_MailDriver =& new $this->_defaultMailDriverName();
        }else{
            $this->_MailDriver =& $Driver;
        }
    }


    /**
    * Specify the template name to use for current message. This is the "base"
    * template name, without the extension or directory, and may be used to
    * have multiple mailer methods share the same template.
    */
    function setTemplate($template_name)
    {
        $this->template = $template_name;
    }

    /**
    * Override the mailer name, which defaults to an inflected version of the
    * mailer's class name. If you want to use a template in a non-standard
    * location, you can use this to specify that location.
    */
    function setMailerName($mailerName)
    {
        $this->mailerName = $mailerName;
    }

    // Mail object specific setters


    /**
    * Define the body of the message. This is either an array (in which case it
    * specifies the variables to pass to the template when it is rendered),
    * or a string, in which case it specifies the actual text of the message.
    */
    function setBody($body)
    {
        if(is_array($body) && count($body) == 1 && array_key_exists(0,$body)){
            $body = $body[0];
        }
        $this->_MailDriver->setBody($body);
    }

    /**
    * Specify the CC addresses for the message.
    */
    function setCc($cc)
    {
        $this->_MailDriver->setCc($cc);
    }

    /**
    * Specify the BCC addresses for the message.
    */    
    function setBcc($bcc)
    {
        $this->_MailDriver->setBcc($bcc);
    }
    /**
     * Specify the charset to use for the message. This defaults to the
     *  +default_charset+ specified for AkActionMailer.
     */
    function setCharset($charset)
    {
        $this->_MailDriver->setCharset($charset);
    }

    /**
     * Specify the content type for the message. This defaults to <tt>text/plain</tt>
     * in most cases, but can be automatically set in some situations.
     */
    function setContentType($content_type)
    {
        $this->_MailDriver->setContentType($content_type);
    }

    /**
     * Specify the from address for the message.
     */
    function setFrom($from)
    {
        $this->_MailDriver->setFrom($from);
    }

    /**
     * Specify additional headers to be added to the message.
     */
    function setHeaders($headers)
    {
        $this->_MailDriver->setHeaders($headers);
    }

    /**
    * Specify the order in which parts should be sorted, based on content-type.
    * This defaults to the value for the +default_implicit_parts_order+.
    */
    function setImplicitPartsOrder($implicit_parts_order)
    {
        $this->_MailDriver->setImplicitPartsOrder($implicit_parts_order);
    }


    /**
     * Defaults to "1.0", but may be explicitly given if needed.
     */
    function setMimeVersion($mime_version)
    {
        $this->_MailDriver->setMimeVersion($mime_version);
    }

    /**
     * The recipient addresses for the message, either as a string (for a single
     * address) or an array (for multiple addresses).
     */
    function setRecipients($recipients)
    {
        $this->_MailDriver->setRecipients($recipients);
    }

    /**
    * The date on which the message was sent. If not set (the default), the
    * header will be set by the delivery agent.
    */
    function setSentOn($date)
    {
        $this->_MailDriver->setSentOn($date);
    }


    /**
     * Specify the subject of the message.
     */
    function setSubject($subject)
    {
        $this->_MailDriver->setSubject($subject);
    }

    /**
     * Generic setter
     * 
     * Calling $this->set(array('body'=>'Hello World', 'subject' => 'First subject'));
     * is the same as calling $this->setBody('Hello World'); and $this->setSubject('First Subject');
     * 
     * This simplifies creating mail objects from datasources.
     * 
     * If the methos does not exists the parameter will be added to the body.
     */
    function set($attributes = array())
    {
        if(!empty($attributes['template'])){
            $this->setTemplate($attributes['template']);
            unset($attributes['template']);
        }
        $this->_MailDriver->set($attributes);
    }

    /**
     * Gets a well formed mail in plain text
     */
    function getEncoded()
    {
        $this->_MailDriver->getEncoded();
    }

    /**
     * The mail object instance referenced by this mailer.
     */
    function &getMail()
    {
        return $this->_MailDriver;
    }


    /**
     * Receives a raw email, parses it into an email object, decodes it,
     * instantiates a new mailer, and passes the email object to the mailer
     * object's #receive method. If you want your mailer to be able to
     * process incoming messages, you'll need to implement a #receive
     * method that accepts the email object as a parameter and then call
     * the AkActionMailer::recieve method using "parent::recieve($Mail);"
     * 
     *
     *   class MyMailer extends AkActionMailer{
     *     function receive($Mail){
     *          parent::recieve($Mail);
     *       ...
     *     }
     *   }
     */
    function receive($raw_mail)
    {
        $this->_MailDriver =& AkMail::parse($raw_mail);
        return $this->_MailDriver;
    }
    

    /**
     * Deliver the given mail object directly. This can be used to deliver
     * a preconstructed mail object, like:
     *
     *   $email =& $MyMailer->createSomeMail($parameters);
     *   $email->setHeader("frobnicate");
     *   MyMailer::deliver($email);
     */
    function deliverDirectly(&$Mail)
    {
        $Mail =& new AkMail($Mail);
        $Mail->send();
    }

    /**
     * Instantiate a new mailer object. If +method_name+ is not +null+, the mailer
     * will be initialized according to the named method. If not, the mailer will
     * remain uninitialized (useful when you only need to invoke the "receive"
     * method, for instance).
     */
    function initialize($method_name = null, $parameters)
    {
        //create!(method_name, *parameters) if method_name
    }

    /**
     * Initialize the mailer via the given +method_name+. The body will be
     * rendered and a new AkMail object created.
     */
    function &create($method_name, $parameters)
    {
        $this->_initializeDefaults($method_name);
        if(method_exists($this, $method_name)){
            $this->$method_name($parameters);
        }else{
            trigger_error(Ak::t('Could not find the method %method on the model %model', array('%method'=>$method_name, '%model'=>$this->getModelName())), E_USER_ERROR);
        }

        $Mail =& $this->_MailDriver;

        if(!is_string($Mail->body)){
            if(empty($Mail->parts)){
                $templates = array_map('basename', Ak::dir($this->getTemplatePath().DS, array('dirs'=>false)));

                foreach ($templates as $template_name){
                    if(preg_match('/^([^\.]+)\.([^\.]+\.[^\.]+)\.(tpl)$/',$template_name, $match)){
                        if($this->template == $match[1]){
                            $content_type = str_replace('.','/', $match[2]);
                            $Mail->setPart(array(
                            'content_type' => $content_type,
                            'disposition' => 'inline',
                            'charset' => $Mail->charset,
                            'body' => $this->renderMessage($template_name, $Mail->body, array('use_full_path'=>true))));
                        }
                    }
                }
                if(!empty($this->parts)){
                    $Mail->content_type = 'multipart/alternative';
                    $Mail->setParts($Mail->sortParts($Mail->parts, $Mail->implicit_parts_order));
                }
            }

            $template_exists = empty($Mail->parts);
            if(!$template_exists){
                $templates = array_map('basename', Ak::dir($this->getTemplatePath(), array('dirs'=>false)));
                foreach ($templates as $template){
                    $parts = explode('.',$template);
                    if(count($parts) == 2 && $parts[0] == $this->template){
                        $template_exists = true;
                    }
                }
            }

            if($template_exists){
                $Mail->setBody($this->renderMessage($this->template, $Mail->body));
            }

            if (!empty($Mail->parts) && is_string($Mail->body)){
                array_unshift($Mail->parts, array('charset' => $Mail->charset, 'body' => $Mail->body));
                $this->body = null;
            }
        }

        $Mail->setMimeVersion((empty($Mail->mime_version) && !empty($Mail->parts)) ? '1.0' : $Mail->mime_version);

        $this->Mail =& $Mail;
        return $Mail;
    }

    /**
    * Delivers an AMail object. By default, it delivers the cached mail
    * object (from the AkActionMailer::create method). If no cached mail object exists, and
    * no alternate has been given as the parameter, this will fail.
    */
    function deliver($method_name, $parameters = null, $Mail = null)
    {
        if(empty($Mail) && empty($this->Mail)){
            $this->create($method_name, $parameters);
        }elseif(!empty($Mail)){
            $this->Mail =& $Mail;
        }

        !empty($this->Mail) or trigger_error(Ak::t('No mail object available for delivery!'), E_USER_ERROR);
        if(!empty($this->perform_deliveries)){
            $this->{"perform".ucfirst(strtolower($this->delivery_method))."Delivery"}($this->Mail);
        }
        return $this->Mail;
    }

    function performSmtpDelivery()
    {
    }

    function performPhpDelivery()
    {
    }

    function performTestDelivery(&$Mail)
    {
        $this->deliveries[] =& $Mail->getEncoded();
    }


    /**
    * Set up the default values for the various instance variables of this
    * mailer. Subclasses may override this method to provide different
    * defaults.
    */
    function _initializeDefaults($method_name)
    {
        $Mail =& $this->_MailDriver;
        foreach (array('charset','content_type','implicit_parts_order', 'mime_version') as $attribute) {
            $method = 'set'.AkInflector::camelize($attribute);
            $Mail->$method(empty($this->$attribute) ? $this->{'default_'.$attribute} : $this->$attribute);
        }
        foreach (array('parts','headers','body') as $attribute) {
            $method = 'set'.AkInflector::camelize($attribute);
            $Mail->$method(empty($this->$attribute) ? array() : $this->$attribute);
        }
        $this->templateRoot = empty($this->templateRoot) ? AK_APP_DIR.DS.'views' : $this->templateRoot;
        $this->template = empty($this->template) ? $method_name : $this->template;
        $this->mailerName = empty($this->mailerName) ? AkInflector::underscore($this->getModelName()) : $this->mailerName;
    }

    function renderMessage($method_name, $body, $options = array())
    {
        return $this->render(array_merge($options, array('file' => $method_name, 'body' => $body)));
    }

    function render($options = array())
    {
        $body = $options['body'];
        unset($options['body']);
        $Template =& $this->_initializeTemplateClass($body);
        $options['locals'] = array_merge((array)@$options['locals'], $this->getHelpers());
        $options['locals'] = array_merge($options['locals'], array('mailer'=>&$this));
        return $Template->render($options);
    }

    function getTemplatePath()
    {
        return $this->templateRoot.DS.$this->mailerName;
    }

    function &_initializeTemplateClass($assigns)
    {
        require_once(AK_LIB_DIR.DS.'AkActionView.php');
        $TemplateInstance =& new AkActionView($this->getTemplatePath(), $assigns, $this);
        require_once (AK_LIB_DIR.DS.'AkActionView'.DS.'AkPhpTemplateHandler.php');
        $TemplateInstance->_registerTemplateHandler('tpl','AkPhpTemplateHandler');
        return $TemplateInstance;
    }


    function _loadMailConnector($options)
    {
        if(empty($this->Connector)){
            $settings = array(
            'host'     =>  $options['server'],
            'port'     =>  $options['port'],
            'auth'     =>  (bool)$options['authentication'],
            'username' =>  $options['username'],
            'password' =>  $options['password'],
            'debug'    =>  true
            );
            $this->Connector =& Mail::factory('smtp', $settings);
        }
        return $this->Connector;
    }

    function _checkIfEmailContactExistsAsPerson(&$EmailContact)
    {
        if(!empty($EmailContact) && strtolower($EmailContact->getModelName()) == 'person'){
            $EmailContact =& new EmailContact(array('name'=>$EmailContact->get('name'), 'email'=>$EmailContact->get('email')));
        }
    }

    /**
     * Alias for getModelName
     */
    function getMailerName()
    {
        return $this->getModelName();
    }


    /**
     * Creates an instance of each available helper and links it into into current mailer.
     * 
     * Mailer helpers work as Controller helpers but without the Request context
     */
    function &getHelpers()
    {
        static $helpers = array();
        require_once(AK_LIB_DIR.DS.'AkActionView'.DS.'AkActionViewHelper.php');

        $mailer_helpers = array_merge(Ak::toArray($this->helpers), array(substr($this->getModelName(),-6)));
        $mailer_helpers = array_unique(array_map(array('AkInflector','underscore'), $mailer_helpers));

        foreach ($mailer_helpers as $file => $mailer_helper){
            $full_path = preg_match('/[\\\\\/]+/',$file);
            $helper_class_name = AkInflector::camelize($mailer_helper).'Helper';
            $attribute_name = (!$full_path ? AkInflector::underscore($helper_class_name) : substr($file,0,-4));
            if(empty($helpers[$attribute_name])){
                if($full_path){
                    include_once($file);
                }else{
                    $helper_file_name = $mailer_helper.'_helper.php';
                    if(file_exists(AK_LIB_DIR.DS.'AkActionView'.DS.'helpers'.DS.$helper_file_name)){
                        include_once(AK_LIB_DIR.DS.'AkActionView'.DS.'helpers'.DS.$helper_file_name);
                    }elseif (file_exists(AK_HELPERS_DIR.DS.$helper_file_name)){
                        include_once(AK_HELPERS_DIR.DS.$helper_file_name);
                    }
                }

                if(class_exists($helper_class_name)){
                    if(empty($helpers[$attribute_name])){
                        $helpers[$attribute_name] =& new $helper_class_name(&$this);
                        if(method_exists($helpers[$attribute_name],'setController')){
                            $helpers[$attribute_name]->setController(&$this);
                        }
                        if(method_exists($helpers[$attribute_name],'setMailer')){
                            $helpers[$attribute_name]->setMailer(&$this);
                        }
                        if(method_exists($helpers[$attribute_name],'init')){
                            $helpers[$attribute_name]->init();
                        }
                    }
                }
            }
        }

        return $helpers;
    }

}

?>