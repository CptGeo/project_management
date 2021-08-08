<?php 

namespace App\Presenters;

use DateTime;
use Nette;
use Nette\Application\UI\Form;


class ProjectPresenter extends Nette\Application\UI\Presenter{
    private Nette\Database\Explorer $database;

    public function __construct(Nette\Database\Explorer $database) {
        $this->database = $database;
    }

    /**
     * Displays the view for a specific project
     * 
     * @param $projectId - The id of the project to be displayed
     * 
     */
    public function renderShow(int $projectId) : void {
        
        $project = $this->database->table('projects')->get($projectId);

        if(!$project) {
            $this->flashMessage('Project with id : ' . $projectId . ' not found', 'danger');
            $this->redirect("Homepage:default");
        }

        $type_id = $project->type_id;
        //fetch type data
        $type = $this->database->table('types')->get($type_id);

        $this->template->type = $type;
        $this->template->project = $project;
    }

    public function renderCreate(): void {
        // its not necessary to have this method
    }

    /**
     * Create a form component 
     */
    protected function createComponentProjectForm(): Form {
        $form = new Form; 

        //Get all available project types for select field
        $types = $this->database->table('types');
        $types_arr = array();
        foreach($types as $type) {
            $types_arr[$type->id] = $type->title;
        }

        $form->addText('title', "Project Title:")
            ->setRequired()
            ->addRule($form::MAX_LENGTH, 'The Project title need to be less than %d character long', 60);

        $form->addSelect("type_id", "Project Type", $types_arr)
            ->setRequired();

        $form->addText('start_date', "Start Date")
            ->setHtmlType('date')
            ->setRequired();

        $form->addText('end_date', "End Date")
            ->setHtmlType('date')
            ->setRequired();

        $form->addSubmit('send', "Publish Project");

        $form->onSuccess[] = [$this, 'projectFormSucceeded'];

        return $form;
    }


    /**
     * Processes a submitted form
     * 
     * @param $values - array/stdClass  Contains the values of the submitted form
     * 
     */
    public function projectFormSucceeded(array $values): void {
        $projectId = $this->getParameter('projectId');
        
        if($projectId) {
            $project= $this->database->table('projects')->get($projectId);
            $project->update($values);
        }else{
            $project= $this->database->table('projects')->insert($values);
        }

        $this->flashMessage('Project was published successfully!', 'success');
        $this->redirect('show', $project->id);
    }


    /**
     * Redirects to page to edit a project, with defaults prefilled
     * 
     * @param $projectId - The id of the project to be editted
     * 
     */
    public function actionEdit(int $projectId): void {
        
        $project = $this->database->table('projects')->get($projectId);
        $this->template->project = $project;

        if(!$project) {
            $this->flashMessage('Project with id : ' . $projectId . ' not found', 'danger');
            $this->redirect("Homepage:default");
        }

        // print_r($project->toArray());
        $this['projectForm']->setDefaults(array(
            'title' => $project->title,
            'type_id' => $project->type_id,
            'start_date' => $project->start_date->format('Y-m-d') . "T" . $project->start_date->format('H:i:s'),
            'end_date' => $project->end_date->format('Y-m-d') . "T" . $project->end_date->format('H:i:s')
        ));
    }

    
    /**
     * Deletes a project
     * 
     * @param $projectId - The id of the project to be deleted
     * 
     */
    public function actionDelete(int $projectId): void {

        $project = $this->database->table('projects')->get($projectId);
        if(!$project){
            $this->flashMessage('Project with id : ' . $projectId . ' not found', 'danger');
            $this->redirect("Homepage:default");
        }
        $project->delete();
        $this->flashMessage('Project has been deleted successfully', 'success');
        $this->redirect("Homepage:default");
    }
}