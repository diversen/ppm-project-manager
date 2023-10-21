<?php

declare(strict_types=1);

namespace App\Admin;

use App\AppUtils;
use Pebble\Pagination\PaginationUtils;
use Pebble\Pager;
use App\Admin\TableUtils as DBUtils;
use Pebble\Exception\JSONException;
use Exception;
use Pebble\Attributes\Route;
use Pebble\Router\Request;
use Pebble\Special;


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
        $context = $this->getContext(['tables' => $this->tables]);
        echo $this->twig->render('admin/index.twig', $context);
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
        $order_by = $pagination_utils->getOrderBy();

        $num_rows = $this->db->getTableNumRows($table_name, $primary_key);
        $url_pattern = $pagination_utils->getPaginationURLPattern("/admin/table/$table_name");

        $pager = new Pager($num_rows, 10);
        $rows = $this->db->getAll($table_name, [], $order_by, [$pager->offset, $pager->limit]);
        $rows = Special::encodeAry($rows);

        $paginator = $pagination_utils->getPaginator($num_rows, $pager->limit, $pager->page, $url_pattern);
        $sorting = $pagination_utils->getSortingURLPaths();

        // Alter column fields to reference links or values
        foreach ($rows as $key => $row) {
            foreach ($table['columns'] as $col) {
                $display_value = HTMLUtils::getReferenceLinkHTMLOrValue($col, $table['references'], $row[$col]);
                $rows[$key][$col] = $display_value;
            }
        }

        $context = [
            'paginator' => $paginator,
            'sorting' => $sorting,
            'rows' => $rows,
            'table' => $table,
            'order_by' => $default_order_by,
            'session_key' => $session_key,
            'title' => 'Admin :: ' . $table['table_human'],
        ];

        $context = $this->getContext($context);
        echo $this->twig->render('admin/table.twig', $context);

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
        $row = Special::encodeAry($row);

        $disabled = $table['disabled'] ?? [];
        $references = $table['references'] ?? [];

        $html_elements = [];
        foreach ($table['columns'] as $key => $column) {

            $attr = [];
            $attr['class'] = 'form-control';
            $attr['id'] = $column;
            $attr['name'] = $column;

            if (HTMLUtils::isDisabled($column, $disabled)) {
                $attr['disabled'] = 'disabled';
            }

            $value = (string)$row[$column];
            $column_type = $table['columns_type'][$column];
            $label = $table['columns_human'][$key];

            $reference_link = HTMLUtils::getReferenceLinkHTML($column, $references, $value, true);
            if ($reference_link) {
                $label .= " ($reference_link) ";
            }

            $html_elements[] = HTMLUtils::getHTMLElement($column_type, $value, $attr, $label);
        }

        $this->log->debug("table", $table);

        $context = [
            'table' => $table,
            'row' => $row,
            'error' => $error,
            'title' => 'Edit row',
            'html_elements' => $html_elements,
            'parent_url' => '/admin/table/' . $table_name,
        ];

        $context = $this->getContext($context);
        echo $this->twig->render('admin/edit.twig', $context);
    }

    #[Route(path: '/admin/table/:table/add')]
    public function create(Request $request)
    {
        // TODO
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
        $row = Special::encodeAry($row);
        $error = $this->validateRow($row, $table_name, $request->param('id'));

        $row_human = [];
        foreach ($table['columns'] as $key => $column) {
            $reference_link = HTMLUtils::getReferenceLink($column, $table['references'], $row[$column]);
            $link = $row[$column];
            if ($reference_link) {
                $link = "<a href='$reference_link'>$row[$column]</a>";
            }
            $row_human[$table['columns_human'][$key]] = $link;
        }

        $context = [
            'table' => $table,
            'row' => $row,
            'row_human' => $row_human,
            'error' => $error,
            'title' => 'View row',
            'parent_url' => '/admin/table/' . $table_name,
        ];

        $context = $this->getContext($context);
        echo $this->twig->render('admin/view.twig', $context);
    }

    /**
     * Validate row and return error message
     */
    private function validateRow(array $row, string $table_name, string $id): ?string
    {
        if (empty($row)) {
            $error_message = "The row with the primary id {$id} could not be found in the table `$table_name`. Maybe it was deleted?";
            return $error_message;
        }
        return null;
    }
}
