<?php
include_once '../lib/countries.php';
include_once '../lib/languages.php';

class Admin_UsersController extends AdminController
{
    var $finder_options = array('User'=>array('include'=>'roles'));

    var $controller_menu_options = array(
    'Accounts'   => array('id' => 'accounts', 'url'=>array('controller'=>'users', 'action'=>'listing')),
    'Roles'   => array('id' => 'roles', 'url'=>array('controller'=>'roles')),
    'Permissions'   => array('id' => 'permissions', 'url'=>array('controller'=>'permissions', 'action'=>'manage')),
    );
    var $controller_selected_tab = 'Accounts';

    function index()
    {
        $this->redirectToAction('listing');
    }

    function add()
    {
        !empty($this->params['id']) ? $this->redirectTo(array('action' => 'add', 'id' => NULL)) : null;
        $this->_loadCurrentUserRoles();
        $this->_addOrEdit();
    }

    function change_password()
    {
        $this->login = $this->User->login;
        if(isset($this->params['login']))
            $this->User->findFirst(array('conditions' => array('login' => $this->params['login'])));

        if(empty($this->params['user'])) {
            $this->User = $this->User->findFirst(array('conditions' => array('login' => $this->User->login)));
            $this->admin = $_SESSION['user']['admin'];
        } else {
            $this->User = $this->User->findFirst(array('conditions' => array('login' => $this->User->login)));
            $this->User->setAttributes($this->params['user']);
            if($this->Request->isPost() && $this->User->save()){
                $this->flash['notice'] = $this->t('User was successfully updated.');
                $this->redirectTo(array('action' => 'show', 'id' => $this->User->getId()));
            }
        }
    }

    function destroy()
    {
        if (empty($this->params['id']) || empty($this->User->id)){
            $this->flash['notice'] = $this->t('Invalid user or not found.');
            $this->redirectTo(array('action' => 'listing'));
        }
        $this->_protectUserFromBeingModified();
        if ($this->Request->isPost()){
            if($this->User->getId() == $this->CurrentUser->getId()){
                $this->flash['error'] = $this->t('You can\'t delete your own account.');
            }else{
                if($this->User->destroy()){
                    $this->flash['success'] = $this->t('User was successfully deleted.');
                }else{
                    $this->flash['error'] = $this->t('There was a problem while deleting the user.');
                }
            }
            $this->redirectTo(array('action' => 'listing'));
        }
    }

    function edit()
    {
        if (empty($this->params['id']) || empty($this->User->id)){
            $this->flash['error'] = $this->t('Invalid user or not found.');
            $this->redirectTo(array('action' => 'listing'));
        }
        $this->User->role->load();
        $this->_loadCurrentUserRoles();
        if(empty($this->params['user']['password'])){
            unset($this->params['user']['password']);
        }
        $this->_addOrEdit();
    }

    function forgot_password()
    {
        $this->User = $this->User->findFirst(array('conditions' => array('login' => $this->CurrentUser->login)));
        if(isset($this->params['user']['security_answer_1']))
        {
            if($this->_authenticate_questions())
            {
                $this->logged_in_as = $this->t('Logged in as ').$this->User->login;
                $this->User = $this->params['user'];
                $this->renderAction('home');
            } else {
                unset($this->params['user']['security_answer_1']);
                $this->renderAction('forgot_password');
            }
        } else {
            $this->login = $this->CurrentUser->login;
            $this->name_last  = $this->User->name_last;
            $this->name_first = $this->User->name_first;
            $this->question_1 = $this->User->security_question_1;
            $this->answer_1   = $this->User->security_answer_1;
            $this->question_2 = $this->User->security_question_2;
            $this->answer_2   = $this->User->security_answer_2;
            $this->question_3 = $this->User->security_question_3;
            $this->answer_3   = $this->User->security_answer_3;
        }
    }

    function home()
    {
        $this->User = $this->CurrentUser;
        $this->admin = ($_SESSION['user']['admin'] == 1) ? 'yes': '';
        $this->login = $this->CurrentUser->login;
    }

    function listing()
    {
        $this->User_pages = $this->pagination_helper->getPaginator($this->User, array('items_per_page' => 50));
        $finder_options = $this->pagination_helper->getFindOptions($this->User);
        empty($finder_options['order']) ? $finder_options['order'] = 'created_at DESC' : null;

        if (!$this->Users =& $this->User->find('all', $finder_options)){
            $this->flash_options = array('seconds_to_close' => 10);
            $this->flash['notice'] = $this->t('It seems like you don\'t have Users on your site. Please fill in the form below in order to create your first user.');
            $this->redirectTo(array('action' => 'add'));
        }
    }

    function login()
    {
        $this->CurrentUser->login = '';
        $this->login = '';
        $this->password = '';
        $this->logged_in_as = '';
    }

    function verify_login()
    {
        if(isset($this->params['user']['login'])) {
            if(empty($this->params['user']['password'])) {
                $this->CurrentUser = new User;
                $this->CurrentUser->login = $this->params['user']['login'];
                $this->renderAction('forgot_password');
                $this->redirectTo(array('action' => 'forgot_password'));
            } else {
                if($this->_authenticate_pwd()) {
                    $this->CurrentUser = new User;
                    $this->CurrentUser->login = $this->params['user']['login'];
                    $_SESSION['__CurrentUser'] = serialize($this->CurrentUser);
                    $this->logged_in_as = $this->t('Logged in as ').$this->CurrentUser->login;
                    $this->User = $this->CurrentUser;
                    $this->redirectTo(array('action' => 'home'));
                } else {
                    $this->redirectTo(array('action' => 'login'));
                }
            }
        } else{
            $this->flash['error'] = $this->t('A user code is required.');
            $this->redirectTo(array('action' => 'login'));
        }
    }

    function show()
    {
        if (!$this->User){
            $this->flash['error'] = $this->t('User not found.');
            $this->redirectTo(array('action' => 'listing'));
        }
        $countries = new Countries();
        $this->country_name = $countries->getDesc($this->User->country_code);
        $languages = new Languages();
        $languages = array_flip($languages->get());
        $this->lang_name = $languages[$this->User->lang];
    }

    function _addOrEdit()
    {
        $languages = new Languages();
        $this->languages = $languages->get();
        $this->priority_countries = $GLOBALS['priority_countries'];
        $this->_protectUserFromBeingModified();
        if ($this->Request->isPost() && !empty($this->params['user'])){
            if ($this->params['user']['app_owner']) {
                $Role =& new Role();
                $this->User =& new User();
                $this->User->setAttributes($this->params['user']);
                $this->User->role->add($Role->findFirstBy('name', 'Application owner'));
                if($this->User->save()){
                    # log him in
                    $this->CurrentUser = new User;
                    $this->CurrentUser->login = $this->params['user']['login'];
                    $_SESSION['__CurrentUser'] = serialize($this->CurrentUser);
                    $this->User = $this->CurrentUser;
                    $this->redirectTo(array('controller' => 'dashboard', 'action' => 'index', 'id' => $this->User->getId());
                }
            }else{
                $this->User->setAttributes($this->params['user']);
                empty($this->params['roles']) ? $this->User->addError('Role', Ak::t('Please select at least one role for this user.')) : null;
                if($this->User->save()){
                    if(User::can('Set roles', 'Admin::Users')){
                        $posted_roles = array_diff($this->params['roles'], array(0));
                        if(!empty($posted_roles)){
                            $role_ids = array_intersect(array_keys($posted_roles),
                            array_keys($this->User->collect($this->Roles,'id','id')));
                            $User =& $this->User->find($this->User->id, array('include'=>'roles'));
                            $User->role->setByIds($role_ids);
                        }
                    }
                    $this->flash_options = array('seconds_to_close' => 10);
                    $this->flash['success'] = $this->t('User was successfully '.($this->getActionName()=='add'?'created':'updated'));
                    $this->redirectTo(empty($this->params['continue_editing']) ?
                    array('action' => 'show', 'id' => $this->User->getId()) : array('action' => 'edit', 'id' => $this->User->getId()));
                }
            }
        }
    }

    function _authenticate_pwd()
    {
        $user = $this->User->findFirst(array('conditions' =>
            array('login' => $this->params['user']['login'])));
        if($user) {
            if(sha1($this->params['user']['password']) == $this->User->password) {
                return true;
            } else {
                $this->flash['error'] = $this->t('Invalid password for "').$this->params['user']['login'].'".';
                return false;
            }
        } else {
            $this->flash['error'] = $this->t('User code "').$this->params['user']['login'].$this->t('" is not in the database.');
            return false;
        }
    }

    function _authenticate_questions()
    {
        $errors = false;
        $ans = $this->params['user']['security_answer_1'];
        if(empty($ans))
        {
            $this->flash['error'] = $this->t('First security question must be answered.');
            $errors = true;
        }else{
            if(sha1($ans) != $this->User->security_answer_1)
            {
                $this->flash['error'] = $this->t('First security question was answered incorrectly.');
                $errors = true;
            }
        }

        $ans = $this->params['user']['security_answer_2'];
        if(empty($ans))
        {
            $this->flash['error'] = $this->t('Second security question must be answered.');
            $errors = true;
        }else{
            if(sha1($ans) != $this->User->security_answer_2)
            {
                $this->flash['error'] = $this->t('Second security question was answered incorrectly.');
                $errors = true;
            }
        }

        $ans = $this->params['user']['security_answer_3'];
        if(empty($ans))
        {
            $this->flash['error'] = $this->t('Third security question must be answered.');
            $errors = true;
        }else{
            if(sha1($ans) != $this->User->security_answer_3)
            {
                $this->flash['error'] = $this->t('Third security question was answered incorrectly.');
                $errors = true;
            }
        }
        return !$errors;
    }

    function _protectUserFromBeingModified()
    {
        if(!isset($this->CurrentUser)) {
            return;
        }
        $self_editing = $this->User->getId() == $this->CurrentUser->getId();
        if($this->User->isNewRecord()){
            return ;
        }elseif(!User::can('Set roles', 'Admin::Users') && $this->User->hasRootPrivileges() && !$self_editing){
            $this->flash['error'] = $this->t('You don\'t have the privileges to modify selected user.');
            $this->redirectToAction('listing');
        }elseif (!$self_editing && !User::can('Edit other users', 'Admin::Users')){
            $this->flash['error'] = $this->t('You can\'t modify other users\' accounts.');
            $this->redirectToAction('listing');
        }
    }
}

?>