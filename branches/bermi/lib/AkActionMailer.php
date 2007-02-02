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

ak_define('MAIL_HEADER_EOL', "\n");
ak_define('EMAIL_REGULAR_EXPRESSION', "/^([a-z0-9_\-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([a-z0-9\-]+\.)+))([a-z]{2,4}|[0-9]{1,3})(\]?)$/i");

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
* These options are specified on the class level, as class attriibutes <tt>$AkActionMailerInstance->template_root = "/my/templates";</tt>
*
* * <tt>template_root</tt> - template root determines the base from which template references will be made.
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
* * <tt>raise_delivery_errors</tt> - whether or not errors should be raised if the email fails to be delivered.
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
    var $template_root;
    var $server_settings = array(
    'address'        => 'localhost',
    'port'           => 25,
    'domain'         => 'localhost.localdomain',
    'user_name'      => null,
    'password'       => null,
    'authentication' => null
    );
    var $raise_delivery_errors = true;
    var $delivery_method = 'php';
    var $perform_deliveries = true;
    var $deliveries = array();
    var $default_charset = 'utf-8';
    var $default_content_type = 'text/plain';
    var $default_mime_version = '1.0';
    var $default_implicit_parts_order = array('text/html', 'text/enriched', 'text/plain');
    var $helpers = array('mail');

    function setBcc($bcc)
    {
        $this->bcc = $bcc;
    }

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
        $this->body = $body;
    }

    /**
    * Specify the CC addresses for the message.
    */
    function setCc($cc)
    {
        $this->cc = $cc;
    }

    /**
     * Specify the charset to use for the message. This defaults to the
     *  +default_charset+ specified for AkActionMailer.
     */
    function setCharset($charset)
    {
        $this->charset = $charset;
    }

    /**
     * Specify the content type for the message. This defaults to <tt>text/plain</tt>
     * in most cases, but can be automatically set in some situations.
     */
    function setContentType($content_type)
    {
        $this->content_type = $content_type;
    }

    /**
     * Specify the from address for the message.
     */
    function setFrom($from)
    {
        $this->from = $from;
    }

    /**
     * Specify additional headers to be added to the message.
     */
    function setHeaders($headers)
    {
        $this->headers = $headers;
    }

    /**
    * Specify the order in which parts should be sorted, based on content-type.
    * This defaults to the value for the +default_implicit_parts_order+.
    */
    function setImplicitPartsOrder($implicit_parts_order)
    {
        $this->implicit_parts_order = $implicit_parts_order;
    }

    /**
    * Override the mailer name, which defaults to an inflected version of the
    * mailer's class name. If you want to use a template in a non-standard
    * location, you can use this to specify that location.
    */
    function setMailerName($mailer_name)
    {
        $this->mailer_name = $mailer_name;
    }

    /**
     * Defaults to "1.0", but may be explicitly given if needed.
     */
    function setMimeVersion($mime_version)
    {
        $this->mime_version = $mime_version;
    }

    /**
     * The recipient addresses for the message, either as a string (for a single
     * address) or an array (for multiple addresses).
     */
    function setRecipients($recipients)
    {
        $this->recipients = $recipients;
    }

    /**
    * The date on which the message was sent. If not set (the default), the
    * header will be set by the delivery agent.
    */
    function setSentOn($date)
    {
        $this->sent_on = $date;
    }


    /**
     * Specify the subject of the message.
     */
    function setSubject($subject)
    {
        $this->subject = $subject;
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
        $body = (array)@$attributes['body'];
        unset($attributes['body']);
        foreach ((array)$attributes as $key=>$value){
            if($key[0] != '_'){
                $method = 'set'.AkInflector::camelize($key);
                if(method_exists($this, $method)){
                    $this->$method($value);
                }else{
                    $body[$key] = $value;
                }
            }
        }
        if(!empty($body)){
            $this->setBody($body);
        }
    }

    /**
     * The mail object instance referenced by this mailer.
     */
    function getMail()
    {
        return $this;
    }


    /**
     * Receives a raw email, parses it into an email object, decodes it,
     * instantiates a new mailer, and passes the email object to the mailer
     * object's #receive method. If you want your mailer to be able to
     * process incoming messages, you'll need to implement a #receive
     * method that accepts the email object as a parameter:
     *
     *   class MyMailer extends AkActionMailer{
     *     function receive($Mail)
     *       ...
     *     }
     *   }
     */
    function receive($raw_email)
    {
        /*
        mail = TMail::Mail.parse(raw_email)
        mail.base64_decode
        new.receive(mail)
        */
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
        $Mail->send();
        // new.deliver!(mail)
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
     * rendered and a new PEAR Mail object created.
     */
    function &create($method_name, $parameters)
    {
        $this->_initializeDefaults($method_name);
        if(method_exists($this, $method_name)){
            $this->$method_name($parameters);
        }else{
            trigger_error(Ak::t('Could not find the method %method on the model %model', array('%method'=>$method_name, '%model'=>$this->getModelName())), E_USER_ERROR);
        }

        if(!is_string($this->body)){
            if(empty($this->parts)){
                $templates = array_map('basename', Ak::dir($this->getTemplatePath().DS, array('dirs'=>false)));

                foreach ($templates as $template_name){
                    if(preg_match('/^([^\.]+)\.([^\.]+\.[^\.]+)\.(tpl)$/',$template_name, $match)){
                        if($this->template == $match[1]){
                            $content_type = str_replace('.','/', $match[2]);
                            $this->addPart(array(
                            'content_type' => $content_type,
                            'disposition' => 'inline',
                            'charset' => $this->charset,
                            'body' => $this->renderMessage($template_name, $this->body)));
                        }
                    }
                }
                if(!empty($this->parts)){
                    $this->content_type = 'multipart/alternative';
                    $this->parts = $this->sortParts($this->parts, $this->implicit_parts_order);
                }
            }

            $template_exists = !empty($this->parts);
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
                $this->body = $this->renderMessage($this->template, $this->body);
            }

            if (!empty($this->parts) && is_string($this->body)){
                array_unshift($this->parts, array('charset' => $this->charset, 'body' => $this->body));
                $this->body = null;
            }
        }

        $this->mime_version = (empty($this->mime_version) && !empty($this->parts)) ? '1.0' : $this->mime_version;

        $this->Mail =& $this->createMail();

        return $this->Mail;
    }

    /**
        * Delivers a Pear::Mail object. By default, it delivers the cached mail
        * object (from the create#create! method). If no cached mail object exists, and
        * no alternate has been given as the parameter, this will fail.
        */
    function deliver($Mail = null)
    {
        $this->Mail = empty($this->Mail) ? $this->Mail : $Mail;
        !empty($this->Mail) or trigger_error(Ak::t('No mail object available for delivery!'), E_USER_ERROR);
        if(!empty($this->perform_deliveries)){
            $this->{"performDelivery".ucfirst(strtolower($this->delivery_method))}();
        }
        return $this->Mail;
    }

    /**
    * Set up the default values for the various instance variables of this
    * mailer. Subclasses may override this method to provide different
    * defaults.
    */
    function _initializeDefaults($method_name)
    {
        foreach (array('charset','content_type','implicit_parts_order', 'mime_version') as $attribute) {
            $this->$attribute = empty($this->$attribute) ? $this->{'default_'.$attribute} : $this->$attribute;
        }
        foreach (array('parts','headers','body') as $attribute) {
            $this->$attribute = empty($this->$attribute) ? array() : $this->$attribute;
        }
        $this->template_root = empty($this->template_root) ? AK_APP_DIR.DS.'views' : $this->template_root;
        $this->template = empty($this->template) ? $method_name : $this->template;
        $this->mailer_name = empty($this->mailer_name) ? AkInflector::underscore($this->getModelName()) : $this->mailer_name;
    }

    function renderMessage($method_name, $body)
    {
        return $this->render(array('file' => $method_name, 'body' => $body));
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
        return $this->template_root.DS.$this->mailer_name;
    }

    function &_initializeTemplateClass($assigns)
    {
        require_once(AK_LIB_DIR.DS.'AkActionView.php');
        $TemplateInstance =& new AkActionView($this->getTemplatePath(), $assigns, $this);
        require_once (AK_LIB_DIR.DS.'AkActionView'.DS.'AkPhpTemplateHandler.php');
        $TemplateInstance->_registerTemplateHandler('tpl','AkPhpTemplateHandler');
        return $TemplateInstance;
    }

    function sortParts($parts, $order = array())
    {
        $this->_parts_order = array_map('strtolower', empty($order) ? $this->implicit_parts_order : $order);
        rsort($parts);
        usort($parts, array($this,'_contentTypeComparison'));
        return $parts;
    }

    function _contentTypeComparison($a, $b)
    {
        $a_ct = strtolower($a['content_type']);
        $b_ct = strtolower($b['content_type']);
        $a_in = in_array($a_ct, $this->_parts_order);
        $b_in = in_array($b_ct, $this->_parts_order);
        if($a_in && $b_in){
            $a_pos = array_search($a_ct, $this->_parts_order);
            $b_pos = array_search($b_ct, $this->_parts_order);
            return (($a_pos == $b_pos) ? 0 : (($a_pos < $b_pos) ? -1 : 1));
        }
        return $a_in ? -1 : ($b_in ? 1 : (($a_ct == $b_ct) ? 0 : (($a_ct < $b_ct) ? -1 : 1)));
    }

    function &createMail()
    {
        return $this;
    }

    /**
          function createMail()
          {
              require_once(AK_CONTRIB_DIR.DS.'pear'.DS.'Mail.php');
            require_once(AK_LIB_DIR.DS.'AkActionView'.DS.'helpers'.DS.'text_helper.php');
            require_once(AK_LIB_DIR.DS.'AkActionView'.DS.'helpers'.DS.'asset_tag_helper.php');

        $default_options = array(
        'html' => TextHelper::textilize($this->body),
        'text' => $this->body,
        'attachments' => array()
        );

        $options = array_merge($default_options, $options);

        //AssetTagHelper::_compute_public_path()
        $images = TextHelper::get_image_urls_from_html($options['html']);
        $html_images = array();
        if(!empty($images)){
            require_once(AK_LIB_DIR.DS.'AkImage.php');
            require_once(AK_LIB_DIR.DS.'AkActionView'.DS.'helpers'.DS.'asset_tag_helper.php');
            
            foreach ($images as $image){
                $image = AssetTagHelper::_compute_public_path($image);
                $extenssion = substr($image, strrpos('.'.$image,'.'));

                $image_name = Ak::uuid();
                Ak::file_put_contents(AK_CACHE_DIR.DS.'tmp'.DS.$image_name.$extenssion, file_get_contents($image));
                $NewImage =& new AkImage(AK_CACHE_DIR.DS.'tmp'.DS.$image_name.$extenssion);
                $NewImage->save(AK_CACHE_DIR.DS.'tmp'.DS.$image_name.'.png');
                $html_images[$image] = $image_name.'.png';
                Ak::file_delete(AK_CACHE_DIR.DS.'tmp'.DS.$image_name);
            }
            $options['html'] = str_replace(array_keys($html_images),array_values($html_images), $options['html']);
        }

        require_once(AK_CONTRIB_DIR.DS.'pear'.DS.'Mail'.DS.'mime.php');
        $this->Mime =& new Mail_mime();
        
        $this->Mime->_build_params['text_encoding'] = '8bit';
        $this->Mime->_build_params['html_charset'] = $this->Mime->_build_params['text_charset'] = $this->Mime->_build_params['head_charset'] = Ak::locale('charset');
        
        $this->Mime->setTxtBody($options['text']);
        $this->Mime->setHtmlBody($options['html']);
        foreach ($html_images as $html_image){
            $this->Mime->addHTMLImage(AK_CACHE_DIR.DS.'tmp'.DS.$html_image, 'image/png');
        }
        foreach ((array)$options['attachments'] as $attachment){
            $this->Mime->addAttachment($attachment);
        }
              
              
              
              
            if(!$this->_isAscii($this->subject)){
                $this->subject = '=?UTF-8?Q?'.$this->_convertQuotedPrintableTo8Bit($this->subject, false).'?=';
            }

            $this->recipients = $this->_encodeAddress($this->recipients, '' , AK_OS == 'WINDOWS');
            $this->from = $this->_encodeAddress($this->from, 'From');
            $this->bcc = $this->_encodeAddress($this->bcc, 'Bcc');
            $this->cc = $this->_encodeAddress($this->cc, 'Cc');
            
            $this->mime_version = !empty($this->mime_version) ? 'MIME-Version: '.$this->mime_version.AK_MAIL_HEADER_EOL : '';
            
            
            $Mail->mime_version = mime_version unless mime_version.null?
            $Mail->date = sent_on.to_time rescue sent_on if sent_on
            
            
            
            

    $header .= 'Content-Type: text/plain; charset=UTF-8'.AK_MAIL_HEADER_EOL;
    $header .= 'Content-Transfer-Encoding: quoted-printable'.AK_MAIL_HEADER_EOL;
    $header .= $headers;
    $header  = trim($header);

    $body = $this->_convertQuotedPrintableTo8Bit($body);
            
            headers.each { |k, v| m[k] = v }
    
            real_content_type, ctype_attrs = parse_content_type
    
            if $this->parts.empty?
              $Mail->set_content_type(real_content_type, null, ctype_attrs)
              $Mail->body = Utils.normalize_new_lines(body)
            else
              if String === body
                part = TMail::Mail.new
                part.body = Utils.normalize_new_lines(body)
                part.set_content_type(real_content_type, null, ctype_attrs)
                part.set_content_disposition "inline"
                $Mail->parts << part
              }
    
              $this->parts.each do |p|
                part = (TMail::Mail === p ? p : p.to_mail(self))
                $Mail->parts << part
              }
              
              if real_content_type =~ /multipart/
                ctype_attrs.delete "charset"
                $Mail->set_content_type(real_content_type, null, ctype_attrs)
              }
            }
            


        $success = true;
        if(empty($EmailAccount)){
        }

        if($this->notifyObservers('beforeSend')){
            $this->save();
            $this->recipient->load(true);
            $this->email_account->assign($EmailAccount);
            if(!empty($this->recipients)){
                $this->_loadMailConnector($EmailAccount->getAttributes());
                foreach (array_keys($this->recipients) as $k){
                    $success = $this->_send(trim($this->recipients[$k]->name.' <'.$this->recipients[$k]->email.'>')) ? $success : false;
                }
            }
            $this->notifyObservers('afterSend');
        }
        if($success){
            $this->draft = false;
            $this->sent = true;
            $this->headers = serialize($this->_getHeaders());
            $this->save();
            return $this;
        }
        return $success;
          }
    * /
    function performDeliverySmtp(&$Mail)
    {
        $body = $Mail->get();
        $headers = $this->Mime->headers($headers);
    $mail = &Mail::factory('mail');
    $mail->send($to, $hdrs, $body);
    
        $destinations = mail.destinations
        $Mail->ready_to_send()
    
            Net::SMTP.start(server_settings[:address], server_settings[:port], server_settings[:domain], 
                server_settings[:user_name], server_settings[:password], server_settings[:authentication]) do |smtp|
              smtp.sendmail(mail.encoded, mail.from, destinations)
            }
          }
    
          function performDeliveryPhp(mail)
            IO.popen("/usr/sbin/sendmail -i -t","w+") do |sm|
              sm.print(mail.encoded.gsub(/\r/, ''))
              sm.flush
            }
          }
    
          function performDeliveryTest($Mail)
          {
            $this->deliveries[] = $Mail;
          }
      }
      */



    function send($EmailAccount = null)
    {
        require_once(AK_CONTRIB_DIR.DS.'pear'.DS.'Mail.php');

        $success = true;
        if(empty($EmailAccount)){
        }

        if($this->notifyObservers('beforeSend')){
            $this->save();
            $this->recipient->load(true);
            $this->email_account->assign($EmailAccount);
            if(!empty($this->recipients)){
                $this->_loadMailConnector($EmailAccount->getAttributes());
                foreach (array_keys($this->recipients) as $k){
                    $success = $this->_send(trim($this->recipients[$k]->name.' <'.$this->recipients[$k]->email.'>')) ? $success : false;
                }
            }
            $this->notifyObservers('afterSend');
        }
        if($success){
            $this->draft = false;
            $this->sent = true;
            $this->headers = serialize($this->_getHeaders());
            $this->save();
            return $this;
        }
        return $success;
    }

    function _send($to)
    {
        $headers = $this->_getHeaders($to);
        if(empty($this->Mime)){
            $body = $this->body;
            $headers['Content-Type'] = 'text/plain; charset='.Ak::locale('charset').'; format=flowed';
        }else{
            $body = $this->Mime->get();
            $headers = $this->Mime->headers($headers);
        }

        return $this->Connector->send(
        array(
        'To'=>$to
        ), $headers, $body);
    }

    function _getHeaders($to = null)
    {
        return array(
        'From' => trim($this->email_account->sender_name.' <'.$this->email_account->reply_to.'>'),
        'Return-path' => trim($this->email_account->sender_name.' <'.$this->email_account->reply_to.'>'),
        'Subject' => $this->subject,
        'To' => $to,
        'Message-Id' => '<'.$this->id.'.'.Ak::uuid().substr('bermi@akelos.com', strpos('bermi@akelos.com','@')).'>',
        'Date' => strftime("%a, %d %b %Y %H:%M:%S %z",Ak::getTimestamp()));
    }
    /*
    require_once('Mail.php');      // These two files are part of Pear,
    require_once('Mail/Mime.php'); // and are required for the Mail_Mime class
    $mime = new Mail_Mime();
    $mime->setTxtBody($textMessage);
    $mime->setHtmlBody($htmlMessage);
    $mime->addAttachment($attachment);

    $this->Mime

    $body = $this->Mime->get();
    $hdrs = $this->Mime->headers($headers);
    $mail = &Mail::factory('mail');
    $mail->send($to, $hdrs, $body);
    */

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

    function setMimeContents($options = array())
    {
        require_once(AK_LIB_DIR.DS.'AkActionView'.DS.'helpers'.DS.'text_helper.php');
        require_once(AK_LIB_DIR.DS.'AkActionView'.DS.'helpers'.DS.'asset_tag_helper.php');

        $default_options = array(
        'html' => TextHelper::textilize($this->body),
        'text' => $this->body,
        'attachments' => array()
        );

        $options = array_merge($default_options, $options);

        //AssetTagHelper::_compute_public_path()
        $images = TextHelper::get_image_urls_from_html($options['html']);
        $html_images = array();
        if(!empty($images)){
            require_once(AK_LIB_DIR.DS.'AkImage.php');
            require_once(AK_LIB_DIR.DS.'AkActionView'.DS.'helpers'.DS.'asset_tag_helper.php');

            foreach ($images as $image){
                $image = AssetTagHelper::_compute_public_path($image);
                $extenssion = substr($image, strrpos('.'.$image,'.'));

                $image_name = Ak::uuid();
                Ak::file_put_contents(AK_CACHE_DIR.DS.'tmp'.DS.$image_name.$extenssion, file_get_contents($image));
                $NewImage =& new AkImage(AK_CACHE_DIR.DS.'tmp'.DS.$image_name.$extenssion);
                $NewImage->save(AK_CACHE_DIR.DS.'tmp'.DS.$image_name.'.png');
                $html_images[$image] = $image_name.'.png';
                Ak::file_delete(AK_CACHE_DIR.DS.'tmp'.DS.$image_name);
            }
            $options['html'] = str_replace(array_keys($html_images),array_values($html_images), $options['html']);
        }

        require_once(AK_CONTRIB_DIR.DS.'pear'.DS.'Mail'.DS.'mime.php');
        $this->Mime =& new Mail_mime();

        $this->Mime->_build_params['text_encoding'] = '8bit';
        $this->Mime->_build_params['html_charset'] = $this->Mime->_build_params['text_charset'] = $this->Mime->_build_params['head_charset'] = Ak::locale('charset');

        $this->Mime->setTxtBody($options['text']);
        $this->Mime->setHtmlBody($options['html']);
        foreach ($html_images as $html_image){
            $this->Mime->addHTMLImage(AK_CACHE_DIR.DS.'tmp'.DS.$html_image, 'image/png');
        }
        foreach ((array)$options['attachments'] as $attachment){
            $this->Mime->addAttachment($attachment);
        }
    }

    function _encodeAddress($address_string, $header_name = '', $names = true)
    {
        $headers = '';
        $addresses = Ak::toArray($address_string);
        $addresses = array_map('trim', $addresses);
        foreach ($addresses as $address){
            $address_description = '';
            if(preg_match('#(.*?)<(.*?)>#', $address, $matches)){
                $address_description = trim($matches[1]);
                $address = $matches[2];
            }

            if(empty($address) || !$this->_isAscii($address) || !$this->_isValidAddress($address)){
                continue;
            }
            if($names && !empty($address_description)){
                $address = "<$address>";
                if(!$this->_isAscii($address_description)){
                    $address_description = '=?UTF-8?Q?'.$this->_convertQuotedPrintableTo8Bit($address_description, 0).'?=';
                }
            }
            $headers .= (!empty($headers)?','.AK_MAIL_HEADER_EOL.' ':'').$address_description.$address;
        }

        return empty($headers) ? false : (!empty($header_name) ? $header_name.': '.$headers.AK_MAIL_HEADER_EOL : $headers);
    }

    function _isValidAddress($email)
    {
        return preg_match(AK_EMAIL_REGULAR_EXPRESSION, $email);
    }

    function _convertQuotedPrintableTo8Bit($quoted_string, $max_length = 74, $emulate_imap_8bit = true)
    {
        $lines= preg_split("/(?:\r\n|\r|\n)/", $quoted_string);
        $search_pattern = $emulate_imap_8bit ? '/[^\x20\x21-\x3C\x3E-\x7E]/e' : '/[^\x09\x20\x21-\x3C\x3E-\x7E]/e';
        $match_replacement = 'sprintf( "=%02X", ord ( "$0" ) ) ;';
        foreach ((array)$lines as $k=>$line){
            $length = strlen($line);
            if ($length == 0){
                continue;
            }
            $line = preg_replace($search_pattern, $match_replacement, $line );
            $is_last_char = ord($line[$length-1]);
            if (!($emulate_imap_8bit && ($k==count($lines)-1)) && ($is_last_char==0x09) || ($is_last_char==0x20)) {
                $line[$length-1] = '=';
                $line .= ($is_last_char==0x09) ? '09' : '20';
            }
            if ($emulate_imap_8bit) {
                $line = str_replace(' =0D', '=20=0D', $line);
            }
            if($max_length){
                preg_match_all( '/.{1,'.($max_length - 2).'}([^=]{0,2})?/', $line, $match );
                $line = implode( '=' . AK_MAIL_HEADER_EOL, $match[0] );
            }
            $lines[$k] =& $line;
        }
        return implode(AK_MAIL_HEADER_EOL,$lines);
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