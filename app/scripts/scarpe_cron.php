<?php

/**
 * This script is meant to run as a cron job or as a background task
 * It is not on the main framework
 * as such needs to be a procedural php script
 */

session_start();

ini_set('max_execution_time', 0);
set_time_limit(0);

define('ROOT', dirname(dirname(dirname(__FILE__))));
define('DS', DIRECTORY_SEPARATOR);

spl_autoload_register(function($className) {

    $tmpClassName = $className;
    $className = strtolower($className);

    $classPaths = [
        'framePath' => ROOT . DS . 'framework' . DS . 'classes' . DS . $className . '.class.php',
        'appClassesPath' => ROOT . DS . 'app' . DS . 'classes' . DS . $className . '.class.php',
        'appInterfacePath' => ROOT . DS . 'app' . DS . 'interfaces' . DS . $className . '.interface.php',
        'controllersPath' => ROOT . DS . 'app' . DS . 'controllers' . DS . $className . '.php',
        'componentsPath' => ROOT . DS . 'app' . DS . 'components' . DS . $className . '.php',
        'modelsPath' => ROOT . DS . 'app' . DS . 'models' . DS . $className . '.php',
        'layoutPath' => ROOT . DS . 'app' . DS . 'layouts' . DS . $className . '.class.php',
    ];

    $gotit = false;
    foreach ($classPaths as $name => $classPath) {
        if (file_exists($classPath)) {
            require $classPath;
            $gotit = true;
            break;
        }
    }

    if (!$gotit) throw new Exception('Class not found: ' . $tmpClassName);

});

require ROOT . DS . 'vendor' . DS . 'autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(ROOT);
$dotenv->load();

try {

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    /** @var ScraperUrl[] $urls */
    $urls = ScraperUrl::find();

    libxml_use_internal_errors(true);

    $newLeadUrls = [];
    foreach ($urls as $urlRow) {

        $filters = unserialize(base64_decode($urlRow->search_string));
        $client = new GuzzleHttp\Client();
        $leads = recursiveCrawl($urlRow->url, 0, $urlRow->depth, $filters, $client);

        $leadHrefs = [];

        foreach ($leads as $href) {

            $leadHrefs[] = $href;

            /** @var ScraperLead $lead */
            $lead = ScraperLead::findOne(['url_id' => $urlRow->url_id, 'url' => $href]);

            if ($lead) {
                $lead->last_seen = gmdate('Y-m-d H:i:s', time());
                $lead->save();
                continue;
            }

//            $plainSTring = getPdfString($href);

//            if (!$plainSTring) {
//                continue;
//            }

//            $dollarAmount = pullDollarAmountFromString($plainSTring);
//            $address = pullAddressFromString($plainSTring);
//            $addressParts = parseAddressPartsFromGoogle($address);

            /** @var ScraperLead $lead */
            $lead = new ScraperLead();
            $lead->url_id = $urlRow->url_id;
            $lead->url = $href;
            $lead->last_seen = gmdate('Y-m-d H:i:s', time());
            $lead->active = 1;
            $lead->flagged = 0;
//            $lead->judgment_amount = floatval($dollarAmount);
            $lead->judgment_amount = 0.0;
//            $lead->lat = $addressParts['lat'];
            $lead->lat = '';
//            $lead->lon = $addressParts['lon'];
            $lead->lon = '';
//            $lead->street = $addressParts['streetNumber'] . ' ' . $addressParts['streetName'];
            $lead->street = '';
//            $lead->city = $addressParts['city'];
            $lead->city = '';
//            $lead->state = $addressParts['state'];
            $lead->state = '';
//            $lead->zip = $addressParts['zip'];
            $lead->zip = '';
            $lead->save();
            $newLeads[] = $lead;

        }

        // delete any outdated leads for this url
        ScraperLead::find([
            'url' => [
                'operator' => 'NOT IN',
                'value' => [
                    '(\'' . implode('\', \'', $leadHrefs) . '\')'
                ]
            ],
            'url_id' => $urlRow->url_id
        ])->delete();

        // update the main url record
        $urlRow->last_scraped = gmdate('Y-m-d H:i:s', time());
        $urlRow->leads_count = count($leads);
        $urlRow->save();

        // send notification emails
        if (!empty($newLeads)) {
            $newLeadUrls[$urlRow->url_id] = $urlRow->name;
        }

    }

    if (!empty($newLeadUrls)) {

        $mailer = new Mailer();
        $mailer->subject = 'E Squared Holdings | Scraper Notification';
        $mailer->to = ['chris@esquaredholdings.com', 'cody@esquaredholdings.com'];

        $linkBase = $_ENV['BASE_PATH'] . '/scraper/';

        $body = '<p>New leads were detected for the following scraper url(s): </p>';

        $body .= '<p>';
        $sep = '';
        foreach ($newLeadUrls as $urlId => $urlName) {
            $body .= $sep . '<a href="' . $linkBase . $urlId . '/leads">' . $urlName . '</a>';
            $sep = '<br />';
        }
        $body .= '</p>';

        $mailer->html = $body;
        $mailer->send();

    }



} catch (Exception $e) {

    echo 'Scraper Cron Error | Msg: ' . $e->getMessage();

}

echo 'done';

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

    $contents = file_get_contents($url);
    if (!$contents) return false;
    $result = file_put_contents($tmpPdfPath, $contents);
    if (!$result) return false;

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



