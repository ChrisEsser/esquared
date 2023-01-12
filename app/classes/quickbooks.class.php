<?php

use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Core\OAuth\OAuth2\OAuth2LoginHelper;

class QuickBooks
{
    public function getDataService()
    {
        $baseUrl = ($_ENV['DEVELOPMENT_ENVIRONMENT'] == 'true') ? 'development' : 'production';

        return DataService::Configure([
            'auth_mode' => 'oauth2',
            'ClientID' => $_ENV['QB_client_id'],
            'ClientSecret' => $_ENV['QB_client_secret'],
            'RedirectURI' => $_ENV['QB_oauth_redirect_uri'],
            'scope' => $_ENV['QB_oauth_scope'],
            'baseUrl' => $baseUrl
        ]);
    }

    public function convertFromRawDbData($string)
    {
        return unserialize(base64_decode($string));
    }

    public function convertToRawDbData($obj)
    {
        return base64_encode(serialize($obj));
    }

    public function getAccessToken($connectId)
    {
        $qbConnection = QbConnect::findOne(['connect_id' => $connectId]);
        if (!$qbConnection) {
            return false;
        }

        session_reset();

        $accessToken = $this->convertFromRawDbData($qbConnection->connect_data);

        if (time() > strtotime($accessToken->getAccessTokenExpiresAt())) {

            if (time() < strtotime($accessToken->getRefreshTokenExpiresAt())) {

                $accessToken = $this->refreshAccessToken($connectId);

            } else {

                return false;
            }
        }

        return $accessToken;
    }

    public function refreshAccessToken($connectId)
    {
        /** @var \QbConnect $qbConnection */
         $qbConnection = QbConnect::findOne(['connect_id' => 1]);
         if (!$qbConnection) {
            return false;
        }

        session_reset();

        $accessToken = $this->convertFromRawDbData($qbConnection->connect_data);

        $dataService = DataService::Configure([
            'auth_mode' => 'oauth2',
            'ClientID' => $_ENV['QB_client_id'],
            'ClientSecret' => $_ENV['QB_client_secret'],
            'RedirectURI' => $_ENV['QB_oauth_redirect_uri'],
            'baseUrl' => 'development',
            'refreshTokenKey' => $accessToken->getRefreshToken(),
            'QBORealmID' => "The Company ID which the app wants to access",
        ]);

        $OAuth2LoginHelper = $dataService->getOAuth2LoginHelper();
        $refreshedAccessTokenObj = $OAuth2LoginHelper->refreshToken();
        $dataService->updateOAuth2Token($refreshedAccessTokenObj);

        $qbConnection->connect_data = $this->convertToRawDbData($refreshedAccessTokenObj);
        $qbConnection->save();

        return $refreshedAccessTokenObj;
    }

}