<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;


final class HomepagePresenter extends Nette\Application\UI\Presenter
{
    private Nette\Database\Explorer $database;
    
    public function __construct(Nette\Database\Explorer $database) {
        $this->database = $database;
    }

    /**
     * Fetches every project and displays it to the front page
     */
    public function renderDefault() : void {

        //fixMe: Did not find a way to fetch data using relationships
        $projects = $this->database->query("
            SELECT p.*, t.title as type_title FROM projects as p
            INNER JOIN types as t
            WHERE p.type_id = t.id");

        $this->template->projects = $projects;

    }

}
