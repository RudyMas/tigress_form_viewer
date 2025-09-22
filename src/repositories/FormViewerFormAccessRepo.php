<?php

namespace Repository;

use Tigress\Repository;

/**
 * Class FormViewerSettingsRepo
 */
class FormViewerFormAccessRepo extends Repository
{
    public function __construct()
    {
        $this->dbName = 'default';
        $this->table = 'form_viewer_form_access';
        $this->primaryKey = ['id'];
        $this->model = 'DefaultModel';
        $this->autoload = true;
        parent::__construct();
    }

    /**
     * Load all data with user and form details
     *
     * @return array
     */
    public function loadAllData(): array
    {
        $sql = "SELECT {$this->table}.*, 
                       users.first_name AS user_first_name, 
                       users.last_name AS user_last_name, 
                       forms.name AS form_name
                FROM {$this->table}
                INNER JOIN users ON {$this->table}.user_id = users.id
                INNER JOIN forms ON {$this->table}.form_id = forms.id";
        $this->database->query($sql);
        return $this->database->fetchAll();
    }
}