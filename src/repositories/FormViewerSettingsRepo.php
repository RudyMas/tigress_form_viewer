<?php

namespace Repository;

use Tigress\SetupRepository;

/**
 * Class FormViewerSettingsRepo
 */
class FormViewerSettingsRepo extends SetupRepository
{
    public function __construct()
    {
        $this->dbName = 'default';
        $this->table = 'form_viewer_settings';
        $this->primaryKey = ['setting'];
        $this->model = 'DefaultModel';
        $this->autoload = true;
        parent::__construct();
    }
}