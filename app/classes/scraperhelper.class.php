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
                $apiUrl = 'https://maps.googleapis.com/maps/api/geocode/json?address=' . $address . '&key=' . $_ENV['GOOGLE_MAPS_API_KEY'];
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

    private static function getPdfString($url)
    {
        // go get the pdf and save it to a temporary file

        $tmpPdfPath = ROOT . DS . 'app' . DS . 'files' . DS . 'tmp' . DS . 'tmppdf' . time() . '.pdf';

        if (!$result = file_put_contents($tmpPdfPath, file_get_contents($url))) {
            return false;
        }

        $tmpImgFilePath = ROOT . DS . 'app' . DS . 'files' . DS . 'tmp' . DS . 'tmptxt' . time();

        $command = 'pdftoppm -jpeg ' . $tmpPdfPath . ' ' . $tmpImgFilePath;
        exec($command);

        // pdftoppm appends -1.jpg for some reason
        $tmpImgFilePath .= '-1.jpg';

        // now we should have an image file which we can use tesseract-ocr to pull a string out of
        $text = (new thiagoalessio\TesseractOCR\TesseractOCR($tmpImgFilePath))
            ->run();

        unlink($tmpPdfPath);
        unlink($tmpImgFilePath);

        return $text;
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
