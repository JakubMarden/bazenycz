<?php
/**
 * Description of SignInPresenter
 *
 * @author Marden
 */

namespace App\AdminModule\Presenters;

use Nette\Application\UI;
use Nette\Application\UI\Form as Form;

class SignPresenter extends \Nette\Application\UI\Presenter
{
  
    	protected function createComponentSignInForm()
	{
		$form = new Form;
                $form->addText('username', 'Uživatelská přezdívka:')
                     ->setRequired('Zadejte prosím jméno');
                $form->addPassword('password', 'Heslo')
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
}
