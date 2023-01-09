<?php

class UID
{

    private static $set = '0123456789abcdefghijklmnopqrstuvwxyz';
    private static $length = 10;

    public static function generate($model)
    {
        $unique = false;
        while (!$unique) {
            $uid = self::generateRandomString();
            $unique = self::checkUnique($model, $uid);
        }

        return $uid;
    }

    private static function generateRandomString()
    {
        $setLength = strlen(self::$set);
        $string = '';
        for($i = 0; $i < self::$length; $i++) {
            $string .= self::$set[mt_rand(0, $setLength - 1)];
        }

        return $string;
    }

    private static function checkUnique($model, $uid)
    {
        $unique = false;
        $tmpRecord = $model::findOne(['uid' => $uid]);
        if (empty($tmpRecord->uid)) $unique = true;
        return $unique;
    }

}