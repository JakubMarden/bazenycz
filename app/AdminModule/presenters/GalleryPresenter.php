<?php

namespace App\AdminModule\Presenters;

use Nette\Database\Context;
use Nette\Application\UI\Form;
use App\Forms;
/**
 * Description of GalleryPresenter
 *
 * @author Marden
 */

class GalleryPresenter extends \AdminModule\BasePresenter
{
    /** @var Nette\Database\Context */
    private $database;
    
    public function __construct(Context $database)
    {
        $this->database = $database;
        @session_start();
    }
    
    public function renderDefault()
    {
        $galleries = $this->database->table('galleries')->order("date");       
        $this->template->galleries = $galleries; 
    }
    
    public function actionAdd()
    {
        $gallery['date'] = (date('d.m.Y'));
        $gallery['active'] = 1;
        $this['galleryAddForm']->setDefaults($gallery);
    }
    
    public function actionEdit($id)
    {                  
        $gallery = $this->database->table('galleries')->get($id);                    // načtení záznamu z databáze
        if (!$gallery->id) {                                                       // kontrola existence záznamu
            throw new BadRequestException;
        } else{
            $this['galleryEditForm']->setDefaults($gallery);                          // nastavení výchozích hodnot
        }
    }
    
    public function actionDetail($id)
    {    
        $this->template->photos = $this->database->table('photos')->where('galeries_id', $id); 
        $this->template->id = $id;
        $this['galleryDetailForm']->setDefaults(array('galeries_id' => $id));              
    }
    
    
    public function actionDelete($id)
    {    
        try {
            $data = $this->database->table('galleries')->get($id);
            $data->delete();
            $this->flashMessage('Galerie byla smazána.', 'info');
            $this->redirect('Gallery:default');
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
        }
    }
    
    public function actionPhotoDelete($id)
    {    
       if(isset($_POST['delete_checkbox'])){
            
            foreach ($_POST['delete_checkbox'] as $photo) {                           
                try {
                    $data = $this->database->table('photos')->get($photo);
                    if(!empty($data) || ($data !==false)){
                        unlink("photos/$data->file");
                        $gallery_id = $data->galeries_id;
                        $data->delete();
                        $this->flashMessage('Foto bylo smazáno.', 'info'); 
                    }    

                } catch (Exception $exc) {
                    echo $exc->getTraceAsString();
                }
            }
            $this->redirect("Gallery:detail", $id);
        }
        $this->redirect("Gallery:detail", $id);
    }
    
    public function actionChangeActive($id)
    {     
         try {
            $data = $this->database->table('galleries')->get($id);
            
            if($data->active === 1){ 
                $values['active'] = 0;  
            } else { 
                $values['active'] = 1;
            }
            
            $data->update($values);
            $this->flashMessage('Viditelnost byla změněna.', 'info');
            $this->redirect('Gallery:default');
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
        }          
    } 
    
    public function createComponentGalleryAddForm() 
    {
        $form = (new Forms\GalleryEditFormFactory($this->database))->create();
        $form->onSuccess[] = array($this, 'galleryAddFormSucceeded');
        return $form;
    }
    
    public function galleryAddFormSucceeded(Form $form, $values)
    {
        try {
            $data = $this->database->table('galleries')->insert($values);
            $this->flashMessage('Galerie byla vytvořena.', 'info');
            $this->redirect('Gallery:default');
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
        }
    }
  
    public function createComponentGalleryEditForm() 
    {
        $form = (new Forms\GalleryEditFormFactory($this->database))->create();
        $form->onSuccess[] = array($this, 'galleryEditFormSucceeded');
        return $form;
    }
    
    public function galleryEditFormSucceeded(Form $form, $values)
    {
        try {
            $data = $this->database->table('galleries')->get($values->id);
            $data->update($values);
            $this->flashMessage('Úprava byla uložena.', 'info');
            $this->redirect('Gallery:default');
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
        }    
    }
    
    public function createComponentGalleryDetailForm() 
    {
        $form = (new Forms\GalleryDetailFormFactory($this->database))->create();
        $form->onSuccess[] = array($this, 'galleryDetailFormSucceeded');        
        return $form;
    }
    
    public function galleryDetailFormSucceeded(Form $form, $values)
    {
        try {
            $max_size = 1024*1024; // 1Mb
            $extensions = array('jpeg', 'jpg', 'png');
            $dir = "photos/";
            $id = $values->galeries_id;
            
            if ($_SERVER['REQUEST_METHOD'] == 'POST' and isset($_FILES['files']))
            {
                    // loop all files
                    foreach ( $_FILES['files']['name'] as $i => $name )
                    {  
                        $extension = pathinfo($name, PATHINFO_EXTENSION);
                        $name = $this->database->table('photos')->max("id")+1;
                        $name .=".$extension";
                            // if file not uploaded then skip it
                            if ( !is_uploaded_file($_FILES['files']['tmp_name'][$i]) )
                                    continue;

                        // skip large files
                            if ( $_FILES['files']['size'][$i] >= $max_size )
                                    continue;

                            // skip unprotected files
                            if( !in_array($extension, $extensions) )
                                    continue;

                            // now we can move uploaded files
                        if( move_uploaded_file($_FILES["files"]["tmp_name"][$i], $dir . $name) )
                                
                            $photo = array("file"=>$name, "galeries_id" => $id);
                            $this->database->table('photos')->insert($photo);
                            $this->flashMessage("Foto $name bylo uloženo.", 'info');
                            
                    }
            }
            $this->redirect("Gallery:detail", $id);
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
        }
        $this->redirect("Gallery:detail", $id);
    }    
}
