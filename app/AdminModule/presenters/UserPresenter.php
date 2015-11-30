<?php

namespace App\AdminModule\Presenters;

use Nette\Database\Context;
use Nette\Application\UI\Form;
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
    }
    
    public function actionEdit($id = 0)
    {
        // načtení záznamu z databáze
        $user = $this->database->table('users')->fetch($id);

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
    
    
    public function actionReset($id)
    {
        
    }
    
    public function createComponentUserEditForm()
    {
        $rights = $this->database->table('rights')->fetchPairs('id','name');
         
        $form = new Form;
        $form->addHidden('id');
        $form->addText('username', 'uživatelské jméno: ')
             ->addRule(Form::FILLED, 'Zadejte prosím jméno.');
        $form->addText('email', 'email: ')
             ->addRule(Form::FILLED, 'Zadejte prosím email.')
             ->addRule(Form::EMAIL, 'Email by měl mít platný formát');
        $form->addPassword('password', 'původní heslo :');
        $form->addPassword('password_new', 'nové heslo :');
        $form->addPassword('password_new2', 'nové heslo potvrzení:')
             ->addConditionOn($form["password"], Form::FILLED)
             ->addRule(Form::EQUAL, "Nová hesla se musí shodovat !", $form["password_new"]);
        
        $form->addSelect('rights_id','level práv: ',$rights);
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
        if(!empty($values->id))
        {           
            try {
                $data = $this->database->table('users')->get($values->id);
                $data->update($values);
            } catch (Exception $exc) {
                echo $exc->getTraceAsString();
            }
        } 
        else 
        {
           $data = $this->database->table('users')->insert($values);
        }
        dump($data);exit;
        
    }
    
}
