<?php declare (strict_types = 1);

namespace App\Project;

use App\Project\ProjectModel;
use Diversen\Lang;
use Exception;
use Pebble\ACL;
use Pebble\Auth;
use Pebble\Template;
use Pebble\JSON;

class Controller
{

    public function __construct()
    {
        $auth = new Auth();
        $this->auth_id = $auth->getAuthId();
    }

    public function index()
    {

        (new ACL())->isAuthenticatedOrThrow();

        $template_data = (new ProjectModel())->getIndexData($this->auth_id);
        $template_data['title'] = Lang::translate('All projects');

        Template::render('App/Project/views/project_index.tpl.php',
            $template_data
        );

    }

    public function view(array $params)
    {

        $access_ary = [
            'entity' => 'project', 
            'entity_id' => $params['project_id'], 
            'right' => 'owner',
            'auth_id' => $this->auth_id,
        ];

        (new ACL())->hasAccessRightsOrThrow($access_ary);

        $template_data = (new ProjectModel())->getViewData($params);
        $template_data['title'] = Lang::translate('View project');

        Template::render('App/Project/views/project_view.tpl.php',
            $template_data
        );
    }

    public function add()
    {
        (new ACL())->isAuthenticatedOrThrow();

        $form_vars = [
            'title' => Lang::translate('Add project'),
        ];

        Template::render('App/Project/views/project_add.tpl.php',
            $form_vars
        );

    }

    public function edit($params)
    {
        $access_ary = [
            'entity' => 'project', 
            'entity_id' => $params['project_id'], 
            'right' => 'owner',
            'auth_id' => $this->auth_id,
        ];

        (new ACL())->hasAccessRightsOrThrow($access_ary);

        $project = (new ProjectModel())->getOne($params['project_id']);

        $form_vars = [
            'title' => Lang::translate('Edit project'),
            'project' => $project,
        ];

        Template::render('App/Project/views/project_edit.tpl.php',
            $form_vars
        );

    }

    public function post()
    {

        $project_model = new ProjectModel();
        $response['error'] = false;

        try {
            (new ACL())->isAuthenticatedOrThrow();
            $_POST['auth_id'] = $this->auth_id;
            $project_model->create($_POST);
            $response['project_redirect'] = "/project";

        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
            $response['post'] = $_POST;
        }

        echo JSON::response($response);
    }

    public function put($params)
    {

        $response['error'] = false;
        $project_model = new ProjectModel();

        try {

            $access_ary = [
                'entity' => 'project', 
                'entity_id' => $params['project_id'], 
                'right' => 'owner',
                'auth_id' => $this->auth_id,
            ];

            (new ACL())->hasAccessRightsOrThrow($access_ary);

            $project_model->update($_POST, $params['project_id']);
            $response['project_redirect'] = "/project";

        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
            $response['post'] = $_POST;
        }

        echo JSON::response($response);
    }

    public function delete($params)
    {

        $response['error'] = false;
        $project_model = new ProjectModel();

        try {

            $access_ary = [
                'entity' => 'project', 
                'entity_id' => $params['project_id'], 
                'right' => 'owner',
                'auth_id' => $this->auth_id,
            ];

            (new ACL())->hasAccessRightsOrThrow($access_ary);

            $project_model->delete($params['project_id']);
            $response['project_redirect'] = "/project";

        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
            $response['post'] = $_POST;
        }

        echo JSON::response($response);
    }
}
