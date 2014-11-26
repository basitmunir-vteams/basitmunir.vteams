<?php
/*
    Author: Muhammad Basit Munir
    Date Created: 11 Feb 2014
    Description: controller class to perform functions related to projects. perform CRUD operatiosn related to project and its sub sections like tasks requirements and other sections.

*/
class ControllerCatalogProjects extends Controller {

    private $error = array();

    /*
    *
    *   Funciton will display projects list projects posted so far in system will be visible at this page.
    */
    public function index() {

        $this->language->load('catalog/projects');
		
        $this->document->setTitle($this->language->get('heading_title'));
        
        $this->load->model('catalog/projects');

        // preparing breadcrumbs array

        $this->data['breadcrumbs'] = array();
        
        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => false
        );
        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('catalog/projects', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => ' :: '
        );

        //if there is any new display message in sessions will passed to view from here.
        if (isset($this->session->data['success'])) {
            $this->data['success'] = $this->session->data['success'];

            unset($this->session->data['success']);
        }

        if (isset($this->session->data['warning'])) {
            $this->data['error'] = $this->session->data['warning'];

            unset($this->session->data['warning']);
        }

        $url = '';

        if (isset($this->request->get['page'])) {
            $page = $this->request->get['page'];
        } else {
            $page = 1;
        }

        if (isset($this->request->post['project_name'])) {
            $searchData = !empty($this->request->post['project_name']) ? $this->request->post['project_name'] : '';
            $url .='&project_name=' . $this->request->post['project_name'];
            $page = 1;
        } else if (isset($this->request->get['project_name'])) {
            $searchData = !empty($this->request->get['project_name']) ? $this->request->get['project_name'] : '';
            $url .='&project_name=' . $this->request->get['project_name'];
        } else {
            $searchData = '';
        }

        $data = array(
            'page' => $page,
            'limit' => $this->config->get('config_admin_limit'),
            'start' => $this->config->get('config_admin_limit') * ($page - 1),
        );
        // getting record list to display
        $total = $this->model_catalog_projects->countProjects($searchData);
        $this->data['projects'] = $this->model_catalog_projects->getAllProjects($data, $searchData);

        // preparing pagination.
        $pagination = new Pagination();
        $pagination->total = $total;
        $pagination->page = $page;
        $pagination->limit = $this->config->get('config_admin_limit');
        $pagination->text = $this->language->get('text_pagination');
        $pagination->url = $this->url->link('catalog/projects', 'token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');

        $this->data['pagination'] = $pagination->render();
        $this->data['heading_title'] = $this->language->get('heading_title');
        $this->data['customer_link'] = $this->url->link('catalog/projects/customers', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['delete_confirm'] = $this->language->get('delete_confirm');

        $this->template = 'catalog/project_list.tpl';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render());
    }

    /*
      end function index.
     */

    /*
    *
    *   Function used to add ne project in the sytem 
    */

    public function addnew($id = NULL) {
        // loading libraries language and models.
        $this->load->language('catalog/projects');
		$this->load->language('catalog/fund');
		
        $this->load->model('catalog/projects');
        
        //setting up title.
        $this->document->setTitle($this->language->get('add_project_heading_title'));

        //if any project need to be edit following code block perform set document title set project id. time and project detail to fill the form fields..
        if ($id != NULL) {
            $this->document->setTitle($this->language->get('edit_project_heading_title'));
            $this->data['p_id'] = $id;
            $this->data['heading_title'] = $this->language->get('Edit_heading_title');
            $project_detail = $this->model_catalog_projects->getProject($id);
            $project_detail['min_amount_for_gift'] = isset($project_detail['min_amount_for_gift']) && !empty($project_detail['min_amount_for_gift'] ) ? json_decode($project_detail['min_amount_for_gift'], true) : array() ;
            $project_detail['gift_detail'] = isset($project_detail['gift_detail']) && !empty($project_detail['gift_detail'] ) ? json_decode($project_detail['gift_detail'], true) : array() ;
            
            $this->data['project_detail'] = $project_detail;
        }

        // when form have been submit this block will be activated.
        if ($this->request->post) {
            if (isset($this->session->data['warning'])) {
                $this->data['error'] = $this->session->data['warning'];

                unset($this->session->data['warning']);
            } else {
                $this->data['error'] = '';
            }

            // check to validate the submitted data.
            if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
                if (!isset($this->request->post['prj_id'])) {
                    $this->model_catalog_projects->addProject($this->request->post);
                    $this->session->data['success'] = $this->language->get('text_success');
                } else {
                    $this->model_catalog_projects->updateProject($this->request->post);
                    $this->session->data['success'] = $this->language->get('text_update');
                }


                $this->redirect($this->url->link('catalog/projects', 'token=' . $this->session->data['token'], 'SSL'));
            } else { // if form is not valid then execute following code block
                $this->data['error'] = $this->error;
                $this->data['project_detail'] = $this->request->post;
                if (isset($this->request->post['prj_id'])) {
                    $this->data['p_id'] = $this->request->post['prj_id'];
                }
            }
        }

        // managing breadrumbs
        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('catalog/projects', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => ' :: '
        );
        if ($id == NULL) {
            $this->data['breadcrumbs'][] = array(
                'text' => $this->language->get('project_heading_title'),
                'href' => $this->url->link('catalog/projects/addnew', 'token=' . $this->session->data['token'], 'SSL'),
                'separator' => ' :: '
            );
        } else {
            $this->data['breadcrumbs'][] = array(
                'text' => $this->language->get('edit_project_heading_title'),
                'href' => $this->url->link('catalog/projects/edit', 'token=' . $this->session->data['token'] . '&project_id=' . $id, 'SSL'),
                'separator' => ' :: '
            );
        }

        if (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1'))) {
            $this->data['base'] = $this->config->get('config_ssl');
        } else {
            $this->data['base'] = $this->config->get('config_url');
        }

        $this->data['customer_link'] = urldecode($this->url->link('catalog/projects/customers&token=' . $this->session->data['token']));
        $this->data['type_list'] = $this->model_catalog_projects->getProjectTypeList();
        $this->template = 'catalog/project_form.tpl';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render());
    }

    /*
      End Add new Proejct Function;
     */

    /**
    *
    *   Function used to delete the project. if it is not necessary.
    */
    public function delete() {
        $this->load->language('catalog/projects');
        $this->load->model('catalog/projects');
        $this->model_catalog_projects->deleteProjects($this->request->get['project_id']);
        $this->session->data['success'] = $this->language->get('text_success_dlt'); //setting up message
        // redirect to list page
        $this->redirect($this->url->link('catalog/projects', 'token=' . $this->session->data['token'], 'SSL'));

    }
     /*
        end Function delete
     */

    /*
    *   function used to edit project it reuse add new funciton to display data for edditing.
    */
    public function edit() {

        $this->addnew($this->request->get['project_id']);
    }

    /* 
    *       Project Section end 
    */

    //------------------------------------------------------------
    
    /*
    * MileStones
    */
    //funciton used to display milestones for selected project. it display list of milestone and a form to add new milestone.
    public function milestone($id = NULL) {

        $this->load->language('catalog/projects');
        $this->load->model('catalog/projects');

        if (isset($this->session->data['success'])) {
            $this->data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        }

        // setting up title
        $this->document->setTitle($this->language->get('add_project_heading_title'));

        //if any project need to be edit following code block perform set document title set project id. time and project detail to fill the form fields..
        if ($id != NULL) {

            $this->document->setTitle($this->language->get('edit_milestone_heading'));
            $this->data['ms_id'] = $id;
            $this->data['heading_title'] = $this->language->get('edit_milestone_heading');
            $this->data['mile_stone'] = $this->model_catalog_projects->getMileStone($id);
        }

        // when form have been submit this block will be activated.
        if (isset($this->request->post['ms_name'])) {

            if (isset($this->session->data['warning'])) {
                $this->data['error'] = $this->session->data['warning'];
                unset($this->session->data['warning']);
            } else {
                $this->data['error'] = '';
            }

            // check to validate the submitted data.
            if ($this->validateMileStone()) {
                if (empty($this->request->post['ms_id'])) {

                    $this->model_catalog_projects->saveMileStone($this->request->post);
                    $this->session->data['success'] = $this->language->get('text_success');
                } else {

                    $this->model_catalog_projects->updateMileStone($this->request->post);
                    $this->session->data['success'] = $this->language->get('text_update');
                }


                $this->redirect($this->url->link('catalog/projects/milestone', 'token=' . $this->session->data['token'] . '&project_id=' . $this->request->post['project_id'], 'SSL'));

            } else { // if form is not valid then execute following code block

                if (isset($this->error['warning'])) {
                    $this->data['error_warning'] = $this->error['warning'];
                }

                $this->data['error'] = $this->error;
                $this->data['mile_stone'] = $this->request->post;
            }
        }

        // managing breadrumbs
        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('catalog/projects', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => ' :: '
        );

        if ($id == NULL) {
            $this->data['breadcrumbs'][] = array(
                'text' => $this->language->get('milestone_heading'),
                'href' => $this->url->link('catalog/projects/milestone', 'token=' . $this->session->data['token'] . '&project_id=' . $this->request->get['project_id'], 'SSL'),
                'separator' => ' :: '
            );
        } else {
            $this->data['breadcrumbs'][] = array(
                'text' => $this->language->get('edit_milestone_heading_title'),
                'href' => $this->url->link('catalog/projects/editmilestone', 'token=' . $this->session->data['token'] . '&project_id=' . $this->request->get['project_id'] . '&msid=' . $id, 'SSL'),
                'separator' => ' :: '
            );
        }

        if (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1'))) {
            $this->data['base'] = $this->config->get('config_ssl');
        } else {
            $this->data['base'] = $this->config->get('config_url');
        }



        //setting up url and search criteria for record

        $url = '';
        if (isset($this->request->get['page'])) {
            $page = $this->request->get['page'];
            $url .= '&page=' . $this->request->get['page'];
        } else {
            $page = 1;
        }

        if (isset($this->request->post['ms_name_search'])) {
            $searchData = !empty($this->request->post['ms_name_search']) ? $this->request->post['ms_name_search'] : '';
            $url .='&ms_name=' . $this->request->post['ms_name_search'];
            $page = 1;
        } else if (isset($this->request->get['ms_name'])) {
            $searchData = !empty($this->request->get['ms_name']) ? $this->request->get['ms_name'] : '';
            $url .='&ms_name=' . $this->request->get['ms_name'];
        } else {
            $searchData = '';
        }

        $data = array(
            'page' => $page,
            'limit' => $this->config->get('config_admin_limit'),
            'start' => $this->config->get('config_admin_limit') * ($page - 1),
        );

        $total = $this->model_catalog_projects->countMileStones($this->request->get['project_id'], $searchData);
        // preparing paginations
        $pagination = new Pagination();
        $pagination->total = $total;
        $pagination->page = $page;
        $pagination->limit = $this->config->get('config_admin_limit');
        $pagination->text = $this->language->get('text_pagination');
        $pagination->url = $this->url->link('catalog/projects/milestone', '&project_id=' . $this->request->get['project_id'] . '&token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');

        $status_list = $this->language->get('status_list');

        $this->data['pagination'] = $pagination->render();

        $this->data['milestones'] = $this->model_catalog_projects->getMileStoneList($this->request->get['project_id'], $searchData, $data);



        //preparing variables to view at front end.
        $project = $this->model_catalog_projects->getProject($this->request->get['project_id']);
        $this->data['active_project'] = $project;
        $this->data['project_title'] = $project['name'];
        $this->data['heading_title'] = $this->language->get('heading_title');
        $this->data['no_record_found'] = $this->language->get('no_record_found');

        if ($id == NULL) {
            $this->data['add_milestone_heading'] = $this->language->get('add_milestone_heading');
        } else {
            $this->data['add_milestone_heading'] = $this->language->get('edit_milestone_heading');
        }

        $this->data['delete_confirm'] = $this->language->get('delete_confirm');
        $this->data['customer_link'] = urldecode($this->url->link('catalog/projects/customers&token=' . $this->session->data['token']));

        $this->template = 'catalog/miles_stones.tpl';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render());
    }

    /*
    *   End Funciton milestone.
    */

    /*
    * function used to edit selected milestone. it reuse milestone function to display record for editting.
    */
    public function edit_mile_stone() {
        $this->milestone($this->request->get['msid']);
    }

    /*
    *   Funciton used to delete milestone it delete milestrone and redirect to milestone page with a message
    */
    public function delete_mile_stone() {
        $this->load->language('catalog/projects');
        $this->load->model('catalog/projects');
        $this->model_catalog_projects->deleteMileStone($this->request->get['project_id'], $this->request->get['msid']);
        $this->session->data['success'] = $this->language->get('text_success_dlt');
        $this->redirect($this->url->link('catalog/projects/milestone', '&project_id=' . $this->request->get['project_id'] . '&token=' . $this->session->data['token'], 'SSL'));
    }

    /* End Mile Stone Section */

    //--------------------------------------------------------------------------------------------------

    /*
    * Function used to display tasks under a milestone.
    */

    public function tasks($id = NULL) {

        $this->load->language('catalog/projects');
        $this->load->model('catalog/projects');

        if (isset($this->session->data['success'])) {
            $this->data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        }

        $this->document->setTitle($this->language->get('add_task_heading_title'));

        //if any project need to be edit following code block perform set document title set project id. time and project detail to fill the form fields..
        if ($id != NULL) {

            $this->document->setTitle($this->language->get('edit_task_heading'));
            $this->data['ms_id'] = $id;
            $this->data['heading_title'] = $this->language->get('edit_task_heading');
            $this->data['tasks'] = $this->model_catalog_projects->getTask($id);

        }

        // when form have been submit this block will be activated.
        if (isset($this->request->post) && !empty($this->request->post)) {

            if (isset($this->session->data['warning'])) {
                $this->data['error'] = $this->session->data['warning'];
                unset($this->session->data['warning']);
            } else {
                $this->data['error'] = '';
            }

            // check to validate the submitted data.
            if (!isset($this->request->post['task_name_search']) ) { 
                
            
                if ($this->validateTaskForm()) {

                    if (empty($this->request->post['task_id'])) {

                        $this->model_catalog_projects->saveTask($this->request->post);
                        $this->session->data['success'] = $this->language->get('text_success');

                    } else {

                        $this->model_catalog_projects->updateTask($this->request->post);
                        $this->session->data['success'] = $this->language->get('text_update');

                    }

                    //redirection after insertion.
                    $this->redirect($this->url->link('catalog/projects/tasks', 'token=' . $this->session->data['token'] . '&project_id=' . $this->request->post['project_id'] . '&msid=' . $this->request->post['ms_id'] . '&requirement_id=' . $this->request->post['requirement_id'], 'SSL'));

                } else { // if form is not valid then execute following code block

                    if (isset($this->error['warning'])) {

                        $this->data['error_warning'] = $this->error['warning'];

                    }
                    $this->data['error'] = $this->error;
                    $this->data['tasks'] = $this->request->post;
                }
            
            } 
        }

        // managing breadrumbs
        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => false
        );

        /* $this->data['breadcrumbs'][] = array(
          'text'      => $this->language->get('text_module'),
          'href'      => $this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL'),
          'separator' => ' :: '
          ); */

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('catalog/projects', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => ' :: '
        );

        if ($id == NULL) {
            $this->data['breadcrumbs'][] = array(
                'text' => $this->language->get('task_heading'),
                'href' => $this->url->link('catalog/projects/tasks', 'token=' . $this->session->data['token'] . '&project_id=' . $this->request->get['project_id'] . '&msid=' . $this->request->get['msid'], 'SSL'),
                'separator' => ' :: '
            );
        } else {
            $this->data['breadcrumbs'][] = array(
                'text' => $this->language->get('edit_milestone_heading_title'),
                'href' => $this->url->link('catalog/projects/editmilestone', 'token=' . $this->session->data['token'] . '&project_id=' . $this->request->get['project_id'] . '&msid=' . $this->request->get['msid'] . '&task_id=' . $id, 'SSL'),
                'separator' => ' :: '
            );
        }

        if (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1'))) {
            $this->data['base'] = $this->config->get('config_ssl');
        } else {
            $this->data['base'] = $this->config->get('config_url');
        }



        //pagination
        $url = '';
        if (isset($this->request->get['page'])) {
            $page = $this->request->get['page'];
        } else {
            $page = 1;
        }

        if (isset($this->request->post['task_name_search'])) {
            $searchData = !empty($this->request->post['task_name_search']) ? $this->request->post['task_name_search'] : '';
            $url .='&task_name=' . $this->request->post['task_name_search'];
            $page = 1;
        } else if (isset($this->request->get['task_name'])) {
            $searchData = !empty($this->request->get['task_name']) ? $this->request->get['task_name'] : '';
            $url .='&task_name=' . $this->request->get['task_name'];
        } else {
            $searchData = '';
        }

        $data = array(
            'page' => $page,
            'limit' => $this->config->get('config_admin_limit'),
            'start' => $this->config->get('config_admin_limit') * ($page - 1),
        );

        // count total number of record
        $total = $this->model_catalog_projects->countTasks($this->request->get['requirement_id'], $searchData);

        $pagination = new Pagination();
        $pagination->total = $total;
        $pagination->page = $page;
        $pagination->limit = $this->config->get('config_admin_limit');
        $pagination->text = $this->language->get('text_pagination');
        $pagination->url = $this->url->link('catalog/projects/tasks', 'token=' . $this->session->data['token'] . '&project_id=' . $this->request->get['project_id'] . '&msid=' . $this->request->get['msid'] . $url . '&page={page}', 'SSL');

        $status_list = $this->language->get('status_list');
        $this->data['pagination'] = $pagination->render();
        $this->data['milestones'] = $this->model_catalog_projects->getTasksList($this->request->get['requirement_id'], $searchData, $data);


        $project = $this->model_catalog_projects->getProject($this->request->get['project_id']);
        $milestone = $this->model_catalog_projects->getMileStone($this->request->get['msid']);

        $this->data['active_milestone'] = $milestone;
        $this->data['project_title'] = $project['name'];
        $this->data['milestone_title'] = $milestone['ms_name'];
        $this->data['heading_title'] = $this->language->get('heading_title');
        $this->data['approve_confirm'] = $this->language->get('approve_confirm');

        //preparing variables to view at front end.

        if ($id == NULL) {

            $this->data['add_task_heading'] = $this->language->get('add_task_heading');

        } else {

            $this->data['add_task_heading'] = $this->language->get('edit_task_heading');

        }

        $this->data['delete_confirm'] = $this->language->get('delete_confirm');
        $this->data['customer_link'] = urldecode($this->url->link('catalog/projects/customers&token=' . $this->session->data['token']));

        $this->template = 'catalog/tasks.tpl';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render());
    }

    /*
    *   function used to Edit the task.
    */
    public function edit_task() {

        $this->tasks($this->request->get['task_id']);
    }

    /*
    *   Function used to approve the tasks.
    */
    public function approve_task() {

        $this->load->language('catalog/projects');
        $this->load->model('catalog/projects');
        $this->model_catalog_projects->approveTask($this->request->get['task_id'], $this->request->get['approve']);
        $this->session->data['success'] = $this->language->get('text_update');
        $this->redirect($this->url->link('catalog/projects/tasks', '&project_id=' . $this->request->get['project_id'] . '&msid=' . $this->request->get['msid'] . '&requirement_id=' . $this->request->get['requirement_id'] . '&token=' . $this->session->data['token'], 'SSL'));
    }

    /*
    * function used to delete task.
    */
    public function delete_task() {

        $this->load->language('catalog/projects');
        $this->load->model('catalog/projects');
        $this->model_catalog_projects->deleteTask($this->request->get['task_id']);
        $this->session->data['success'] = $this->language->get('text_success_dlt');
        $this->redirect($this->url->link('catalog/projects/tasks', '&project_id=' . $this->request->get['project_id'] . '&msid=' . $this->request->get['msid'] . '&requirement_id=' . $this->request->get['requirement_id'] . '&token=' . $this->session->data['token'], 'SSL'));
    }

    /*  End Tasks Section  */
    // --------------------------------------------------------

    /*
    * function used to get customer list for autocomplete fields.
    */
    public function customers() {

        $this->load->language('catalog/projects');
        $this->load->model('catalog/projects');
        echo json_encode($this->model_catalog_projects->getCustomer($this->request->get['term']));
    }

    /*
    * function used to display backlogs for a project.
    */
    public function backlog() {

        $this->load->language('catalog/projects');
        $this->load->model('catalog/projects');
        $this->document->setTitle($this->language->get('backlog'));

        if (isset($this->session->data['success'])) {
            $this->data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        }
        
        if (isset($this->session->data['success'])) {
            $this->data['warning'] = $this->session->data['warning'];
            unset($this->session->data['warning']);
        }

        // if data is submitted from form this chek will called and insert entry in database.
        if (isset($this->request->post)) {
            // check to validate the submitted data.
            if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateBacklogForm()) {

                if (empty($this->request->post['task_id'])) {

                    $this->model_catalog_projects->saveBackLog($this->request->post);
                    $this->data['success'] = $this->language->get('text_success');
                    $this->request->post = NULL;

                } else {

                    $this->model_catalog_projects->updateBackLog($this->request->post);
                    $this->data['success'] = $this->language->get('text_update');
                    unset($this->request->post);

                }

            } else {

                $this->data['error'] = $this->error;
            }
        }


        $url = '';
        if (isset($this->request->get['page'])) {
            $page = $this->request->get['page'];
            $url .= '&page=' . $this->request->get['page'];
        } else {
            $page = 1;
        }

        $data = array(
            'page' => $page,
            'limit' => $this->config->get('config_admin_limit'),
            'start' => $this->config->get('config_admin_limit') * ($page - 1),
        );

        $this->data['breadcrumbs'] = array();
        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('project_heading'),
            'href' => $this->url->link('catalog/projects', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => ' :: '
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('backlog'),
            'href' => $this->url->link('catalog/projects/backlog', '&project_id=' . $this->request->get['project_id'] . '&token=' . $this->session->data['token'], 'SSL'),
            'separator' => ' :: '
        );

        $total = $this->model_catalog_projects->CountBacklogs($this->request->get['project_id']);
        $pagination = new Pagination();
        $pagination->total = $total;
        $pagination->page = $page;
        $pagination->limit = $this->config->get('config_admin_limit');
        $pagination->text = $this->language->get('text_pagination');
        $pagination->url = $this->url->link('catalog/projects/backlog', '&token=' . $this->session->data['token'] . '&project_id=' . $this->request->get['project_id'] . $url . '&page={page}', 'SSL');

        $pages = $pagination->render();

        //Add new button
        $this->data['addNew']['text'] = $this->language->get('add_new');
        $this->data['addNew']['link'] = $this->url->link('catalog/projects/milestone', '&project_id=' . $this->request->get['project_id'] . '&token=' . $this->session->data['token'], 'SSL');
        $this->data['addNew']['project_text'] = $this->language->get('project_heading');
        $this->data['addNew']['project_link'] = $this->url->link('catalog/projects', '&token=' . $this->session->data['token'], 'SSL');

        $project = $this->model_catalog_projects->getProject($this->request->get['project_id']);

        //preparing variables to view at front end.
        $this->data['project_title'] = $project['name'];
        $this->data['project_name'] = $this->language->get('project_name');
        $this->data['heading_title'] = $this->language->get('heading_title');
        $this->data['sr_no'] = $this->language->get('sr_no');
        $this->data['add_new'] = $this->language->get('add_new');
        $this->data['task_name'] = $this->language->get('task_name');
        $this->data['status'] = $this->language->get('status');
        $this->data['milelstone'] = $this->language->get('milelstone');
        $this->data['backlog_title'] = $this->language->get('backlog');
        $this->data['status_list'] = $this->language->get('status_list');
        $this->data['button_submit'] = $this->language->get('button_submit');
        $this->data['edit'] = $this->language->get('edit_text');
        $this->data['delete'] = $this->language->get('delete_text');
        $this->data['backlog_list'] = $this->language->get('backlog_list');
        $this->data['delete_confirm'] = $this->language->get('delete_confirm');

        $this->data['backlogs'] = $this->model_catalog_projects->GetBackLogs($this->request->get['project_id'], $data);
        $this->data['milestones'] = $this->model_catalog_projects->getMileStoneList($this->request->get['project_id'], '', $data);
        $this->data['pages'] = $pages;

        $this->template = 'catalog/backlog.tpl';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render());
    }

    /* function end backlog */

    /*
    *   function to use edit backlogs
    */
    public function editbacklog() {
        
        $this->load->language('catalog/projects');
        $this->load->model('catalog/projects');
        $row = $this->model_catalog_projects->getBackLog($this->request->get['task_id']);
        echo json_encode($row);
    }

    /*
    *   Function to use delete backlog.
    */
    public function deletebacklog() {

        $this->load->language('catalog/projects');
        $this->load->model('catalog/projects');
        $this->model_catalog_projects->deleteBacklog($this->request->get['task_id']);
        $this->session->data['success'] = $this->language->get('text_success_dlt');
        $this->redirect($this->url->link('catalog/projects/backlog' . '&project_id=' . $this->request->get['project_id'] . '&token=' . $this->session->data['token'], 'SSL'));
    }

    /*
    *   
    */
    public function members() {
        $this->language->load('catalog/projects');
        $this->load->model('catalog/projects');

        echo json_encode($this->model_catalog_projects->getProjectTeamMembers($this->request->get['term'], $this->request->get['project_id']));
    }

    /*  ------------------------------------------------------- */
    /*  ------------------ Validation functions --------------- */
    /*  ------------------------------------------------------- */

    protected function validate() {
        
        if (!$this->user->hasPermission('modify', 'catalog/projects')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if ((utf8_strlen($this->request->post['name']) < 1) || (utf8_strlen($this->request->post['name']) > 200)) {
            $this->error['name'] = $this->language->get('error_project_name');
        }

        if (empty($this->request->post['description'])) {
            $this->error['description'] = $this->language->get('error_description');
        }
        if (empty($this->request->post['lead'])) {
            if (!$this->request->post['lead_name']) {
                $this->error['lead'] = $this->language->get('error_lead_name');
            } else {
                $this->error['lead'] = $this->language->get('error_lead');
            }
        }

        if (empty($this->request->post['due_date']) && $this->request->post['due_date'] < date("Y-m-m", time())) {
            $this->error['due_date'] = $this->language->get('error_due_date');
        }

        if ((int) $this->request->post['budget'] == 0) {
            if (empty($this->request->post['budget'])) {
                $this->error['budget'] = $this->language->get('error_budget');
            } else {
                $this->error['budget'] = $this->language->get('error_int');
            }
        }

        if (!$this->fileUpload()) {
            $this->error['attachment'] = $this->language->get('attachment');
        }
        
        if(isset($this->request->post['min_amount_for_gift'])) {
            foreach($this->request->post['min_amount_for_gift'] as $index=>$value) {
                
                if(empty( $this->request->post['min_amount_for_gift'][$index]) ) { 
                    
                    $this->error['min_amount_for_gift'][$index] = $this->language->get('error_empty_field');
                            
                } else if ( (int) $this->request->post['min_amount_for_gift'][$index] <= 0 ) {
                    
                    $this->error['min_amount_for_gift'][$index] = $this->language->get('error_num_field');       
                            
                }
                
                
            }
        }
        
        if(isset($this->request->post['gift_detail'])){
            foreach($this->request->post['gift_detail'] as $index=>$value) {
                
                if(empty( $this->request->post['gift_detail'][$index]) ) { 
                    
                    $this->error['gift_detail'][$index] = $this->language->get('error_empty_field');
                            
                }
                
                
            }
        }

        if (!$this->error) {
            return true;
        } else {
            return false;
        }
    }

    /*
    * Function to used validate milestone form.
    */
    protected function validateMileStone() {
        $project = $this->model_catalog_projects->getProject($this->request->post['project_id']);
        $ms_budget = $this->model_catalog_projects->getAllMileStoneCost($this->request->post['project_id']);

        if (!$this->user->hasPermission('modify', 'catalog/projects')) {
            $this->error['warning'] = $this->language->get('milestone_error_permission');
        }

        if ((utf8_strlen($this->request->post['ms_name']) < 1) || (utf8_strlen($this->request->post['ms_name']) > 50)) {
            $this->error['ms_name'] = $this->language->get('error_ms_name');
        }

        if (empty($this->request->post['ms_description'])) {
            $this->error['ms_description'] = $this->language->get('error_ms_description');
        }

        if (empty($this->request->post['ms_due_date']) && $this->request->post['ms_due_date'] < date("Y-m-m", time())) {

            $this->error['ms_due_date'] = $this->language->get('error_ms_due_date');
        }

        if (isset($this->request->post['ms_budget']) && (int) $this->request->post['ms_budget'] == 0) {

            if (empty($this->request->post['ms_budget'])) {

                $this->error['ms_budget'] = $this->language->get('error_budget');

            } else {

                $this->error['ms_budget'] = $this->language->get('error_int');
            }
        }

        if (isset($this->request->post['ms_budget']) && ( $project['budget'] <= $ms_budget || $project['budget'] <= $this->request->post['ms_budget'] )) {
            $this->error['ms_budget'] = $this->language->get('error_ms_budget_limit_reached');
        }
       

        if (!$this->error) {
            return true;
        } else {
            return false;
        }
    }

    /*
    *
    * function use validate task form.
    */
    protected function validateTaskForm() {
        $milestone = $this->model_catalog_projects->getMileStone($this->request->post['ms_id']);
        $task_budget = $this->model_catalog_projects->getAllTasksCost($this->request->post['ms_id']);

        if (!$this->user->hasPermission('modify', 'catalog/projects')) {
            $this->error['warning'] = $this->language->get('milestone_error_permission');
        }

        if ((utf8_strlen($this->request->post['task_title']) < 1) || (utf8_strlen($this->request->post['task_title']) > 50)) {
            $this->error['task_title'] = $this->language->get('error_task_name');
        }

        if (empty($this->request->post['task_description'])) {
            $this->error['task_description'] = $this->language->get('error_ms_description');
        }

        if (empty($this->request->post['task_due_date']) && $this->request->post['task_due_date'] < date("Y-m-m", time())) {
            $this->error['task_due_date'] = $this->language->get('error_ms_due_date');
        }
        if ((int) $this->request->post['task_budget'] == 0) {
            // $this->error['task_budget'] = $this->language->get('error_ms_budget');
            if (empty($this->request->post['task_budget'])) {
                $this->error['task_budget'] = $this->language->get('error_budget');
            } else {
                $this->error['task_budget'] = $this->language->get('error_int');
            }
        }

        if (isset($this->request->post['task_budget']) && ( $milestone['ms_budget'] < $task_budget || $milestone['ms_budget'] < $this->request->post['task_budget'] )) {
            $this->error['task_budget'] = $this->language->get('error_task_budget_limit_reached');
        }

        if (empty($this->request->post['task_assigned_to'])) {
            if (!$this->request->post['task_assigned_to']) {
                $this->error['task_assigned_to'] = $this->language->get('error_assigned_to');
            } else {
                $this->error['task_assigned_to'] = $this->language->get('error_assigned_to_name');
            }
        }
        

        if (!$this->error) {
            return true;
        } else {
            return false;
        }
    }

    /*
    * Function to vlaidate backlogform
    */
    protected function validateBacklogForm() {
        if ((utf8_strlen($this->request->post['task_title']) < 1) || (utf8_strlen($this->request->post['task_title']) > 50)) {
            $this->error['task_title'] = $this->language->get('error_task_name');
        }

        if (!$this->error) {
            return true;
        } else {
            return false;
        }
    }

    /*
    *   Function to use Fileupload.
    */
    protected function fileUpload() {

        $allowedExts = array("gif", "jpeg", "jpg", "png", "doc", "docx", "xls", "xlsx");
        $allowed_types = array("image/jpeg", "image/gif", "image/png", "text/plain", "application/msword", "application/vnd.ms-excel");

        if (isset($_FILES["file"]["name"]) && $_FILES["file"]["name"] != '') {
            $temp = explode(".", $_FILES["file"]["name"]);
            $extension = end($temp);

            if ($_FILES["file"]["size"] < 100000 && in_array($extension, $allowedExts)) {

                $target = "./../image/data/";
                $path = "./image/data/";
                $target = $target . basename($_FILES['file']['name']);
                $ok = 1;
                if (move_uploaded_file($_FILES['file']['tmp_name'], $target)) {
                    //echo "The file " . basename($_FILES['uploaded']['name']) . " has been uploaded";
                    if (!empty($this->request->post['avatar'])) {
                        unlink('./../' . $this->request->post['avatar']);
                    }
                    $this->request->post['avatar'] = $path . basename($_FILES['file']['name']);
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } else {
            return true;
        }
    }

    /*proposals function get proposals of selected project*/
    public function proposals() {

        $this->load->language('catalog/projects');
        $this->load->model('catalog/projects');
        if (isset($this->session->data['success'])) {
            $this->data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        }

        $this->document->setTitle($this->language->get('add_project_heading_title'));

        // managing breadrumbs
        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('catalog/projects', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => ' :: '
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('proposal_heading'),
            'href' => $this->url->link('catalog/projects/proposals', 'token=' . $this->session->data['token'] . '&project_id=' . $this->request->get['project_id'], 'SSL'),
            'separator' => ' :: '
        );

        if (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1'))) {
            $this->data['base'] = $this->config->get('config_ssl');
        } else {
            $this->data['base'] = $this->config->get('config_url');
        }



        //pagination
        $url = '';
        if (isset($this->request->get['page'])) {
            $page = $this->request->get['page'];
            $url .= '&page=' . $this->request->get['page'];
        } else {
            $page = 1;
        }

        $data = array(
            'page' => $page,
            'limit' => $this->config->get('config_admin_limit'),
            'start' => $this->config->get('config_admin_limit') * ($page - 1),
        );

        $total = $this->model_catalog_projects->countProposals($this->request->get['project_id']);
        $pagination = new Pagination();
        $pagination->total = $total;
        $pagination->page = $page;
        $pagination->limit = $this->config->get('config_admin_limit');
        $pagination->text = $this->language->get('text_pagination');
        $pagination->url = $this->url->link('catalog/projects/proposals', '&project_id=' . $this->request->get['project_id'] . '&token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');

        $status_list = $this->language->get('status_list');
        $this->data['pagination'] = $pagination->render();

        $this->data['proposals'] = $this->model_catalog_projects->getProposalsList($this->request->get['project_id'], $data);

        //preparing variables to view at front end.
        $project = $this->model_catalog_projects->getProject($this->request->get['project_id']);
        $this->data['active_project'] = $project;
        $this->data['project_title'] = $project['name'];
        $this->data['heading_title'] = $this->language->get('heading_title');
        $this->data['no_record_found'] = $this->language->get('no_record_found');

        $this->data['delete_confirm'] = $this->language->get('delete_confirm');

        $this->template = 'catalog/proposals.tpl';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render());
    }

    /*function to modify the status of the the proposal*/
    public function proposal_action() {
        $this->load->language('catalog/projects');
        $this->load->model('catalog/projects');
        $this->model_catalog_projects->updateProposalStatus($this->request->get['status'], $this->request->get['proposal']);
        $this->session->data['success'] = $this->language->get('text_update');
        $this->redirect($this->url->link('catalog/projects/proposals', '&project_id=' . $this->request->get['project_id'] .'&token=' . $this->session->data['token'], 'SSL'));
    }

    public function delete_proposal() {
        $this->load->language('catalog/projects');
        $this->load->model('catalog/projects');
        $this->model_catalog_projects->deleteProposal($this->request->get['proposal']);
        $this->session->data['success'] = $this->language->get('text_success_dlt');
        $this->redirect($this->url->link('catalog/projects/proposals', '&project_id=' . $this->request->get['project_id'] . '&token=' . $this->session->data['token'], 'SSL'));
    }

}

?>