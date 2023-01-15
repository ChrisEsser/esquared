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
            if ($key == 'search' && !empty($value)) {
                $where['name'] = [
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
            if ($key == 'search' && !empty($value)) {
                $where['name'] = [
                    'operator' => 'LIKE',
                    'value' => '%' . $value . '%'
                ];
            } else if ($key == 'property' && !empty($value)) {
                $where['property_id'] = intval($value);
            }
        }

        $collection = Unit::find($where);
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

    public function documents()
    {
        $where = [];
        foreach ($this->filters as $key => $value) {
            if ($key == 'search' && !empty($value)) {
                $where['name'] = [
                    'operator' => 'LIKE',
                    'value' => '%' . $value . '%'
                ];
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

    public function users()
    {
        $where = [];
        foreach ($this->filters as $key => $value) {
            if ($key == 'search' && !empty($value)) {
                $where['last_name'] = [
                    'operator' => 'LIKE',
                    'value' => '%' . $value . '%'
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
            if ($key == 'search' && !empty($value)) {
                $where['name'] = [
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

        $collection = ScraperLead::find($where);
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