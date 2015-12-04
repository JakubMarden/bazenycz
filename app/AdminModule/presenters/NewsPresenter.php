<?php

namespace App\AdminModule\Presenters;

use Nette\Database\Context;
use Nette\Application\UI\Form;
use App\Forms;
/**
 * Description of NewsPresenter
 *
 * @author Marden
 */

class NewsPresenter extends \AdminModule\BasePresenter
{
    /** @var Nette\Database\Context */
    private $database;
    
    public function __construct(Context $database)
    {
        $this->database = $database;
    }
    
    public function renderDefault()
    {
        $news = $this->database->table('news');       
        $this->template->news = $news; 
    }
    
    public function actionAdd()
    {
        $news['date'] = (date('d.m.Y'));
        $news['active'] = 1;
        $this['newsAddForm']->setDefaults($news);
    }
    
    public function actionEdit($id)
    {                  
        $news = $this->database->table('news')->fetch($id);                    // načtení záznamu z databáze
        if (!$news->id) {                                                       // kontrola existence záznamu
            throw new BadRequestException;
        } else{
            $this['newsEditForm']->setDefaults($news);                          // nastavení výchozích hodnot
        }
    } 
    
    public function actionDelete($id)
    {    
        try {
            $data = $this->database->table('news')->get($id);
            $data->delete();
            $this->flashMessage('Novinka byla smazána.', 'info');
            $this->redirect('News:default');
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
        }
    } 
    
    public function actionChangeActive($id)
    {     
         try {
            $data = $this->database->table('news')->get($id);
            $data->active === 1 ? $values['active'] = 0 : $values['active'] = 1;
            $data->update($values);
            $this->flashMessage('Viditelnost byla změněna.', 'info');
            $this->redirect('News:default');
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
        }          
    } 
    
    public function createComponentNewsAddForm() 
    {
        $form = (new Forms\NewsEditFormFactory($this->database))->create();
        $form->onSuccess[] = array($this, 'newsAddFormSucceeded');
        return $form;
    }
    
    public function newsAddFormSucceeded(Form $form, $values)
    {
        try {
            $data = $this->database->table('news')->insert($values);
            $this->flashMessage('Novinka byla vytvořena.', 'info');
            $this->redirect('News:default');
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
        }
    }
    
    public function createComponentNewsEditForm() 
    {
        $form = (new Forms\NewsEditFormFactory($this->database))->create();
        $form->onSuccess[] = array($this, 'newsEditFormSucceeded');
        return $form;
    }
    
    public function newsEditFormSucceeded(Form $form, $values)
    {
        try {
            $data = $this->database->table('news')->get($values->id);
            $data->update($values);
            $this->flashMessage('Úprava byla uložena.', 'info');
            $this->redirect('News:default');
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
        }    
    }
}
