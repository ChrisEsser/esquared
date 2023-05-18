<?php

class ScraperHelper
{

    /**
     * @TODO: update so the scrape can scape all urls
     *
     * @param $url \ScraperUrl
     * @return bool
     */
    public static function runScrape($url)
    {
        try {

            if (empty($url->url_id)) throw new Exception();

             libxml_use_internal_errors(true);

            $filters = unserialize(base64_decode($url->search_string));

            $client = new GuzzleHttp\Client();

            $leads = self::recursiveCrawl($url->url, 0, $url->depth, $filters, $client);
            $leadHrefs = $newLeads = [];

            foreach ($leads as $href) {

                $leadHrefs[] = $href;

                // we need to check if this link has already been scraped. chances are it has.
                $lead = ScraperLead::findOne(['url_id' => $url->url_id, 'url' => $href]);

                if ($lead) {
                    $lead->last_seen = gmdate('Y-m-d H:i:s', time());
                    $lead->save();
                    continue;
                }

                // let's try to convert the pdf to plain string to pull out info
                $plainSTring = self::getPdfString($href);
                $address = self::pullAddressFromString($plainSTring);
                $dollarAmount = self::pullDollarAmountFromString($plainSTring);

                // call google maps to get the address information
                $address = urlencode($address);
                $apiUrl = 'https://maps.googleapis.com/maps/api/geocode/json?address=' . $address . '&key=' . $_ENV['GOOGLE_MAPS_SERVER_KEY'];
                $result = file_get_contents($apiUrl);
                $result = json_decode($result, true);

                $streetNumber = $streetName = $city = $state = $zip = $lat = $lon = '';

                if (isset($result['status']) && $result['status'] = 'OK') {

                    $parts = $result['results'][0]['address_components'];
                    $geo = $result['results'][0]['geometry']['location'];

                    if ($geo) {
                        $lat = $geo['lat'];
                        $lon = $geo['lng'];
                    }

                    foreach ($parts as $key => $part) {
                        switch($part['types'][0]) {
                            case 'street_number':
                                $streetNumber =  $part['long_name'];
                                break;
                            case 'route':
                                $streetName = $part['short_name'];
                                break;
                            case 'locality':
                                $city = $part['long_name'];
                                break;
                            case 'administrative_area_level_1':
                                $state = $part['short_name'];
                                break;
                            case 'postal_code':
                                $zip = $part['short_name'];
                            default:
                                break;
                        }
                    }

                }

                $lead = new ScraperLead();
                $lead->url_id = $url->url_id;
                $lead->url = $href;
                $lead->last_seen = gmdate('Y-m-d H:i:s', time());
                $lead->active = 1;
                $lead->flagged = 0;
                $lead->judgment_amount = floatval($dollarAmount);
                $lead->lat = $lat;
                $lead->lon = $lon;
                $lead->street = $streetNumber . ' ' . $streetName;
                $lead->city = $city;
                $lead->state = $state;
                $lead->zip = $zip;
                $lead->save();
                $newLeads[] = $lead;

            }

            ScraperLead::find([
                'url' => [
                    'operator' => 'NOT IN',
                    'value' => [
                        '(\'' . implode('\', \'', $leadHrefs) . '\')'
                    ]
                ],
                'url_id' => $url->url_id
            ])->delete();

            $url->last_scraped = gmdate('Y-m-d H:i:s', time());
            $url->leads_count = count($leads);
            $url->save();

            // send notification email if there are new leads
            if (!empty($newLeads)) {

                $mailer = new Mailer();
                $mailer->subject = 'E Squared Holdings | Scraper Notification';
                $mailer->to = ['chris@esquaredholdings.com', 'cody@esquaredholdings.com'];

                $linkHref = $_ENV['BASE_PATH'] . '/scraper/' . $url->url_id . '/leads';

                $body = '<p>New leads were detected for the following scraper url: </p>';
                $body .= '<p><a href="' . $linkHref . '">' . $url->name . '</a></p>';
                $mailer->html = $body;

                if (!$mailer->send()) {
                    HTML::addAlert('error sending scraper notification email. ERROR MESSAGE: ' . $mailer->error, 'danger');
                }

            }

            HTML::addAlert('Scrape successful', 'success');

        } catch (Exception $e) {

            Debug::dump($e->getMessage());
            die;

            return false;
        }

        return true;
    }

    public static function runScrapeAll()
    {
        /** @var \ScraperUrl[] $urls */
        $urls = ScraperUrl::find();

        if (count($urls)) {
            foreach ($urls as $url) {
                self::runScrape($url);
            }
        }

    }

    private static function recursiveCrawl($url, $currentLevel, $totalLevels, $filters, $client)
    {
        $request = $client->request('GET', $url);
        $html = (string)$request->getBody();

        $dom = new DOMDocument();
        $dom->loadHTML($html);

        $leads = [];

        $thisFilters = explode(',', ($filters[$currentLevel]) ?? []);

        foreach ($dom->getElementsByTagName('a') as $e) {

            $href = $e->getAttribute('href');

            // dont save if there is not text. One site has empty links. probably pre set in a CMS editor or something.
            if (empty(trim($e->textContent))) continue;

            // check the filters for this level.
            $goodLink = true;

            foreach ($thisFilters as $thisFilter) {
                $thisFilter = trim($thisFilter);
                if (str_starts_with($thisFilter, '!!')) {
                    if (stripos($href, substr($thisFilter, 2)) !== false) {
                        $goodLink = false;
                        break;
                    }
                } else {
                    if (stripos($href, $thisFilter) === false) {
                        $goodLink = false;
                        break;
                    }
                }
            }

            if (!$goodLink) continue;

            // add the protocol and host if needed
            if (stripos($href, 'http') === false) {

                $parse = parse_url($url);

                if (str_starts_with($href, '//')) {
                    // do nothing.. we should be able to navigate
                    $href = $parse['scheme'] . '://' . substr($href, 2);
                } else {
                    if (str_starts_with($href, '/')) {
                        $href = $parse['scheme'] . '://' . $parse['host'] . $href;
                    }
                    else {
                        $href = $parse['scheme'] . '://' . $parse['host'] . '/' . $href;
                    }
                }
            }

            if ($currentLevel < $totalLevels) {

                $tmpLeads = self::recursiveCrawl($href, $currentLevel+1, $totalLevels, $filters, $client);
                $leads = array_merge($tmpLeads, $leads);

            } else {

                $leads[] = $href;

            }
        }

        return $leads;
    }

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

    private static function pullAddressFromString($string)
    {
        $starts = stripos($string,'Address: ') + strlen('Address: ');
        $ends = stripos($string, "\n", $starts);
        $address = substr($string, $starts, $ends - $starts);

        return $address;
    }

    private static function pullDollarAmountFromString($string)
    {
        preg_match('/\$([0-9]+[\.,0-9]*)/', $string, $m);
        $dollar = $m[1];
        $dollar = str_replace(',', '', $dollar);

        return $dollar;
    }

}
