<?php

class FileController extends BaseController
{

    public function beforeAction()
    {
        $this->render = false;

        if (!Auth::isAdmin()) {
            HTML::addAlert('Unauthorized access');
            HTTP::redirect('/');
        }
    }

    public function upload()
    {

        $return = [];

        try {

            if (empty($_FILES['filepond'])) throw new Exception('No File');
            else {

//            	$allowedFileTypes = ['jpg', 'jpeg', 'png'];
                $ext = strtolower(pathinfo($_FILES['filepond']['name'], PATHINFO_EXTENSION));
//                if (!in_array($ext, $allowedFileTypes)) throw new Exception('Extension not allowed');

                $path = ROOT . DS . 'app' . DS . 'files' . DS . 'tmp';
                $uid = UID::generate('File');
                $file = $path . DS . $uid . '.' . $ext;

                if (!move_uploaded_file($_FILES['filepond']['tmp_name'], $file)) {
                    throw new Exception('File could not be moved');
                }

                $dbFile = new File();
                $dbFile->uid = $uid;
                $dbFile->deleted = 0;
                $dbFile->type = 'tmp';
                $dbFile->original_name = filter_var($_FILES['filepond']['name'], FILTER_SANITIZE_STRING);
                $dbFile->save();

                // determine if the uploaded file is an image and send that back to the front end
                $finfo = finfo_open(FILEINFO_MIME);  // return mime type ala mimetype extension
                $info = finfo_file($finfo, $file);
                finfo_close($finfo);
                $return['isImage'] = (strpos($info, 'image') !== false);
                $return['fileInfo'] = $info;

                $return['key'] = $uid;

            }

        } catch (Exception $e) {
            $return['error'] = $e->getMessage();
        }


        echo json_encode($return);
        exit;

    }

    public function proxy()
    {
        $fileRef = $_GET['file'] ?? '';

        try {

            for ($i = 0; $i < ob_get_level(); $i++) {
                ob_end_clean();
            }

            if (empty($fileRef)) throw new Exception();

            $file = '';
            foreach (glob(ROOT . DS . 'app' . DS . 'files' . DS . $fileRef) as $file) {
                break; // just take the first file. this is a specific reference. update this later to be more dynamic
            }
            if (emptY($file)) throw new Exception();


            $ext = pathinfo($file)['extension'];

            if (!$mime = $this->getMime($ext)) throw new Exception();

            header('Content-Type: ' . $mime);
            header('Content-Length: ' . filesize($file));
            header('Content-Disposition: attachment; filename="' . basename($file) . '"');

            if ($fp = fopen($file, 'rb')) {
                // use a reasonable packet size to conserve memory
                $packetSize = 1048576;
                while (!feof($fp)) {
                    echo fread($fp, $packetSize);
                }
                fclose($fp);
            }

        } catch (Exception $e) {
            throw new Exception404();
        }
    }

    private function getMime($ext)
    {
         switch ($ext) {
            case 'pdf':
                $mime = 'application/pdf';
                break;
            case 'txt':
                $mime = 'text/plain';
                break;
            case 'csv':
                $mime = 'text/csv';
                break;
            case 'xls':
                $mime = 'application/vnd.ms-excel';
                break;
            case 'doc':
                $mime = 'application/msword';
                break;
            case 'docx':
                $mime = 'application/vnd.openxmlformats';
                break;
            case 'xlsx':
                $mime = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
                break;
            case 'jpg':
            case 'jpeg':
                $mime = 'image/jpeg';
                break;
            case 'png':
                $mime = 'image/png';
                break;
            default:
                $mime = false;
                break;
         }
         return $mime;
    }

    public function afterAction()
    {

    }

}
