<?php

class QuickBooksAPIController extends BaseController
{

    public $qb;

    public function beforeAction()
    {
        if (!Auth::isAdmin()) {
            HTML::addAlert('Unauthorized access');
            HTTP::redirect('/');
        }

        $this->qb = new QuickBooks();
        $this->render = false;
    }

    public function companyInfo()
    {
        try {

            $dataService = $this->qb->getDataService();

            session_reset();

            $accessToken = $this->qb->getAccessToken(1);
            $dataService->updateOAuth2Token($accessToken);

            $companyInfo = $dataService->getCompanyInfo();
            echo json_encode($companyInfo);
            exit;

        } catch (\QuickBooksOnline\API\Exception\SdkException $e) {
            print_r($e->getMessage());
        }
    }

    public function accounts()
    {
        try {

             $dataService = $this->qb->getDataService();

             session_reset();

             $accessToken = $this->qb->getAccessToken(1);
             $dataService->updateOAuth2Token($accessToken);

             $accounts = $dataService->FindAll('account');
             echo json_encode($accounts);
             exit;

        } catch (\QuickBooksOnline\API\Exception\SdkException $e) {
             print_r($e->getMessage());
        }
    }

}