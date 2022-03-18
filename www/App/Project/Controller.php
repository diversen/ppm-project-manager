<?php

declare(strict_types=1);

namespace App\Project;

use App\Project\ProjectModel;
use Diversen\Lang;
use Exception;
use Pebble\Template;
use Pebble\JSON;
use App\AppMain;

class Controller
{
    public function __construct()
    {
        $app_main = new AppMain();
        $this->app_acl = $app_main->getAppACL();
    }

    /**
     * @route /project
     * @verbs GET
     */
    public function index()
    {
        $this->app_acl->isAuthenticatedOrThrow();

        $template_data = (new ProjectModel())->getIndexData($this->app_acl->getAuthId());
        $template_data['title'] = Lang::translate('All projects');

        Template::render(
            'App/Project/views/project_index.tpl.php',
            $template_data
        );
    }

    /**
     * @route /project/view/:project_id
     * @verbs GET
     */
    public function view(array $params)
    {
        $this->app_acl->authUserIsProjectOwner($params['project_id']);

        $template_data = (new ProjectModel())->getViewData($params);
        $template_data['title'] = Lang::translate('View project');

        Template::render(
            'App/Project/views/project_view.tpl.php',
            $template_data
        );
    }

    /**
     * @route /project/add
     * @verbs GET
     */
    public function add()
    {
        $this->app_acl->isAuthenticatedOrThrow();

        $form_vars = [
            'title' => Lang::translate('Add project'),
        ];

        Template::render(
            'App/Project/views/project_add.tpl.php',
            $form_vars
        );
    }

    /**
     * @route /project/edit/:project_id
     * @verbs GET
     */
    public function edit($params)
    {
        $this->app_acl->authUserIsProjectOwner($params['project_id']);
        $project = (new ProjectModel())->getOne($params['project_id']);

        $form_vars = [
            'title' => Lang::translate('Edit project'),
            'project' => $project,
        ];

        Template::render(
            'App/Project/views/project_edit.tpl.php',
            $form_vars
        );
    }

    /**
     * @route /project/post
     * @verbs POST
     */
    public function post()
    {
        $response['error'] = false;

        try {
            $this->app_acl->isAuthenticatedOrThrow();
            $_POST['auth_id'] = $this->app_acl->getAuthId();

            $project_model = new ProjectModel();
            $project_model->create($_POST);
            $response['project_redirect'] = "/project";
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
            $response['post'] = $_POST;
        }

        echo JSON::response($response);
    }

    /**
     * @route /project/put/:project_id
     * @verbs POST
     */
    public function put($params)
    {
        $response['error'] = false;

        try {
            $this->app_acl->authUserIsProjectOwner($params['project_id']);

            $project_model = new ProjectModel();
            $project_model->update($_POST, $params['project_id']);

            $response['project_redirect'] = "/project";
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
            $response['post'] = $_POST;
        }

        echo JSON::response($response);
    }

    /**
     * @route /project/delete/:project_id
     * @verbs POST
     */
    public function delete($params)
    {
        $response['error'] = false;

        try {
            $this->app_acl->authUserIsProjectOwner($params['project_id']);

            $project_model = new ProjectModel();
            $project_model->delete($params['project_id']);
            $response['project_redirect'] = "/project";
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
            $response['post'] = $_POST;
        }

        echo JSON::response($response);
    }
}
