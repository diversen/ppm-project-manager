<?php

declare(strict_types=1);

namespace App\Admin;

use App\AppUtils;
use JasonGrimes\Paginator;
use Pebble\Pagination\PaginationUtils;
use Pebble\Pager;
use App\Admin\TableUtils as DBUtils;
use Pebble\Exception\JSONException;
use Exception;
use Pebble\Attributes\Route;
use Pebble\Router\Request;

class Controller extends AppUtils
{
    private $tables;
    private $db_utils;

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

        $this->tables = $tables;

        define('ADMIN_SUB_MENU_SEP', ' ::');
    }


    /**
     * @route /admin/
     * @verbs GET
     */

    #[Route(path: '/admin/')]
    public function index()
    {
        $this->renderPage('Admin/views/index.tpl.php', ['tables' => $this->tables]);
    }

    #[Route(path: '/admin/table/:table')]
    public function table(Request $request)
    {
        // Get table definition
        $table = $this->tables[$request->param('table')];
        $table_name = $table['table'];
        $primary_key = $table['primary_key'];

        // Default column order
        $default_order_by = [];
        foreach ($table['columns'] as $column) {
            $default_order_by[$column] = 'ASC';
        }

        $session_key = "admin_$table_name";
        $pagination_utils = new PaginationUtils($default_order_by, $session_key);
        $order_by = $pagination_utils->getOrderByFromRequest($session_key);

        $num_rows = $this->db->getTableNumRows($table_name, $primary_key);
        $url_pattern = $pagination_utils->getPaginationURLPattern("/admin/table/$table_name");
        $pager = new Pager($num_rows, 10);

        $paginator = new Paginator($num_rows, $pager->limit, $pager->page, $url_pattern);
        $rows = $this->db->getAll($table_name, [], $order_by, [$pager->offset, $pager->limit]);

        $template_data = [
            'paginator' => $paginator,
            'rows' => $rows,
            'table' => $table,
            'order_by' => $default_order_by,
            'session_key' => $session_key,
            'title' => 'Admin :: ' . $table['table_human'],
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

    #[Route(path: '/admin/table/:table/edit/:id')]
    public function edit(Request $request)
    {
        $table = $this->getTableWithColumnTypes($request->param('table'));
        $table_name = $table['table'];
        $primary_key = $table['primary_key'];

        $row = $this->db->getOne($table_name, [$primary_key => $request->param('id')]);
        $error = $this->validateRow($row, $table_name, $request->param('id'));
        $template_data = [
            'table' => $table,
            'row' => $row,
            'error' => $error,
        ];

        $this->renderPage('Admin/views/edit.tpl.php', $template_data);
    }

    #[Route(path: '/admin/table/:table/add')]
    public function create(Request $request)
    {
        $table = $this->getTableWithColumnTypes($request->param('table'));
        $template_data = [
            'table' => $table,
            'error' => null,
        ];

        $this->renderPage('Admin/views/add.tpl.php', $template_data);
    }

    #[Route(path: '/admin/table/:table/put/:id', verbs: ['POST'])]
    public function put(Request $request)
    {
        $response['error'] = true;
        $table = $this->getTableWithColumnTypes($request->param('table'));
        foreach ($table['columns_type'] as $column => $type) {
            if ($type === 'tinyint' && !isset($_POST[$column])) {
                $_POST[$column] = '0';
            }
        }

        $table_name = $table['table'];
        $primary_key = $table['primary_key'];

        try {
            $this->db->update($table_name, $_POST, [$primary_key => $request->param('id')]);
            $response['message'] = 'Row updated';
            $this->json->renderSuccess($response);
        } catch (Exception $e) {
            throw new JSONException($e->getMessage(), 400);
        }
    }

    #[Route(path: '/admin/table/:table/delete/:id', verbs: ['POST'])]
    public function delete(Request $request)
    {
        $response['error'] = true;
        $table = $this->getTableWithColumnTypes($request->param('table'));
        $table_name = $table['table'];
        $primary_key = $table['primary_key'];

        try {
            $this->db->delete($table_name, [$primary_key => $request->param('id')]);
            $this->flash->setMessage('Row deleted', 'success', ['flash_remove' => true]);
            $this->json->renderSuccess();
        } catch (Exception $e) {
            throw new JSONException($e->getMessage(), 400);
        }
    }

    #[Route(path: '/admin/table/:table/view/:id')]
    public function view(Request $request)
    {
        $table = $this->getTableWithColumnTypes($request->param('table'));
        $table_name = $table['table'];
        $primary_key = $table['primary_key'];

        $row = $this->db->getOne($table_name, [$primary_key => $request->param('id')]);
        $error = $this->validateRow($row, $table_name, $request->param('id'));

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
