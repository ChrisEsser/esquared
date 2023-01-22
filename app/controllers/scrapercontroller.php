<?php

use thiagoalessio\TesseractOCR\TesseractOCR;

class ScraperController extends BaseController
{
    public function beforeAction($params)
    {
        if (!Auth::isAdmin()) {
            HTTP::redirect('/login');
        }
    }

    public function scraper($params)
    {

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
            $url->dom_target = $_POST['dom_target'];
            $url->doc_type = $_POST['doc_type'];
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

        $this->view->setVar('url', $url);
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

        try {

            $leadId = ($_POST['lead']) ?? 0;

            /** @var \ScraperLead $lead */
            $lead = ScraperLead::findOne(['lead_id' => $leadId]);

            if (!$lead->lead_id) throw new Exception('Invalid Lead');

            $lead->judgment_amount = floatval($_POST['judgment_amount'] ?? 0);
            $lead->save();

        } catch (Exception $e) {
            $return = [
                'result' => 'error',
                'message' => $e->getMessage(),
            ];
        }

        echo json_encode($return);
    }

    public function editAddress($params)
    {
        HTTP::removePageFromHistory();
        $this->render_header = false;

        $addressId = $params['addressId'] ?? 0;
        $leadId = $params['leadId'] ?? 0;

        $address = ($addressId)
            ? ScraperLeadAddress::findOne(['address_id' => $addressId])
            : new ScraperLeadAddress();

        if ($address->lead_id) $leadId = $address->lead_id;

        if (!$address) throw new Exception404();

        $this->view->setVar('address', $address);
        $this->view->setVar('leadId', $leadId);
    }

    public function saveAddress()
    {
        $this->render = false;

        $return = [
            'result' => 'success',
            'message' => '',
        ];

        try {

            $missing = [];
            if (empty($_POST['street'])) $missing[] = 'street';
            if (empty($_POST['city'])) $missing[] = 'city';
            if (empty($_POST['state'])) $missing[] = 'state';
            if (empty($_POST['zip'])) $missing[] = 'zip';

            if (!empty($missing)) throw new Exception('Some required fields were missing');

            $addressId = $_POST['address'] ?? 0;
            $leadId = $_POST['lead'] ?? 0;

            $address = ($addressId)
                ? ScraperLeadAddress::findOne(['address_id' => $addressId])
                : new ScraperLeadAddress();

            if (!empty($address->lead_id)) $leadId = $address->lead_id;

            $address->lead_id = $leadId;
            $address->street = $_POST['street'];
            $address->city = $_POST['city'];
            $address->state = $_POST['state'];
            $address->zip = $_POST['zip'];

            $addressString = $_POST['street'] . ', ' . $_POST['city'] . ', ' . $_POST['state'];
            $addressString = urlencode($addressString);
            $url = 'https://maps.googleapis.com/maps/api/geocode/json?address=' . $addressString . '&key=' . $_ENV['GOOGLE_MAPS_API_KEY'];
            $resp_json = @file_get_contents($url);
            $resp = json_decode($resp_json, true);

            if ($resp['status'] == 'OK') {

                $lat = isset($resp['results'][0]['geometry']['location']['lat']) ? $resp['results'][0]['geometry']['location']['lat'] : "";
                $lon = isset($resp['results'][0]['geometry']['location']['lng']) ? $resp['results'][0]['geometry']['location']['lng'] : "";

                $address->lat = $lat;
                $address->lon = $lon;

            }

            $address->save();

        } catch (Exception $e) {
            $return = [
                'result' => 'error',
                'message' => $e->getMessage(),
            ];
        }

        echo json_encode($return);
    }

    public function quarantineAddress($params)
    {
        HTTP::removePageFromHistory();
        $this->render = false;

        $addressId = $params['addressId'] ?? 0;
        $address = ScraperLeadAddress::findOne(['address_id' => $addressId]);

        if (!$address) throw new Exception404();

        $qAddress = new ScraperQuarantineAddress();
        $qAddress->street = $address->street;
        $qAddress->city = $address->city;
        $qAddress->state = $address->state;
        $qAddress->zip = $address->zip;
        $qAddress->lon = $address->lon;
        $qAddress->lat = $address->lat;
        $qAddress->save();

        ScraperLeadAddress::find([
            'street' => $address->street,
            'city' => $address->city,
            'state' => $address->state
        ])->delete();

        HTTP::rewindQuick();
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
        HTTP::removePageFromHistory();
        $this->render_header = false;

        $addressId = ($params['addressId']) ?? 0;
        /** @var \ScraperLeadAddress $address */
        $address = ScraperLeadAddress::findOne(['address_id' => $addressId]);

        if (!$address->address_id) throw new Exception404();

        $this->view->setVar('address', $address);
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