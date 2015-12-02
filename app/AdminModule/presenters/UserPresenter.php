<?php

namespace App\AdminModule\Presenters;

use Nette\Database\Context;
use Nette\Application\UI\Form;
use Nette\Mail\Message;
use Nette\Mail\SendmailMailer;
/**
 * Description of UserPresenter
 *
 * @author Marden
 */
class UserPresenter extends \AdminModule\BasePresenter
{
    private $id;
    
    /** @var Nette\Database\Context */
    private $database;
    
    public function __construct(Context $database)
    {
        $this->database = $database;
    }
    
    public function renderDefault()
    {
        $userList = $this->database->table('users');       
        $this->template->users = $userList; 
        $this->template->user_id = $this->user->id;
    }
    
    public function actionEdit($id = 0)
    {
        // načtení záznamu z databáze
        $user = $this->database->table('users')->fetch($id);
        $this->id = $id;
        
        if (!$user) { // kontrola existence záznamu
            throw new BadRequestException;

        }/* elseif ($user->userId != $this->user->id) { // kontrola oprávnění
            throw new ForbiddenRequestException;
        }*/

        if($id <> 0)
        {
            $this['userEditForm']->setDefaults($user); // nastavení výchozích hodnot
        }
    }
    
    public function createComponentUserEditForm()
    {
        $rights = $this->database->table('rights')->fetchPairs('id','name');
        $username_list = $this->database->table('users')->fetchPairs('username');
        
        $form = new Form;
        $form->addHidden('id');
        
        if($this->id ===0){
           $form->addText('username', 'uživatelské jméno: ')
                ->addRule(~Form::EQUAL,"Vyberte prosím ještě nepoužité jméno",$username_list) //kontrola jeste nepouzivaneho username pro noveho uzivatele
                ->addRule(Form::FILLED, 'Zadejte prosím jméno.'); 
        } else{
           $form->addText('username', 'uživatelské jméno: ')
                ->addRule(Form::FILLED, 'Zadejte prosím jméno.');  
        }
        
        $form->addText('email', 'email: ')
             ->addRule(Form::FILLED, 'Zadejte prosím email.')
             ->addRule(Form::EMAIL, 'Email by měl mít platný formát');
        
        if(in_array("admin",$this->user->roles)) { // editace prav jen adminem
            $form->addSelect('rights_id','level práv: ',$rights);            
        }
        
        $form->addSubmit('submit', 'uložit');
        $form->onSuccess[] = array($this, 'userEditFormSucceeded');
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
    
    public function userEditFormSucceeded(Form $form, $values)
    {
        if(!empty($values->id)) //editace stavajicicho uzivatele
        {           
            try {
                $data = $this->database->table('users')->get($values->id);
                $data->update($values);
                $this->flashMessage('Úprava byla uložena.', 'info');
                $this->redirect('User:default');
            } catch (Exception $exc) {
                echo $exc->getTraceAsString();
            }
        } 
        else // vytvoreni noveho uzivatele
        {
            try {
                $data = $this->database->table('users')->insert($values);
                $this->flashMessage('Uživatel byl vytvořen.', 'info');
                
                $template = $this->createTemplate()->setFile(dirname(__FILE__) . '/templates/emails/_password_email.latte');
                $template->id = $values->id;
                $template->token = rand(1000000,999999999);
                $template->username = $values->username;
                
                $mail = new Message;
                $mail->setFrom('info@bazeny.cz')
                    ->addTo($data->email)
                    ->setSubject('Bazenycz - vytvoření účtu')
                    ->setHtmlBody($template);
            
                $mailer = new SendmailMailer;
                $mailer->send($mail);
                
                $this->flashMessage('Email s odkazem na zapsání hesla byl odeslán.', 'info');
                $this->redirect('User:default');
            } catch (Exception $exc) {
                echo $exc->getTraceAsString();
            }
        }
     //   dump($data);exit; 
    }
    
    public function actionResetPassword($id)
    {
        try {
            $data = $this->database->table('users')->get($id);
            $password_base = $id .$data->username;
            $update['password'] = password_hash($password_base,PASSWORD_DEFAULT);
            $update['token'] = rand(1000000,999999999);
            $data->update($update);
            $this->flashMessage('Heslo bylo resetováno.', 'info');
            
            $template = $this->createTemplate()->setFile(dirname(__FILE__) . '/templates/emails/_password_email.latte');
            $template->id = $id;
            $template->token = $update['token'];
            $template->username = $data->username;
            
            $mail = new Message;
            $mail->setFrom('info@bazeny.cz')
                ->addTo($data->email)
                ->setSubject('Bazenycz - reset hesla')
                ->setHtmlBody($template);
            
            $mailer = new SendmailMailer;
            $mailer->send($mail);
            
            $this->flashMessage('Email s odkazem na změnu hesla byl odeslán.', 'info');
            $this->redirect('Sign:in');
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
        }
    }
}
