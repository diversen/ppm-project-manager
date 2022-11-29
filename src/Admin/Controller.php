<?php

declare(strict_types=1);

namespace App\Admin;

use App\AppUtils;
use App\Utils\AppPaginationUtils;
use App\Admin\TableUtils as DBUtils;
use Exception;

class Controller extends AppUtils
{

    public function __construct()
    {

        parent::__construct();
        $this->acl_role->hasRoleOrThrow(['right' => 'admin', 'auth_id' => $this->acl_role->getAuthId()]);
        $this->db_utils = new DBUtils();

        $tables['auth'] = [
            'table' => 'auth',
            'table_human' => 'User',
            'primary_key' => 'id',
            'columns' => ['id', 'email', 'created', 'verified', 'locked'],
            'columns_human' => ['ID', 'Email', 'Created', 'Verified', 'Locked'],
            'references' => [],
            'disabled' => ['id'],
        ];

        $tables['project'] = [
            'table' => 'project',
            'table_human' => 'Project',
            'primary_key' => 'id',
            'columns' => ['id', 'title', 'note', 'created', 'auth_id'],
            'columns_human' => ['ID', 'Title', 'Note', 'Created', 'User ID'],
            'references' => ['auth_id' => 'auth.id'],
            'disabled' => ['id'],
        ];

        $tables['task'] = [
            'table' => 'task',
            'table_human' => 'Project Task',
            'primary_key' => 'id',
            'columns' => ['id', 'title', 'note', 'created', 'project_id', 'auth_id'],
            'columns_human' => ['ID', 'Title', 'Note', 'Created', 'Project ID', 'User ID'],
            'references' => ['project_id' => 'project.id', 'auth_id' => 'auth.id'],
            'disabled' => ['id', 'project_id', 'auth_id'],
        ];

        $this->db_name = $this->db_utils->getDBName();
        $this->tables = $tables;

        define('ADMIN_SUB_MENU_SEP', ' ::');
    }


    /**
     * @route /admin/
     * @verbs GET
     */
    public function index()
    {
        $this->renderPage('Admin/views/index.tpl.php', ['tables' => $this->tables]);
    }

    /**
     * @route /admin/table/:table
     * @verbs GET
     */
    public function table(array $params)
    {

        // Get table definition
        $table = $this->tables[$params['table']];
        $table_name = $table['table'];

        // Default column order
        $column_order = [];
        foreach ($table['columns'] as $column) {
            $column_order[$column] = 'ASC';
        }

        list($rows, $paginator) = AppPaginationUtils::getRowsAndPaginator(
            table_name: $table_name,
            primary_key: $table['primary_key'],
            url_pattern: "/admin/table/$table_name",
            column_order: $column_order,
            per_page: $this->config->get('App.pager_limit'),
            max_pages: 10
        );

        $template_data = [
            'paginator' => $paginator,
            'rows' => $rows,
            'table' => $table,
            'column_order' => $column_order,
        ];

        $this->renderPage('Admin/views/table.tpl.php', $template_data);
    }


    /**
     * Add Database column types to table definition
     */
    private function getTableWithColumnTypes(string $table_name): array
    {

        $table = $this->tables[$table_name];
        $columns_type = $this->db_utils->getColumnTypes($table_name);
        foreach ($table['columns'] as $column) {
            $table['columns_type'][$column] = $columns_type[$column];
        }
        return $table;
    }

    /**
     * @route /admin/table/:table/edit/:id
     * @verbs GET
     */
    public function edit(array $params)
    {

        $table = $this->getTableWithColumnTypes($params['table']);
        $table_name = $table['table'];
        $primary_key = $table['primary_key'];

        $row = $this->db->getOne($table_name, [$primary_key => $params['id']]);
        $error = $this->validateRow($row, $table_name, $params['id']);
        $template_data = [
            'table' => $table,
            'row' => $row,
            'error' => $error,
        ];

        $this->renderPage('Admin/views/edit.tpl.php', $template_data);
    }

    /**
     * @route /admin/table/:table/add
     * @verbs GET
     */
    public function create(array $params)
    {

        $table = $this->getTableWithColumnTypes($params['table']);
        $template_data = [
            'table' => $table,
            'error' => null,
        ];

        $this->renderPage('Admin/views/add.tpl.php', $template_data);
    }

    /**
     * @route /admin/table/:table/put/:id
     * @verbs POST
     */
    public function put(array $params)
    {
        $response['error'] = true;
        $table = $this->getTableWithColumnTypes($params['table']);
        foreach($table['columns_type'] as $column => $type) {
            if ($type === 'tinyint' && !isset($_POST[$column])) {
                $_POST[$column] = '0';
            }
        }

        $table_name = $table['table'];
        $primary_key = $table['primary_key'];

        try {
            $this->db->update($table_name, $_POST, [$primary_key => $params['id']]);
            $response['message'] = 'Row updated';
            $response['error'] = false;
        } catch (Exception $e) {
            $response['message'] = $e->getMessage();
        }

        echo $this->json->response($response);
    }

    /**
     * @route /admin/table/:table/delete/:id
     * @verbs POST
     */
    public function delete(array $params)
    {
        $response['error'] = true;
        $table = $this->getTableWithColumnTypes($params['table']);
        $table_name = $table['table'];
        $primary_key = $table['primary_key'];

        try {
            $this->db->delete($table_name, [$primary_key => $params['id']]);
            $response['error'] = false;
            $this->flash->setMessage('Row deleted', 'success', ['flash_remove' => true]);
        } catch (Exception $e) {
            $response['message'] = $e->getMessage();
        }

        echo $this->json->response($response);
    }

    /**
     * @route /admin/table/:table/view/:id
     * @verbs GET
     */
    public function view(array $params)
    {

        $table = $this->getTableWithColumnTypes($params['table']);
        $table_name = $table['table'];
        $primary_key = $table['primary_key'];

        $row = $this->db->getOne($table_name, [$primary_key => $params['id']]);
        $error = $this->validateRow($row, $table_name, $params['id']);

        $template_data = [
            'table' => $table,
            'row' => $row,
            'error' => $error,
        ];

        $this->renderPage('Admin/views/view.tpl.php', $template_data);
    }

    /**
     * Validate row and return error message
     */
    public function validateRow(array $row, string $table_name, string $id): ?string
    {

        if (empty($row)) {
            $error_message = "The row with the primary id {$id} could not be found in the table `$table_name`. Maybe it was deleted?";
            return $error_message;
        }
        return null;
    }
}
