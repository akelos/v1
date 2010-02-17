<?php 
class AkCookieStore {
    /**
    * Cookies can typically store 4096 bytes.
    */
    const MAX = 4096;
    const SECRET_MIN_LENGTH = 30; # characters
    private $default_options = array(
    'key'          => AK_SESSION_NAME,
    'secret'       => '',
    'domain'       => AK_HOST,
    'path'         => '/',
    'expire_after' => AK_SESSION_EXPIRE,
    'secure'       => false,
    'httponly'     => true,
    );

    public $options = array();

    public function __destruct(){
        # Ak::getLogger('sessions')->info(__METHOD__);
        @session_write_close();
    }

    public function init($options){
        # Ak::getLogger('sessions')->info(__METHOD__);
        if($settings=Ak::getSettings('session',false)) {
            $this->options = array_merge($this->default_options, !empty($settings['options'])?$settings['options']:array());
        } else {
            $this->options = $this->default_options;
        }
        $this->options = array_merge($this->options, $options);
        $this->options['expire_after'] = time()+$this->options['expire_after'];
        $this->ensureSessionKey();
        $this->ensureSecretSecure();
        ini_set('session.use_cookies','0');
        session_set_save_handler(
        array($this, 'open'),
        array($this, 'close'),
        array($this, 'read'),
        array($this, 'write'),
        array($this, 'destroy'),
        array($this, 'gc')
        );
        session_start();
        return true;
    }

    private function ensureSessionKey(){
        if(empty($this->options['key'])){
            throw new Exception(
            Ak::t('A key is required to write a cookie containing the session data. Use ' .
            'AkConfig::setOption(\'action_controller.session\', '.
            'array("key" => "_myapp_session", "secret" => "some secret phrase")); in config/environment.php'));
        }
    }

    /**
     * To prevent users from using something insecure like "Password" we make sure that the
     * secret they've provided is at least 30 characters in length.
     */
    private function ensureSecretSecure(){
        if(empty($this->options['secret'])){
            throw new Exception(
            Ak::t('A secret is required to generate an integrity hash for cookie session data. Use '.
            'AkConfig::setOption(\'action_controller.session\', '.
            'array("key" => "_myapp_session", "secret" => "some secret '.
            'phrase of at least %length characters")); in config/environment.php', array('%length' => self::SECRET_MIN_LENGTH)));
        }
        if(strlen($this->options['secret']) < self::SECRET_MIN_LENGTH){
            throw new Exception(
            Ak::t('Secret should be something secure, '.
            'like "%rand". The value you provided "%secret", '.
            'is shorter than the minimum length of %length characters', array('%length' => self::SECRET_MIN_LENGTH, '%rand' => Ak::uuid())));
        }
    }

    public function open($save_path, $session_name) {
        # Ak::getLogger('sessions')->info(__METHOD__);
        $this->session_name = $session_name;
        return true;
    }

    public function close() {
        # Ak::getLogger('sessions')->info(__METHOD__);
        session_write_close();
        return true;
    }

    public function read() {
        $data = empty($_COOKIE[$this->session_name]) ? '' : $this->_decodeData($_COOKIE[$this->session_name]);
        # Ak::getLogger('sessions')->info(__METHOD__.' '.$data);
        return $data;
    }

    public function write($irrelevant_but_needed_session_id, $data) {
        # Ak::getLogger('sessions')->info(__METHOD__.' '.$data.' '.AkNumberHelper::human_size(strlen($data)));
        $data = $this->_encodeData($data);
        setcookie($this->session_name, $data, 0, '/', $this->options['domain']);
        return true;
    }
    protected function _remove() {
        # Ak::getLogger('sessions')->info(__METHOD__.' '.$data.' '.AkNumberHelper::human_size(strlen($data)));
        $data = $this->_encodeData($data);
        setcookie($this->session_name, null, time()-36000, '/', $this->options['domain']);
        return true;
    }
    public function destroy() {
        # Ak::getLogger('sessions')->info(__METHOD__);
        //$this->write($this->session_name, '');
        $this->_remove();
        return true;
    }

    public function gc($lifetime) {
        return true;
    }

    public function _decodeData($encoded_data){
        # Ak::getLogger('sessions')->info(__METHOD__);
        list($checksum, $data) = explode('|',$encoded_data.'|', 2);
        $data = @base64_decode($data);
        if(empty($data)){
            return '';
        }
        if($this->_getChecksumForData($data) != $checksum){
            throw new Exception("Cookie data tamper atempt.  Received: \"$data\"\nVisitor IP:".AK_REMOTE_IP);
        }
        return $data;
    }

    public function _encodeData($data){
        # Ak::getLogger('sessions')->info(__METHOD__);
        $data = $this->_getChecksumForData($data).'|'.base64_encode($data);
        if(strlen($data) > self::MAX){
            throw new Exception('Tried to allocate '.strlen($data).' chars into a session based cookie. The limit is '.self::MAX.' chars. Please try storing smaller sets into cookies or use another session handler.');
        }
        return $data;
    }

    public function _getChecksumForData($data){
        # Ak::getLogger('sessions')->info(__METHOD__);
        return sha1(sha1($this->options['secret'].$this->session_name.$data.$this->options['domain']));
    }
}
?>