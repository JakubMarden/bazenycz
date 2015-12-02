<?php
/**
 * Description of SignInPresenter
 *
 * @author Marden
 */

namespace App\AdminModule\Presenters;

use Nette\Database\Context;
use Nette\Application\UI;
use Nette\Application\UI\Form as Form;

class SignPresenter extends \Nette\Application\UI\Presenter
{
        /** @var Nette\Database\Context */
        private $database;

        public function __construct(Context $database)
        {
            $this->database = $database;
        }

    	protected function createComponentSignInForm()
	{
		$form = new Form;
                $form->addText('username', 'Uživatelská přezdívka: ')
                     ->setRequired('Zadejte prosím jméno');
                $form->addPassword('password', 'Heslo: ')
                    ->setRequired('Zadejte prosím heslo');
                $form->addSubmit('login', 'Přihlásit');
                $form->onSuccess[] = array($this,'SignInFormSubmitted'); 
                $form->addProtection('Vypršel časový limit, odešlete formulář znovu');
                return $form;
	}
        
        public function SignInFormSubmitted(Form $form){
            
            try {
                $values = $form->getValues();  
                $credentials = [$values->username, $values->password];
                $this->user->login($credentials);
                
                if ($this->user->isLoggedIn()){
                    $this->flashMessage('Přihlášení bylo úspěšné.', 'info');
                    $this->redirect('Admin:default');                
                } else {
                    $this->flashMessage('Nemáš přístup do administrace.', 'error');
                    $user->logout(true);
                    $this->redirect('Sign:in');                
                }
            }
            catch (NS\AuthenticationException $e) {
                $form->addError('Neplatné uživatelské jméno nebo heslo.','error');
            }
        }
        
        public function actionEditPassword($id)
        {
            $array = explode('-',$id);
            $id = $array[0];
            $token = $array[1];
            $user = $this->database->table('users')->fetch($id);
            $password = $id .$user->username;
            $allow_change = password_verify($password, $user->password); // kontrola, ze uzivatel meni heslo opravdu sobe
            if($allow_change === false || $token != $user->token){
                $this->redirect('Sign:in');
            }
        }
        
        public function createComponentEditPasswordForm()
        {
            $id = $this->getParameter('id');
            $array = explode('-',$id);
            $id = $array[0];
            $form = new Form;
            $form->addHidden('id')
                  ->setDefaultValue($id);
            $form->addPassword('password', 'nové heslo :')
                  ->addRule(Form::FILLED, 'Zadejte prosím heslo.');
            $form->addPassword('password2', 'nové heslo potvrzení:')
                 ->addConditionOn($form["password"], Form::FILLED)
                 ->addRule(Form::EQUAL, "Nová hesla se musí shodovat !", $form["password"]);
            $form->addSubmit('login', 'Změnit');
            $form->onSuccess[] = array($this,'EditPasswordFormSubmitted'); 
            $form->addProtection('Vypršel časový limit, odešlete formulář znovu');
            
             //bootstrap vzhled
            $renderer = $form->getRenderer();
            $renderer->wrappers['controls']['container'] = NULL;
            $renderer->wrappers['pair']['container'] = 'div class=form-group';
            $renderer->wrappers['pair']['.error'] = 'has-error';
            $renderer->wrappers['control']['container'] = '';
            $renderer->wrappers['label']['container'] = 'div class="control-label col-sm-5"';
            $renderer->wrappers['control']['description'] = 'span class=help-block';
            $renderer->wrappers['control']['errorcontainer'] = 'span class=help-block';
            $form->getElementPrototype()->class('form-horizontal col-sm-12');
            
            return $form;          
        }
        
        public function EditPasswordFormSubmitted(Form $form, $values)
        {
            //dump($values);exit;
            $data['id'] = $values->id;
            $data['password'] = password_hash($values->password,PASSWORD_DEFAULT);
            $data['active'] = 1;
            
            try {                
                $update = $this->database->table('users')->get($values->id);
                $update->update($data);
                $this->flashMessage('Heslo bylo uloženo.', 'info');
                $this->redirect('Sign:in');
            } catch (Exception $exc) {
                echo $exc->getTraceAsString();
            }
        }
}
