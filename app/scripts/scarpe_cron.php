<?php

/**
 * This script is meant to run as a cron job or as a background task
 * It is not on the main framework
 * as such needs to be a procedural php script
 */

ini_set('max_execution_time', 0);
set_time_limit(0);

define('ROOT', dirname(dirname(dirname(__FILE__))));
define('DS', DIRECTORY_SEPARATOR);

require ROOT . DS . 'vendor' . DS . 'autoload.php';

use thiagoalessio\TesseractOCR\TesseractOCR;


// load environment variables from the .env file
$dotenv = Dotenv\Dotenv::createImmutable(ROOT);
$dotenv->load();

try {

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    $db = mysqli_connect($_ENV['DB_HOST'], $_ENV['DB_USER'], $_ENV['DB_PASSWORD'], $_ENV['DB_NAME']);

    $result = mysqli_query($db, 'select * from scraper_urls');


    while ($row = mysqli_fetch_assoc($result)) {

        $filters = unserialize(base64_decode($row['search_string']));

        libxml_use_internal_errors(true);

        $client = new GuzzleHttp\Client();

        $leads = recursiveCrawl($row['url'], 0, $row['depth'], $filters, $client);

        $leadHrefs = [];

        foreach ($leads as $href) {

            $leadHrefs[] = $href;

            $sql = 'select * from scraper_leads where url_id = ? and url = ?'; // SQL with parameters
            $stmt = $db->prepare($sql);
            $stmt->bind_param('is', $row['url_id'], $href);
            $stmt->execute();
            $result = $stmt->get_result(); // get the mysqli result
            $lead = $result->fetch_assoc(); // fetch data

            if (!empty($lead)) {

                $sql = 'update scraper_leads set last_seen = ? where lead_id = ?';
                $stmt = $db->prepare($sql);
                $date = gmdate('Y-m-d H:i:s', time());
                $stmt->bind_param('si', $date, $lead['lead_id']);
                $stmt->execute();
                continue;

            }

            $plainSTring = getPdfString($href);
            $dollarAmount = pullDollarAmountFromString($plainSTring);
            $address = pullAddressFromString($plainSTring);
            $addressParts = parseAddressPartsFromGoogle($address);


            $sql = 'insert into scraper_leads (url_id, url, last_seen, active, flagged, judgment_amount, lat, lon, 
                                               street, city, state, zip)
                    values (?, ?, ?, 1, 0, ?, ?, ?, ?, ?, ?, ?)';
            $stmt = $db->prepare($sql);
            $date = gmdate('Y-m-d H:i:s', time());
            $dollarAmount = floatval($dollarAmount);
            $street = $addressParts['streetNumber'] . ' ' . $addressParts['streetName'];
            $stmt->bind_param(
                'ssssssssss',
                $row['url_id'],
                $href,
                $date,
                $dollarAmount,
                $addressParts['lat'],
                $addressParts['lon'],
                $street,
                $addressParts['city'],
                $addressParts['state'],
                $addressParts['zip']
            );
            $stmt->execute();

        }

        $sql = 'delete from scraper_leads where url_id = ? and url not in (\'' . implode('\', \'', $leadHrefs) . '\')';
        $stmt = $db->prepare($sql);
//        $notInList = implode('\', \'', $leadHrefs);
        $stmt->bind_param('i', $row['url_id']);
        $stmt->execute();

        $sql = 'update scraper_urls set last_scraped = ?, leads_count = ? where url_id = ?';
        $stmt = $db->prepare($sql);
        $date = $date = gmdate('Y-m-d H:i:s', time());
        $count = count($leads);
        $stmt->bind_param('sii', $date, $count, $row['url_id']);
        $stmt->execute();

    }

    echo 'done';

} catch (Exception $e) {

    echo 'Scraper Cron Error | Msg: ' . $e->getMessage();

}

mysqli_close($db);

/**
 * Recursively Crawl the url using settings set up in the app
 *
 * @param $url
 * @param $currentLevel
 * @param $totalLevels
 * @param $filters
 * @param $client
 * @return array
 */
function recursiveCrawl($url, $currentLevel, $totalLevels, $filters, $client)
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
            $tmpLeads = recursiveCrawl($href, $currentLevel+1, $totalLevels, $filters, $client);
            $leads = array_merge($tmpLeads, $leads);
        } else {
            $leads[] = $href;
        }

    }

    return $leads;
}

function getPdfString($url)
{
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

function pullAddressFromString($string)
{
    $starts = stripos($string,'Address: ') + strlen('Address: ');
    $ends = stripos($string, "\n", $starts);
    $address = substr($string, $starts, $ends - $starts);

    return $address;
}

function pullDollarAmountFromString($string)
{
    $dollar = 0;
    preg_match('/\$([0-9]+[\.,0-9]*)/', $string, $m);
    if (!empty($m[1])) {
        $dollar = $m[1];
        $dollar = str_replace(',', '', $dollar);
    }

    return $dollar;
}

function parseAddressPartsFromGoogle($address)
{
    // call google maps to get the address information
    $address = urlencode($address);
    $apiUrl = 'https://maps.googleapis.com/maps/api/geocode/json?address=' . $address . '&key=' . $_ENV['GOOGLE_MAPS_API_KEY'];
    $result = file_get_contents($apiUrl);
    $result = json_decode($result, true);

    $returnParts = [
        'streetNumber' => '',
        'streetName' => '',
        'city' => '',
        'state' => '',
        'zip' => '',
        'lat' => '',
        'lon' => '',
    ];

    if (isset($result['status']) && $result['status'] = 'OK') {

        $parts = $result['results'][0]['address_components'];
        $geo = $result['results'][0]['geometry']['location'];

        if ($geo) {
            $returnParts['lat'] = $geo['lat'];
            $returnParts['lon'] = $geo['lng'];
        }

        foreach ($parts as $key => $part) {
            switch($part['types'][0]) {
                case 'street_number':
                    $returnParts['streetNumber'] =  $part['long_name'];
                    break;
                case 'route':
                    $returnParts['streetName'] = $part['short_name'];
                    break;
                case 'locality':
                    $returnParts['city'] = $part['long_name'];
                    break;
                case 'administrative_area_level_1':
                    $returnParts['state'] = $part['short_name'];
                    break;
                case 'postal_code':
                    $returnParts['zip'] = $part['short_name'];
                default:
                    break;
            }
        }
    }

    return $returnParts;

}



