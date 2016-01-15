<?php

namespace App\AdminModule\Presenters;

use Nette\Database\Context;
use Nette\Application\UI\Form;
use App\Forms;
use Nette\Mail\Message;
use Nette\Mail\SendmailMailer;

/**
 * Description of UserPresenter
 *
 * @author Marden
 */
class UserPresenter extends \AdminModule\BasePresenter
{  
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
    
    public function actionEdit($id)
    {                  
        $user = $this->database->table('users')->fetch($id);                    // načtení záznamu z databáze
        if (!$user->id) {                                                       // kontrola existence záznamu
            throw new BadRequestException;
        } else{
            $this['userEditForm']->setDefaults($user);                          // nastavení výchozích hodnot
        }  
    } 
    
    public function actionAdd(){}
    
    public function createComponentUserEditForm()
    {
        $id = $this->getParameter('id');
        $form = (new Forms\UserEditFormFactory($this->database))->create($this->user->roles);
        if(!$id){
            $form->onSuccess[] = array($this, 'userAddFormSucceeded');
        } else {
            $form->onSuccess[] = array($this, 'userEditFormSucceeded');
        }
        return $form;
    }
    
    public function userEditFormSucceeded(Form $form, $values)
    {
        $username_list = $this->database->table('users')->where('username', $values->username)->where('NOT id',$values->id)->fetchAll();
        
        if(!empty($username_list)){ 
            $this->flashMessage('Bylo vybráno již použité jméno, zkuste prosím jiné.', 'warning');
            $this->redirect('User:edit');
        } else{
            try {
                $data = $this->database->table('users')->get($values->id);
                $data->update($values);
                $this->flashMessage('Úprava byla uložena.', 'info');
                $this->redirect('User:default');
            } catch (Exception $exc) {
                echo $exc->getTraceAsString();
            }
        }    
    }    
        
    public function userAddFormSucceeded(Form $form, $values)
    {
        $username_list = $this->database->table('users')->where('username', $values->username)->fetchAll();
        if(!empty($username_list)){  
            $this->flashMessage('Bylo vybráno již použité jméno, zkuste prosím jiné.', 'warning');
            $this->redirect('User:add');
        } else {
            try {
                $password_base = $values->id .$values->username;
                $values->password = password_hash($password_base,PASSWORD_DEFAULT);
                $values->token = rand(1000000,999999999);
                $data = $this->database->table('users')->insert($values);
                $this->flashMessage('Uživatel byl vytvořen.', 'info');
                
                $template = $this->createTemplate()->setFile(dirname(__FILE__) . '/templates/emails/_password_email.latte');
                $template->id = $values->id .'-' .$values->token;
                $template->username = $values->username;
                
                $mail = new Message;
                $mail->setFrom('info@bazenycz.cz')
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
            $template->id = $id.'-'.$update['token'];
            $template->username = $data->username;
            
            $mail = new Message;
            $mail->setFrom('info@bazenycz.cz')
                ->addTo($data->email)
                ->setSubject('Bazenycz - reset hesla')
                ->setHtmlBody($template);
            
            $mailer = new SendmailMailer;
            $mailer->send($mail);
            
            $this->flashMessage('Email s odkazem na změnu hesla byl odeslán.', 'info');
            $this->redirect('User:default');
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
        }
    }
}
