<?php

class AjaxDataController extends BaseController
{
    protected $pageLength = 2;
    protected $page = 1;
    protected $filters = [];
    protected $order = [];

    public function beforeAction()
    {
        HTTP::removePageFromHistory();
        $this->render = false;

        if (!Auth::isAdmin()) {
            HTML::addAlert('Unauthorized access');
            HTTP::redirect('/');
        }

        $this->page = $_POST['page'] ?? $this->page;
        $this->pageLength = $_POST['len'] ?? $this->pageLength;
        $this->filters = $_POST['filters'] ?? $this->filters;
        $this->filters = json_decode($this->filters);
        $this->order = $_POST['order'] ?? $this->order;
    }

    public function properties()
    {
        $where = [];
        foreach ($this->filters as $key => $value) {
            if ($key == 'name' && !empty($value)) {
                $where['name'] = [
                    'operator' => 'LIKE',
                    'value' => '%' . $value . '%'
                ];
            } else if ($key == 'description' && !empty($value)) {
                $where['description'] = [
                    'operator' => 'LIKE',
                    'value' => '%' . $value . '%'
                ];
            }
        }

        $collection = Property::find($where);
        $collection->activePagination($this->pageLength);
        $collection->paginate($this->page);
        $total = $collection->queryFoundModels();
        $totalPAges = $collection->getTotalPages();

        $data = [];
        foreach ($collection as $row) {
            $data[] =  $row;
        }

        echo json_encode([
            'total' => $total,
            'pages' => $totalPAges,
            'page' => $this->page,
            'data' => $data
        ]);
    }

    public function units()
    {
        $where = [];
        foreach ($this->filters as $key => $value) {

            if (in_array($key, ['name', 'description', 'rent']) && !empty($value)) {
                $where[$key] = [
                    'operator' => 'LIKE',
                    'value' => '%' . $value . '%'
                ];
            } else if ($key == 'status') {
                $in[] = 0;
                $unit = new Unit();
                foreach ($unit->statusStrings() as $code => $status) {
                    if (stripos($status, $value) !== false) $in[] = $code;
                }
                $where[$key] = [
                    'operator' => 'IN',
                    'value' => [
                        '(' . implode(',', $in) . ')'
                    ]
                ];
            } else if ($key == 'property' && !empty($value)) {
                $in[] = 0;
                /** @var Property[] $properties */
                $properties = Property::find();
                foreach ($properties as $property) {
                    if (stripos($property->name, $value) !== false) {
                        $in[] =  $property->property_id;
                    }
                }
                $where['property_id'] = [
                    'operator' => 'IN',
                    'value' => [
                        '(' . implode(',', $in) . ')'
                    ]
                ];
            } else if ($key == 'property_id') {
                $where['property_id'] = $value;
            }
        }

        /** @var Unit[] $collection */
        $collection = Unit::find($where);
        $collection->activePagination($this->pageLength);
        $collection->paginate($this->page);
        $total = $collection->queryFoundModels();
        $totalPAges = $collection->getTotalPages();

        $data = [];
        foreach ($collection as $row) {
            $row->property = $row->getProperty()->name;
            $row->property_id = $row->getProperty()->property_id;
            $data[] =  $row;
        }

        echo json_encode([
            'total' => $total,
            'pages' => $totalPAges,
            'page' => $this->page,
            'data' => $data
        ]);
    }

    public function documents()
    {
        $where = [];
        foreach ($this->filters as $key => $value) {
            if (in_array($key, ['name', 'created']) && !empty($value)) {
                $where[$key] = [
                    'operator' => 'LIKE',
                    'value' => '%' . $value . '%'
                ];
            } else if ($key == 'user' && !empty($value)) {
                $in[] = 0;
                /** @var User[] $users */
                $users = User::find(['admin' => 1]);
                foreach ($users as $user) {
                    $name = trim($user->first_name) . ' ' . trim($user->last_name);
                    if (stripos($name, $value) !== false) {
                        $in[] = $user->user_id;
                    }
                }
                $where['user_id'] = [
                    'operator' => 'IN',
                    'value' => [
                        '(' . implode(',', $in) . ')'
                    ]
                ];
            } else if ($key == 'property_id') {
                $where['property_id'] = $value;
            }
        }

        /** @var Document[] $collection */
        $collection = Document::find($where);
        $collection->activePagination($this->pageLength);
        $collection->paginate($this->page);
        $total = $collection->queryFoundModels();
        $totalPAges = $collection->getTotalPages();

        $data = [];
        foreach ($collection as $row) {
            $row->user = $row->getUser()->first_name . ' ' . $row->getUser()->last_name;
            $row->owner = $row->getOwner()->first_name . ' ' . $row->getOwner()->last_name;
            $data[] = $row;
        }

        echo json_encode([
            'total' => $total,
            'pages' => $totalPAges,
            'page' => $this->page,
            'data' => $data
        ]);
    }

    public function notes()
    {
        $where = [];
        foreach ($this->filters as $key => $value) {
            if ($key == 'property_id') {
                $where['property_id'] = $value;
            }
        }

        /** @var Note[] $collection */
        $collection = Document::find($where);
        $collection->activePagination($this->pageLength);
        $collection->paginate($this->page);
        $total = $collection->queryFoundModels();
        $totalPAges = $collection->getTotalPages();

        $data = [];
        foreach ($collection as $row) {
            $row->user = $row->getUser()->first_name . ' ' . $row->getUser()->last_name;
            $data[] = $row;
        }

        echo json_encode([
            'total' => $total,
            'pages' => $totalPAges,
            'page' => $this->page,
            'data' => $data
        ]);
    }

    public function users()
    {
        $where = [];
        foreach ($this->filters as $key => $value) {
            if (in_array($key, ['first_name', 'last_name', 'email']) && !empty($value)) {
                $where[$key] = [
                    'operator' => 'LIKE',
                    'value' => '%' . $value . '%'
                ];
            } else if ($key == 'admin' && $value != '') {
                $admin = 0;
                if (stripos('yes', $value) !== false || $value == '1') {
                    $admin = 1;
                }
                $where[$key] = $admin;
            } else if ($key == 'unit' && !empty($value)) {
                // get all the units... this might be a little slow since we have to look at property too for this one
                // it might be ok since this is paginated results anyways
                $in[] = 0;
                /** @var Unit[] $units */
                $units = Unit::find();
                foreach ($units as $unit) {
                    if (stripos($unit->name, $value) !== false || stripos($unit->getProperty()->name, $value) !== false) {
                        $in[] = $unit->unit_id;
                    }
                }
                $where['unit_id'] = [
                    'operator' => 'IN',
                    'value' => [
                        '(' . implode(',', $in) . ')'
                    ]
                ];
            }

        }

        $collection = User::find($where);
        $collection->activePagination($this->pageLength);
        $collection->paginate($this->page);
        $total = $collection->queryFoundModels();
        $totalPAges = $collection->getTotalPages();

        $data = [];
        foreach ($collection as $row) {
            $row->unit = '';
            $row->property_id = 0;
            if (!empty($row->unit_id)) {
                $row->unit = $row->getUnit()->getProperty()->name . ' | ' . $row->getUnit()->name;
                $row->property_id = $row->getUnit()->getProperty()->property_id;
            }
            $data[] =  $row;
        }

        echo json_encode([
            'total' => $total,
            'pages' => $totalPAges,
            'page' => $this->page,
            'data' => $data
        ]);
    }

    public function scraperUrls()
    {
        $where = [];
        foreach ($this->filters as $key => $value) {
            if (in_array($key, ['name', 'state', 'last_scraped']) && !empty($value)) {
                $where[$key] = [
                    'operator' => 'LIKE',
                    'value' => '%' . $value . '%'
                ];
            }
        }

        $collection = ScraperUrl::find($where);
        $collection->activePagination($this->pageLength);
        $collection->paginate($this->page);
        $total = $collection->queryFoundModels();
        $totalPAges = $collection->getTotalPages();

        $data = [];
        foreach ($collection as $row) {
            $data[] =  $row;
        }

        echo json_encode([
            'total' => $total,
            'pages' => $totalPAges,
            'page' => $this->page,
            'data' => $data
        ]);
    }

    public function scraperLeads()
    {
        $where = [];
        foreach ($this->filters as $key => $value) {
            if ($key == 'search' && !empty($value)) {
                $where['name'] = [
                    'operator' => 'LIKE',
                    'value' => '%' . $value . '%'
                ];
            } else if ($key == 'url' && !empty($value)) {
                $where['url_id'] = intval($value);
            }
        }

        $collection = ScraperLead::find($where, ['active' => 'DESC']);
        $collection->activePagination($this->pageLength);
        $collection->paginate($this->page);
        $total = $collection->queryFoundModels();
        $totalPAges = $collection->getTotalPages();

        $data = [];
        foreach ($collection as $row) {
            $data[] =  $row;
        }

        echo json_encode([
            'total' => $total,
            'pages' => $totalPAges,
            'page' => $this->page,
            'data' => $data
        ]);
    }

}