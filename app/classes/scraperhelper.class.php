<?php

class ScraperHelper
{
    public static function getPdfString($url)
    {
        $client = new GuzzleHttp\Client();

        $tmpPdfPath = ROOT . DS . 'app' . DS . 'files' . DS . 'tmp' . DS . 'tmppdf' . time() . '.pdf';

        $request = $client->request('GET', $url, [
            'referer' => true,
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36 Edg/109.0.1518.70',
            ],
        ]);
        $contents = (string)$request->getBody();

        if ($contents === false || empty($contents)) return false;
        $result = @file_put_contents($tmpPdfPath, $contents);
        if ($result === false) return false;

        $tmpImgFilePath = ROOT . DS . 'app' . DS . 'files' . DS . 'tmp' . DS . 'tmptxt' . time();

        $command = 'pdftoppm -jpeg ' . $tmpPdfPath . ' ' . $tmpImgFilePath;
        exec($command);

        if (file_exists($tmpImgFilePath . '.jpg')) {

            $tmpImgFilePath .= '.jpg';

            // only one files exists. we don't need to run the loop
            // now we should have an image file which we can use tesseract-ocr to pull a string out of
            $text = (new thiagoalessio\TesseractOCR\TesseractOCR($tmpImgFilePath))
                ->run();

            unlink($tmpImgFilePath);

        } else {

            $text = '';

            // pdftoppm creates multiple files appended with a number on the end for multiple page pdfs
            // filename-1, filename-2, etc
            for($i = 1; $i <= 3; $i++) {

                $tmpImagePath2 = $tmpImgFilePath . '-' . $i . '.jpg';
                if (file_exists($tmpImagePath2)) {
                    $text .= (new thiagoalessio\TesseractOCR\TesseractOCR($tmpImagePath2))
                        ->run();
                    unlink($tmpImagePath2);
                }

            }

        }

        unlink($tmpPdfPath);

        return $text;
    }

    public static function pullAddressesFromString($string)
    {
        $string = strip_tags($string);
        $string = preg_replace("/&nbsp;/"," ",$string);

//    $tmpMatches = [];
        preg_match_all("/[0-9]{2,10}+\s+[^0-9]{0,50}(wi|ia)+\s+[0-9]{5}/is", $string, $matches);

//    foreach ($tmpMatches[0] as $tmpMatch) {
//        // find the string in the full string and make sure the address doesn't start with a letter. Example N1430 S Something Road.
////        $matches[] = $tmpMatch;
////        $pos = stripos($string, $tmpMatch);
//        $sub = substr($string, stripos($string, $tmpMatch), strlen($tmpMatch));
//
//    }

        return (!empty($matches[0])) ? $matches[0] : [];
    }

    public static function parseAddressPartsFromGoogle($addresses)
    {
        $return = [];

        if (is_string($addresses)) {

            $return[] = googleGeoAndParse($addresses);

        } else if (is_array($addresses)) {

            // this will take the last good address found and return ony one set of parts
            foreach ($addresses as $address) {

                $tmpParts = googleGeoAndParse($address);
                if (!empty($tmpParts['streetNumber'])) {
                    $return[] = $tmpParts;
                }

            }

        }

        return $return;
    }

    public static function removeQuarantinedAddressFromArray($addresses)
    {
        $returnAddresses = [];

        foreach ($addresses as $address) {

            $db = new StandardQuery();
            $sql = 'SELECT address_id FROM quarantine_addresses WHERE street = :street AND city = :city AND state = :state ';
            $params = [
                'street' => $address['streetNumber'] . ' ' . $address['streetName'],
                'city' => $address['city'],
                'state' => $address['state']
            ];

            if (!$db->count($sql, $params)) {
                $returnAddresses[] = $address;
            }

        }

        return $returnAddresses;
    }

}
