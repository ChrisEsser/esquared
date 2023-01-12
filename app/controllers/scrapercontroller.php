<?php

use thiagoalessio\TesseractOCR\TesseractOCR;

class ScraperController extends BaseController
{
    public function beforeAction($params)
    {
        if (!Auth::isAdmin() && $this->_action != 'scrapeBackground') {
            HTTP::redirect('/login');
        }
    }

    public function scraper($params)
    {
        /** @var \ScraperUrl[] $urls */
        $urls = ScraperUrl::find([], ['name' => 'ASC']);
        $this->view->setVar('urls', $urls);
    }

    public function scrape($params)
    {
        $this->render = false;

        $urlId = $params['urlId'] ?? 0;

        if ($urlId) {

            /** @var \ScraperUrl $url */
            $url = ScraperUrl::findOne(['url_id' => $urlId]);

            if (!$url->url_id) HTTP::redirect('/scraper');

            ScraperHelper::runScrape($url);

        } else {

            ScraperHelper::runScrapeAll();

        }

        HTTP::redirect('/scraper');
    }

    public function scrapeBackground()
    {
        $this->render = false;

        if (isset($_GET['key']) && $_GET['key'] == 'hdyetbx84648732mcjd') {
            ScraperHelper::runScrapeAll();
        }

        echo 'done';
    }

    public function saveScraper()
    {
        $this->render = false;

        $return = [
            'result' => 'success',
            'message' => '',
        ];

        $missing = [];

        try {

            $urlId = ($_POST['url_id']) ?? 0;

            /** @var \ScraperUrl $url */
            $url = ($urlId)
                ? ScraperUrl::findOne(['url_id' => $urlId])
                : new ScraperUrl();

            if (empty($_POST['name'])) $missing[] = 'name';
            if (empty($_POST['url'])) $missing[] = 'url';

            if (!empty($missing)) throw new Exception('Some required fields were missing');

            $url->name = $_POST['name'];
            $url->url = $_POST['url'];
            $url->depth = intval($_POST['depth'])-1;
            $url->search_string = base64_encode(serialize($_POST['search_string']));
            $url->state = $_POST['state'];
            if (empty($url->last_scraped)) $url->last_scraped = gmdate('Y-m-d H:i:s');
            $url->save();

        } catch (Exception $e) {
            $return = [
                'result' => 'error',
                'message' => $e->getMessage(),
            ];
        }

        echo json_encode($return);
    }

    public function editScraper($params)
    {
        HTTP::removePageFromHistory();
        $this->render_header = false;

        $urlId = ($params['urlId']) ?? 0;

        /** @var \ScraperUrl $lead */
        $url = ($urlId)
            ? ScraperUrl::findOne(['url_id' => $urlId])
            : new ScraperUrl();

        $this->view->setVar('url', $url);
    }

    public function deleteScraper($params)
    {
        $this->render = false;

        $urlId = $params['urlId'] ?? 0;

        $url = ScraperUrl::findOne(['url_id' => $urlId]);
        $url->delete();

        HTTP::rewindQuick();
    }

    public function leads($params)
    {
        $urlId = $params['urlId'] ?? 0;

        if ($urlId) {
            $url = ScraperUrl::findOne(['url_id' => $urlId]);
            $viewAll = false;
        } else {
            $url = new ScraperUrl();
            $viewAll = true;
        }

        $order = ['flagged' => 'DESC', 'active' => 'DESC', 'created' => 'DESC'];
        $leads = ($url->url_id)
            ? ScraperLead::find(['url_id' => $urlId], $order)
            : ScraperLead::find([], $order);

        $this->view->setVar('url', $url);
        $this->view->setVar('leads', $leads);
        $this->view->setVar('viewAll', $viewAll);
    }

    public function lead($params)
    {
        $leadId = $params['leadId'] ?? 0;

        /** @var \ScraperLead $lead */
        $lead = ScraperLead::findOne(['lead_id' => $leadId]);

        if (empty($lead->lead_id)) throw new Exception404();

        $this->view->setVar('lead', $lead);
    }

    public function toggleLeadActive($params)
    {
        $this->render = false;

        $return = [
            'result' => 'success',
            'message' => '',
        ];

        try {

            $leadId = $params['leadId'] ?? 0;

            /** @var \ScraperLead $lead */
            $lead = ScraperLead::findOne(['lead_id' => $leadId]);

            $lead->active = intval($params['active']);
            $lead->save();

        } catch (Exception $e) {
            $return = [
                'result' => 'error',
                'message' => $e->getMessage(),
            ];
        }

        echo json_encode($return);
        exit;
    }

    public function toggleLeadFlagged($params)
    {
        $this->render = false;

        $return = [
            'result' => 'success',
            'message' => '',
        ];

        try {

            $leadId = $params['leadId'] ?? 0;

            /** @var \ScraperLead $lead */
            $lead = ScraperLead::findOne(['lead_id' => $leadId]);
            $lead->flagged = intval($params['flagged']);

            $lead->save();

        } catch (Exception $e) {
            $return = [
                'result' => 'error',
                'message' => $e->getMessage(),
            ];
        }

        echo json_encode($return);
        exit;
    }

    public function editLead($params)
    {
        HTTP::removePageFromHistory();
        $this->render_header = false;

        $leadId = ($params['leadId']) ?? 0;
        $lead = ScraperLead::findOne(['lead_id' => $leadId]);

        if (!$lead) throw new Exception404();

        $this->view->setVar('lead', $lead);
    }

    public function saveLead()
    {
        $this->render = false;

        $return = [
            'result' => 'success',
            'message' => '',
        ];

        $missing = [];

        try {

            $leadId = ($_POST['lead']) ?? 0;

            /** @var \ScraperLead $lead */
            $lead = ScraperLead::findOne(['lead_id' => $leadId]);

            if (!$lead->lead_id) throw new Exception('Invalid Lead');

            $missing = [];
            if (empty($_POST['street'])) $missing[] = 'street';
            if (empty($_POST['city'])) $missing[] = 'city';
            if (empty($_POST['state'])) $missing[] = 'state';
            if (empty($_POST['zip'])) $missing[] = 'zip';

            if (!empty($missing)) throw new Exception('Some required fields were missing');

            $address = $_POST['street'] . ', ' . $_POST['city'] . ', ' . $_POST['state'];
            $address = urlencode($address);
            $url = 'https://maps.googleapis.com/maps/api/geocode/json?address=' . $address . '&key=' . $_ENV['GOOGLE_MAPS_API_KEY'];
            $resp_json = file_get_contents($url);
            $resp = json_decode($resp_json, true);

            if ($resp['status'] == 'OK') {

                // get the important data
                $lat = isset($resp['results'][0]['geometry']['location']['lat']) ? $resp['results'][0]['geometry']['location']['lat'] : "";
                $lon = isset($resp['results'][0]['geometry']['location']['lng']) ? $resp['results'][0]['geometry']['location']['lng'] : "";

                $lead->lat = $lat;
                $lead->lon = $lon;

            }

            $lead->street = $_POST['street'];
            $lead->city = $_POST['city'];
            $lead->state = $_POST['state'];
            $lead->zip = $_POST['zip'];
            $lead->save();

        } catch (Exception $e) {
            $return = [
                'result' => 'error',
                'message' => $e->getMessage(),
            ];
        }

        echo json_encode($return);
    }

    public function deleteLead($params)
    {
        $this->render = false;

        $leadId = ($params['leadId']) ?? 0;

        $lead = ScraperLead::findOne(['lead_id' => $leadId]);
        $lead->delete();

        HTTP::rewindQuick();
    }

    public function leadStreetView($params)
    {
        $this->render_header = false;

        $leadId = ($params['leadId']) ?? 0;
        /** @var \ScraperLead $lead */
        $lead = ScraperLead::findOne(['lead_id' => $leadId]);

        if (!$lead->lead_id) throw new Exception404();

        $this->view->setVar('lead', $lead);
    }

    public function ocdTest()
    {
        $this->render = false;
//
//        $image = ROOT . DS . 'public' . DS . 'images' . 'Callout.png';
//
//        echo (new TesseractOCR($image))
//            ->run();

    }

    public function afterAction()
    {
        if (!$this->render_header) {
            $layout = new AjaxLayout();
            $layout->action = $this->_action;
            $layout->addTemplate($this->view);
            $layout->display();
        }
        else if ($this->render) {
            $layout = new AdminLayout();
            $layout->action = $this->_action;
            $layout->addTemplate($this->view);
            $layout->display();
        }
    }
}