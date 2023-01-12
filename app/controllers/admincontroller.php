<?php

use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Core\OAuth\OAuth2\OAuth2LoginHelper;

class AdminController extends BaseController
{
    /** @var \QuickBooks */
    public $qb;

    public function beforeAction()
    {
        if (!Auth::isAdmin()) {
            HTML::addAlert('Unauthorized access');
            HTTP::redirect('/');
        }

        $this->qb = new QuickBooks();
    }

    public function switchUser($params)
    {
        $this->render = false;
        Auth::switchUser($params['userId']);
        HTTP::redirect('/');
    }

    public function admin()
    {
        $qb = new QuickBooks();
        $accessToken = $qb->getAccessToken(1);
    }

    public function qbSetup()
    {
        $dataService = $this->qb->getDataService();

        $OAuth2LoginHelper = $dataService->getOAuth2LoginHelper();
        $authUrl = $OAuth2LoginHelper->getAuthorizationCodeURL();

        $accessToken = $this->qb->getAccessToken(1);

        $companyInfo = [];
        if ($accessToken) {
            $dataService->updateOAuth2Token($accessToken);
            $companyInfo = $dataService->getCompanyInfo();
        }

        $this->view->setVar('authUrl', $authUrl);
        $this->view->setVar('companyInfo', $companyInfo);
    }

    public function qbDisconnect()
    {
        $this->render = false;

        $qbConnection = QbConnect::findOne(['connect_id' => 1]);
        if ($qbConnection) {
            $qbConnection->delete();
        }

        HTTP::rewind();
    }

    public function qbCallback()
    {
        $this->render = false;

        $dataService = $this->qb->getDataService();

        $OAuth2LoginHelper = $dataService->getOAuth2LoginHelper();

        parse_str($_SERVER['QUERY_STRING'],$qsArray);
        $parseUrl = [
            'code' => $qsArray['code'],
            'realmId' => $qsArray['realmId']
        ];

        $accessToken = $OAuth2LoginHelper->exchangeAuthorizationCodeForToken($parseUrl['code'], $parseUrl['realmId']);
        $dataService->updateOAuth2Token($accessToken);

        /** @var \QbConnect $qbConnection */
        $qbConnection = QbConnect::findOne(['connect_id' => 1]);
        if (!$qbConnection) {
            $qbConnection = new QbConnect();
        }

        $qbConnection->connect_data = base64_encode(serialize($accessToken));
        $qbConnection->save();
    }

    public function qbRefreshToken()
    {
        $this->render = false;

        $return = [
            'result' => '',
            'message' => '',
        ];

        try {

            $accessToken = $this->qb->refreshAccessToken(1);
            if (!$accessToken) throw new Exception('Unable to refresh token');

            $return['result'] = 'success';

        } catch (Exception $e) {
            $return = [
                'result' => 'error',
                'message' => $e->getMessage(),
            ];
        }

        echo json_encode($return);
        exit;
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