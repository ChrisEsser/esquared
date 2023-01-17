<?php

class AjaxDataController extends BaseController
{
    protected $pageLength = 2;
    protected $page = 1;
    protected $filters = [];
    protected $sort = [];
    protected $offset = 0;

    public function beforeAction()
    {
        HTTP::removePageFromHistory();
        $this->render = false;

        if (!Auth::isAdmin()) {
            HTML::addAlert('Unauthorized access');
            HTTP::redirect('/');
        }

        $requestData = json_decode($_POST['tableData']);

        $this->page = $requestData->page ?? $this->page;
        $this->pageLength = $requestData->len ?? $this->pageLength;
        $this->filters = $requestData->filter ?? $this->filters;
        $this->sort = $requestData->sort ?? $this->sort;

        $this->offset = ($this->page - 1) * $this->pageLength;
    }

    public function properties()
    {
        $where = $order = [];

        $db = new StandardQuery();

        $sql = 'SELECT p.* 
                FROM properties p';

        $where['deleted'] = 'p.deleted = 0';

        $params = [];
        foreach ($this->filters as $filter) {
            foreach ($filter as $col => $value) {
                if (in_array($col, ['name', 'description'])) {
                    $where[$col] = 'p.' . $col . ' LIKE :' . $col;
                    $params[$col] = '%' . $value . '%';
                }
            }
        }

        foreach ($this->sort as $sort) {
            foreach ($sort as $col => $dir) {
                if (in_array($col, ['name', 'description'])) {
                    $order[$col] = $col . ' ' . $dir;
                }
            }
        }

        $whereString = (!empty($where)) ? ' WHERE ' . implode(' AND ', $where) : '';
        $sql .= ' ' . $whereString;

        $total = $db->count($sql, $params);
        $totalPages = ceil($total / $this->pageLength);

        $orderString = (!empty($order)) ? ' ORDER BY ' . implode(', ', $order) : '';
        $sql .= ' ' . $orderString;

        $sql .= ' LIMIT ' . $this->offset . ', ' . $this->pageLength;

        $data = $db->rows($sql, $params);

        echo json_encode([
            'total' => $total,
            'pages' => $totalPages,
            'page' => $this->page,
            'data' => $data,
        ]);
    }

    public function units()
    {
        $where = $order = [];

        $db = new StandardQuery();

        $sql = 'SELECT u.*, p.name AS property 
                FROM units u
                INNER JOIN properties p ON p.property_id = u.property_id';

        $where['deleted'] = 'p.deleted = 0';

        $params = [];
        foreach ($this->filters as $filter) {
            foreach ($filter as $col => $value) {
                if (in_array($col, ['name', 'description', 'rent'])) {
                    $where[$col] = 'u.' . $col . ' LIKE :' . $col;
                    $params[$col] = '%' . $value . '%';
                } else if ($col == 'status') {
                    $in = [];
                    foreach ((new Unit())->statusStrings() as $code => $statusString) {
                        if (stripos($statusString, $value) !== false) $in[] = $code;
                    }
                    $where[$col] = 'u.status IN (' . implode(', ', $in) . ') ';
                } else if ($col == 'property') {
                    $where[$col] = 'p.name LIKE :' . $col;
                    $params[$col] = '%' . $value . '%';
                } else if ($col == 'property_id') {
                    $where[$col] = 'u.' . $col . ' = :' . $col;
                    $params[$col] = $value;
                }
            }
        }

        foreach ($this->sort as $sort) {
            foreach ($sort as $col => $dir) {
                if (in_array($col, ['name', 'description', 'rent', 'status'])) {
                    $order[$col] = $col . ' ' . $dir;
                } else if ($col == 'property') {
                    $order[$col] = 'p.name ' . $dir;
                }
            }
        }

        $whereString = (!empty($where)) ? ' WHERE ' . implode(' AND ', $where) : '';
        $sql .= ' ' . $whereString;

        $total = $db->count($sql, $params);
        $totalPages = ceil($total / $this->pageLength);

        $orderString = (!empty($order)) ? ' ORDER BY ' . implode(', ', $order) : '';
        $sql .= ' ' . $orderString;

        $sql .= ' LIMIT ' . $this->offset . ', ' . $this->pageLength;

        $data = $db->rows($sql, $params);

        echo json_encode([
            'total' => $total,
            'pages' => $totalPages,
            'page' => $this->page,
            'data' => $data,
        ]);
    }

    public function documents()
    {
        $where = $order = [];

        $db = new StandardQuery();

        $sql = 'SELECT d.*, CONCAT(u.first_name, \' \', u.last_name) AS user 
                FROM documents d
                INNER JOIN users u ON u.user_id = d.user_id';

        $where['deleted'] = 'd.deleted = 0';

        $params = [];
        foreach ($this->filters as $filter) {
            foreach ($filter as $col => $value) {
                if (in_array($col, ['name', 'description', 'created'])) {
                    $where[$col] = 'd.' . $col . ' LIKE :' . $col;
                    $params[$col] = '%' . $value . '%';
                } else if ($col == 'user') {
                    $where[$col] = '(u.first_name LIKE :' . $col . ' OR u.last_name LIKE :' . $col . ' OR CONCAT(u.first_name, \' \', u.last_name) LIKE :' . $col . ' )';
                    $params[$col] = '%' . $value . '%';
                } else if ($col == 'property_id') {
                    $where[$col] = 'd.' . $col . ' = :' . $col;
                    $params[$col] = $value;
                }
            }
        }

        foreach ($this->sort as $sort) {
            foreach ($sort as $col => $dir) {
                if (in_array($col, ['name', 'description', 'created'])) {
                    $order[$col] = $col . ' ' . $dir;
                }
            }
        }

        $whereString = (!empty($where)) ? ' WHERE ' . implode(' AND ', $where) : '';
        $sql .= ' ' . $whereString;

        $total = $db->count($sql, $params);
        $totalPages = ceil($total / $this->pageLength);

        $orderString = (!empty($order)) ? ' ORDER BY ' . implode(', ', $order) : '';
        $sql .= ' ' . $orderString;

        $sql .= ' LIMIT ' . $this->offset . ', ' . $this->pageLength;

        $data = $db->rows($sql, $params);

        echo json_encode([
            'total' => $total,
            'pages' => $totalPages,
            'page' => $this->page,
            'data' => $data,
        ]);
    }

    public function notes()
    {
        $where = $order = [];

        $db = new StandardQuery();

        $sql = 'SELECT n.*, CONCAT(u.first_name, \' \', u.last_name) AS user 
                FROM notes n
                INNER JOIN users u ON u.user_id = n.created_by';

        $where['deleted'] = 'n.deleted = 0';

        $params = [];
        foreach ($this->filters as $filter) {
            foreach ($filter as $col => $value) {
                if (in_array($col, ['created', 'note'])) {
                    $where[$col] = 'p.' . $col . ' LIKE :' . $col;
                    $params[$col] = '%' . $value . '%';
                } else if ($col == 'type') {
                    $in = [];
                    foreach ((new Note())->typeStrings() as $code => $typeString) {
                        if (stripos($typeString, $value) !== false) $in[] = $code;
                    }
                    $where[$col] = 'n.type IN (' . implode(', ', $in) . ') ';
                } else if ($col == 'user') {
                    $where['user'] = '(u.last_name LIKE :user OR u.first_name LIKE :user OR CONCAT(u.first_name, \' \', u.last_name) LIKE :user )';
                    $params['user'] = '%' . $value . '%';
                } else if ($col == 'property_id') {
                    $where['property_id'] = 'n.property_id = :property_id ';
                    $params['property_id'] = $value;
                }
            }
        }

        foreach ($this->sort as $sort) {
            foreach ($sort as $col => $dir) {
                if (in_array($col, ['created', 'note', 'type'])) {
                    $order[$col] = $col . ' ' . $dir;
                } else if ($col == 'user') {
                    $order[$col] = 'u.last_name ' . $dir;
                }
            }
        }

        $whereString = (!empty($where)) ? ' WHERE ' . implode(' AND ', $where) : '';
        $sql .= ' ' . $whereString;

        $total = $db->count($sql, $params);
        $totalPages = ceil($total / $this->pageLength);

        $orderString = (!empty($order)) ? ' ORDER BY ' . implode(', ', $order) : '';
        $sql .= ' ' . $orderString;

        $sql .= ' LIMIT ' . $this->offset . ', ' . $this->pageLength;

        $data = $db->rows($sql, $params);

        echo json_encode([
            'total' => $total,
            'pages' => $totalPages,
            'page' => $this->page,
            'data' => $data,
        ]);
    }

    public function users()
    {
        $where = $order = [];

        $db = new StandardQuery();

        $sql = 'SELECT u.*, IFNULL(un.property_id, 0) AS property_id,
                       CASE 
                          WHEN ISNULL(un.name) = 0 THEN CONCAT(p.name, \' | \', un.name) 
                          ELSE \'\' END AS unit
                FROM users u
                LEFT JOIN units un ON un.unit_id = u.unit_id
                INNER JOIN properties p ON p.property_id = un.property_id';

        $where['deleted'] = 'u.deleted = 0';

        $params = [];
        foreach ($this->filters as $filter) {
            foreach ($filter as $col => $value) {
                if (in_array($col, ['first_name', 'last_name', 'email', 'admin'])) {
                    $where[$col] = 'u.' . $col . ' LIKE :' . $col;
                    $params[$col] = '%' . $value . '%';
                } else if ($col == 'unit') {
                    $where[$col] = 'un.name LIKE :' . $col;
                    $params[$col] = '%' . $value . '%';
                } else if ($col == 'unit_id') {
                    $where[$col] = 'un.' . $col . ' = :' . $col;
                    $params[$col] = $value;
                }
            }
        }

        foreach ($this->sort as $sort) {
            foreach ($sort as $col => $dir) {
                if (in_array($col, ['first_name', 'last_name', 'email', 'admin'])) {
                    $order[$col] = $col . ' ' . $dir;
                } else if ($col == 'unit') {
                    $order[$col] = 'un.name ' . $dir;
                }
            }
        }

        $whereString = (!empty($where)) ? ' WHERE ' . implode(' AND ', $where) : '';
        $sql .= ' ' . $whereString;

        $total = $db->count($sql, $params);
        $totalPages = ceil($total / $this->pageLength);

        $orderString = (!empty($order)) ? ' ORDER BY ' . implode(', ', $order) : '';
        $sql .= ' ' . $orderString;

        $sql .= ' LIMIT ' . $this->offset . ', ' . $this->pageLength;

        $data = $db->rows($sql, $params);

        echo json_encode([
            'total' => $total,
            'pages' => $totalPages,
            'page' => $this->page,
            'data' => $data,
        ]);
    }

    public function scraperUrls()
    {
        $where = $order = [];

        $db = new StandardQuery();

        $sql = 'SELECT s.* 
                FROM scraper_urls s';

        $params = [];
        foreach ($this->filters as $filter) {
            foreach ($filter as $col => $value) {
                if (in_array($col, ['name', 'state', 'last_scraped'])) {
                    $where[$col] = 's.' . $col . ' LIKE :' . $col;
                    $params[$col] = '%' . $value . '%';
                }
            }
        }

        foreach ($this->sort as $sort) {
            foreach ($sort as $col => $dir) {
                if (in_array($col, ['name', 'state', 'last_scraped', 'leads_count'])) {
                    $order[$col] = 's.' . $col . ' ' . $dir;
                }
            }
        }

        $whereString = (!empty($where)) ? ' WHERE ' . implode(' AND ', $where) : '';
        $sql .= ' ' . $whereString;

        $total = $db->count($sql, $params);
        $totalPages = ceil($total / $this->pageLength);

        $orderString = (!empty($order)) ? ' ORDER BY ' . implode(', ', $order) : '';
        $sql .= ' ' . $orderString;

        $sql .= ' LIMIT ' . $this->offset . ', ' . $this->pageLength;

        $data = $db->rows($sql, $params);

        echo json_encode([
            'total' => $total,
            'pages' => $totalPages,
            'page' => $this->page,
            'data' => $data,
        ]);
    }

    public function scraperLeads()
    {
        $where = $order = [];

        $db = new StandardQuery();

        $sql = 'SELECT l.* 
                FROM scraper_leads l ';

        $where['deleted'] = 'l.deleted = 0';

        $params = [];
        foreach ($this->filters as $filter) {
            foreach ($filter as $col => $value) {
                if (in_array($col, ['url', 'judgment_amount', 'last_seen'])) {
                    $where[$col] = 'l.' . $col . ' LIKE :' . $col;
                    $params[$col] = '%' . $value . '%';
                } else if ($col == 'address') {
                    $where[$col] = ' (l.street LIKE :address OR l.city LIKE :address OR l.state LIKE :address OR l.zip LIKE :address ) ';
                    $params['address'] = '%' . $value . '%';
                } else if ($col == 'active') {
                    $where['active'] = 'l.active = :active ';
                    $params['active'] = $value;
                } else if ($col == 'url_id') {
                    $where['url_id'] = 'l.url_id = :url_id ';
                    $params['url_id'] = $value;
                }
            }
        }

        foreach ($this->sort as $sort) {
            foreach ($sort as $col => $dir) {
                if (in_array($col, ['url', 'judgment_amount', 'last_seen', 'active'])) {
                    $order[$col] = 'l.' . $col . ' ' . $dir;
                }
            }
        }

        $whereString = (!empty($where)) ? ' WHERE ' . implode(' AND ', $where) : '';
        $sql .= ' ' . $whereString;

        $total = $db->count($sql, $params);
        $totalPages = ceil($total / $this->pageLength);

        $orderString = (!empty($order)) ? ' ORDER BY ' . implode(', ', $order) : '';
        $sql .= ' ' . $orderString;

        $sql .= ' LIMIT ' . $this->offset . ', ' . $this->pageLength;

        $data = $db->rows($sql, $params);

        echo json_encode([
            'total' => $total,
            'pages' => $totalPages,
            'page' => $this->page,
            'data' => $data,
        ]);
    }

    public function payments()
    {
        $where = $order = [];

        $db = new StandardQuery();

        $sql = 'SELECT p.*, CONCAT(u.first_name, " ", u.last_name) AS payment_by, 
                       IFNULL(un.name, "") AS unit_name, IFNULL(pr.name, "") AS property_name,
                       IFNULL(un.unit_id, 0) AS unit_id
                FROM payment_history p
                INNER JOIN users u ON u.user_id = p.user_id
                LEFT JOIN units un ON un.unit_id = p.unit_id
                INNER JOIN properties pr ON pr.property_id = un.property_id ';

        $params = [];
        foreach ($this->filters as $filter) {
            foreach ($filter as $col => $value) {
                if (in_array($col, ['payment_date', 'amount', 'method', 'type'])) {
                    $where[$col] = 'p.' . $col . ' LIKE :' . $col;
                    $params[$col] = '%' . $value . '%';
                } else if ($col == 'payment_by') {
                    $where[$col] = '(u.first_name LIKE :' . $col . ' OR u.last_name LIKE :' . $col . ' OR CONCAT(u.first_name, \' \', u.last_name) LIKE :' . $col . ' )';
                    $params[$col] = '%' . $value . '%';
                } else if ($col == 'unit_name') {
                    $where[$col] = '(un.name LIKE :' . $col . ' OR pr.name LIKE :' . $col . ' )';
                    $params[$col] = '%' . $value . '%';
                }
            }
        }

        foreach ($this->sort as $sort) {
            foreach ($sort as $col => $dir) {
                if (in_array($col, ['payment_date', 'amount', 'method', 'type'])) {
                    $order[$col] = $col . ' ' . $dir;
                } else if ($col == 'payment_by') {
                    $order[$col] = 'u.last_name ' . $dir;
                } else if ($col == 'unit_name') {
                    $order[$col] = 'pr.name ' . $dir . ', un.name ' . $dir;
                }
            }
        }

        $whereString = (!empty($where)) ? ' WHERE ' . implode(' AND ', $where) : '';
        $sql .= ' ' . $whereString;

        $total = $db->count($sql, $params);
        $totalPages = ceil($total / $this->pageLength);

        $orderString = (!empty($order)) ? ' ORDER BY ' . implode(', ', $order) : '';
        $sql .= ' ' . $orderString;

        $sql .= ' LIMIT ' . $this->offset . ', ' . $this->pageLength;

        $data = $db->rows($sql, $params);

        echo json_encode([
            'total' => $total,
            'pages' => $totalPages,
            'page' => $this->page,
            'data' => $data,
        ]);

    }

}