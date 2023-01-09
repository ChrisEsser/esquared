<?php

class Alerts
{

    private $userId;

    public function __construct()
    {
        $this->userId = Auth::loggedInUser();
    }

    public function getAlerts()
    {

    }

}
