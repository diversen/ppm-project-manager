<?php declare (strict_types = 1);

namespace App\Project;

use App\Project\ProjectModel;
use Diversen\Lang;
use Exception;
use Pebble\ACL;
use Pebble\Auth;
use Pebble\Template;
use Pebble\JSON;
use App\AppACL;

class Controller
{

    public function __construct()
    {
        $auth = Auth::getInstance();
        $this->auth_id = $auth->getAuthId();
    }

    /**
     * @route /project
     * @verbs GET
     */
    public function index()
    {

        (new ACL())->isAuthenticatedOrThrow();

        $template_data = (new ProjectModel())->getIndexData($this->auth_id);
        $template_data['title'] = Lang::translate('All projects');

        Template::render('App/Project/views/project_index.tpl.php',
            $template_data
        );

    }

    /**
     * @route /project/view/:project_id
     * @verbs GET
     */
    public function view(array $params)
    {

        $app_acl = new AppAcl;
        $app_acl->authUserIsProjectOwner($params['project_id']);

        $template_data = (new ProjectModel())->getViewData($params);
        $template_data['title'] = Lang::translate('View project');

        Template::render('App/Project/views/project_view.tpl.php',
            $template_data
        );
    }

    /**
     * @route /project/add
     * @verbs GET
     */
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

    /**
     * @route /project/edit/:project_id
     * @verbs GET
     */
    public function edit($params)
    {
        $app_acl = new AppAcl;
        $app_acl->authUserIsProjectOwner($params['project_id']);

        $project = (new ProjectModel())->getOne($params['project_id']);

        $form_vars = [
            'title' => Lang::translate('Edit project'),
            'project' => $project,
        ];

        Template::render('App/Project/views/project_edit.tpl.php',
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
            (new ACL())->isAuthenticatedOrThrow();
            $_POST['auth_id'] = $this->auth_id;

            $project_model = new ProjectModel();
            $project_model->create($_POST);
            $response['project_redirect'] = "/project";

        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
            $response['post'] = $_POST;
        }

        echo JSON::responseAddRequest($response);
    }

    /**
     * @route /project/put/:project_id
     * @verbs POST
     */
    public function put($params)
    {

        $response['error'] = false;
        
        try {

            $app_acl = new AppAcl;
            $app_acl->authUserIsProjectOwner($params['project_id']);

            $project_model = new ProjectModel();
            $project_model->update($_POST, $params['project_id']);
            $response['project_redirect'] = "/project";

        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
            $response['post'] = $_POST;
        }

        echo JSON::responseAddRequest($response);
    }

    /**
     * @route /project/delete/:project_id
     * @verbs POST
     */
    public function delete($params)
    {

        $response['error'] = false;
        
        try {

            $app_acl = new AppAcl;
            $app_acl->authUserIsProjectOwner($params['project_id']);

            $project_model = new ProjectModel();
            $project_model->delete($params['project_id']);
            $response['project_redirect'] = "/project";

        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
            $response['post'] = $_POST;
        }

        echo JSON::responseAddRequest($response);
    }
}
