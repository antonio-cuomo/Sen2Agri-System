<?php
//require_once('ConfigParams.php');

include 'master.php';

function getTiles($siteId,$satelliteId){
    $dbconn = pg_connect(ConfigParams::getConnection()) or die ("Could not connect");
    $rows = pg_query_params($dbconn, "select * from sp_get_site_tiles($1,$2)",array($siteId,$satelliteId)) or die(pg_last_error());
    return (pg_numrows($rows) > 0 ? pg_fetch_all_columns($rows): array());
}

function CallRestAPI($method, $url, $data = false)
{
    $curl = curl_init();

    switch ($method)
    {
        case "POST":
            curl_setopt($curl, CURLOPT_POST, 1);

            if ($data) {
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Content-Length: ' . strlen($data)));
            }
            break;
        case "DELETE":
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");

            if ($data) {
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Content-Length: ' . strlen($data)));
            }

            echo "Delete " . $data . "      ";
            break;
        case "PUT":
            curl_setopt($curl, CURLOPT_PUT, 1);
            break;
        default:
            if ($data)
                $url = sprintf("%s?%s", $url, http_build_query($data));
    }

    // Optional Authentication:
    //curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    //curl_setopt($curl, CURLOPT_USERPWD, "username:password");

    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    //curl_setopt($curl, CURLOPT_TIMEOUT_MS, 600000);   // 10 minutes

    $result = curl_exec($curl);

    curl_close($curl);

    return $result;
}

function endsWith($str, $sub) {
    return (substr ( $str, strlen ( $str ) - strlen ( $sub ) ) === $sub);
}

function getInsituFolder($siteId, $isStrata) {
    $dbconn = pg_connect( ConfigParams::getConnection() ) or die ( "Could not connect" );
    if (is_numeric($siteId)) {
        $rows = pg_query($dbconn, "SELECT short_name FROM sp_get_sites() WHERE id = ".$siteId) or die(pg_last_error());
        $siteId = pg_fetch_array($rows, 0)[0];
    }
    $rows = pg_query($dbconn, "SELECT key, value FROM sp_get_parameters('processor.l4a.reference_data_dir') WHERE site_id IS NULL") or die(pg_last_error());
    $result = pg_fetch_array($rows, 0)[1];

    $insituDataDir = str_replace("{site}", $siteId, $result);
    if ($isStrata) {
        $insituDataDir = $insituDataDir . DIRECTORY_SEPARATOR . "strata";
    }
    return $insituDataDir;
}
    
function getInsituFileName($siteId, $isStrata) {
    $insituDataDir = getInsituFolder($siteId, $isStrata);
    // echo "Insitu Directory is " . $insituDataDir . "\r\n";
    $newest_file = "";
    if (is_dir($insituDataDir)) {
        //echo "IsDir " . "\r\n";
        $files = scandir($insituDataDir, SCANDIR_SORT_DESCENDING);
        $existingFiles = array_diff($files, array('..', '.', 'strata'));
        foreach($existingFiles as $existingFile) {
            $fullFilePath = $insituDataDir . DIRECTORY_SEPARATOR . $existingFile;
            if (!is_dir($fullFilePath)) {
                $fileExt = pathinfo($fullFilePath, PATHINFO_EXTENSION);
                if (strtolower($fileExt) == 'shp') {
                    $newest_file = $existingFile;
                    break;
                }
            }
        }
        //echo "Newest file " . $newest_file . "\r\n";
    }
    
    return $newest_file;
}

function moveInsituFiles($srcDir, $destDir) {
    
    if (!file_exists($destDir)) {
        if (!mkdir($destDir, 0777, true)) {
            return false;
        }
    }
        
    // ignore ., .., and strata folder
    // create a backup folder for the existing files as we don't want to delete them directly
    $Ignore = array(".","..","strata");
    $existingFiles = array_diff(scandir($destDir), $Ignore);
    if (count($existingFiles) > 0) {
        $date = date_create();
        $formattedDate = date_format($date, "Ymd_His");

        $prevFilesDir = $destDir . DIRECTORY_SEPARATOR . $formattedDate . "_backup_prev_files";
        if (!file_exists($prevFilesDir)) {
            // if we cannot create the folder, we skip backing up
            if (mkdir($prevFilesDir, 0777, true)) {
                foreach($existingFiles as $existingFile) {
                    if(!in_array($existingFile,$Ignore) && !is_dir($destDir . DIRECTORY_SEPARATOR . $existingFile)) {
                        rename($destDir . DIRECTORY_SEPARATOR . $existingFile, $prevFilesDir . DIRECTORY_SEPARATOR . $existingFile); // rename the file 
                    }
                }
            }
        }
    }
    // now move actually the files from the src dir to dest dir
    $existingFiles = array_diff(scandir($srcDir), array('..', '.'));
    if (count($existingFiles) > 0) {
        $Ignore = array(".","..");
        foreach($existingFiles as $existingFile) {
            if(!in_array($existingFile,$Ignore)) {
                rename($srcDir . DIRECTORY_SEPARATOR . $existingFile, $destDir . DIRECTORY_SEPARATOR . $existingFile); // rename the file 
            }
        }
    }
    return true;
}

function get_product_types_from_db() {
    function cmp($a, $b)
    {
        return strcmp($a["description"], $b["description"]);
    }

    $restResult = CallRestAPI("GET", ConfigParams::$REST_SERVICES_URL . "/products/types/");
    $jsonArr = json_decode($restResult, true);
    if (count($jsonArr) > 0) {
        usort($jsonArr, "cmp");
        foreach($jsonArr as $productTypeInfo) {
            $id = $productTypeInfo['id'];
            $short = substr(strtoupper($productTypeInfo['description']), 0, 3);
            ?>
            <input class="form-control" id="delete_chk<?=$short?>" type="checkbox" name="delete_chk<?=$short?>" value="<?=$id?>">
            <label class="control-label" for="delete_chk<?=$short?>"><?=$short?></label>
<?php
        }
    }
}

function createCustomUploadFolder($siteId, $timestamp, $subDir) {
    // create custom upload path like: /mnt/upload/siteName/userName_timeStamp/
    $dbconn = pg_connect( ConfigParams::getConnection() ) or die ( "Could not connect" );
    $rows = pg_query($dbconn, "SELECT key, value FROM sp_get_parameters('site.upload_path') WHERE site_id IS NULL") or die(pg_last_error());
    $result = pg_fetch_array($rows, 0)[1];

    //////////////////////////////////////////////////////
    // TODO: Remove this ... is only for testing on windows
    //$result = "c:" . $result;
    //////////////////////////////////////////////////////

    $upload_target_dir = str_replace("{user}", "", $result);

    $result = $siteId;
    if (is_numeric($siteId)) {
        $rows = pg_query($dbconn, "SELECT name FROM sp_get_sites() WHERE id = ".$siteId) or die(pg_last_error());
        $result = pg_fetch_array($rows, 0)[0];
    }
    $upload_target_dir = $upload_target_dir . $result . "/" . ConfigParams::$USER_NAME . "_".$timestamp . "/";
    $upload_target_dir = str_replace("(", "", $upload_target_dir);
    $upload_target_dir = str_replace(")", "", $upload_target_dir);
    $upload_target_dir = str_replace(" ", "_", $upload_target_dir);
    if ($subDir != '') {
        $upload_target_dir = $upload_target_dir . DIRECTORY_SEPARATOR . $subDir;
    }
    if (!is_dir($upload_target_dir)) {
        mkdir($upload_target_dir, 0755, true);
    }
    return $upload_target_dir;
}

function uploadReferencePolygons($zipFile, $siteId, $timestamp, $subDir, $chkExtractedShape = true) {
    $zip_msg = "";
    $shp_file = false;
    if ($_FILES[$zipFile]["name"]) {
        $filename = $_FILES[$zipFile]["name"];
        $source = $_FILES[$zipFile]["tmp_name"];

        $upload_target_dir = createCustomUploadFolder($siteId, $timestamp, $subDir);

        $target_path = $upload_target_dir . $filename;
        if(move_uploaded_file($source, $target_path)) {
            $zip = new ZipArchive();
            $x = $zip->open($target_path);
            if ($x === true) {
                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $filename = $zip->getNameIndex($i);
                    if (endsWith($filename, '.shp')) {
                        $shp_file = $upload_target_dir . $filename;
                        break;
                    }
                }
                $zip->extractTo($upload_target_dir);
                $zip->close();
                unlink($target_path);
                if ($shp_file) {
                    $zip_msg = "Your .zip file was uploaded and unpacked successfully!";
                } else {
                    $zip_msg = "Your .zip file does not contain any shape (.shp) file!";
                }
            } else {
                $zip_msg = "Your file is not a valid .zip archive!";
            }
        } else {
            $zip_msg = "Failed to upload the file you selected!";
        }
    } else {
        $zip_msg = 'Unable to access your selected file!';
    }

    // verify if shape file has valid geometry
    $shp_msg = '';
    $shape_ok = false;
    if ($chkExtractedShape) {
        if ($shp_file) {
            exec('scripts/check_shp.py -b '.$shp_file, $output, $ret);

            if ($ret === FALSE) {
                $shp_msg = 'Invalid command line!';
            } else {
                switch ($ret) {
                    case 0:     $shape_ok = true; break;
                    case 1:     $shp_file = false; $shp_msg = 'Unable to open the shape file!'; break;
                    case 2:     $shp_file = false; $shp_msg = 'Shape file has invalid geometry!'; break;
                    case 3:     $shp_file = false; $shp_msg = 'Shape file has overlapping polygons!'; break;
                    case 4:     $shp_file = false; $shp_msg = 'Shape file is too complex!'; break;
                    case 127:   $shp_file = false; $shp_msg = 'Invalid geometry detection script!'; break;
                    default:    $shp_file = false; $shp_msg = 'Unexpected error with the geometry detection script!'; break;
                }
            }
            if ($shape_ok) {
                $last_line = $output[count($output) - 1];
                $r = preg_match('/^Union: (.+)$/m', $last_line, $matches);
                if (!$r) {
                    $shp_file = false;
                    $shp_msg = 'Unable to parse shape!';
                } else {
                    $shp_msg = $matches[1];
                }
            }
        } else {
            $shp_msg = 'Missing shape file due to a problem with your selected file!';
        }
    }

    return array ( "polygons_file" => $shp_file, "result" => $shp_msg, "message" => $zip_msg, "upload_target_dir" => $upload_target_dir );
}

function getSatelliteEnableStatus($siteId, $satId) {
    return CallRestAPI("GET",  ConfigParams::$REST_SERVICES_URL . "/products/enable/status/" . $satId . "/" . $siteId);
}

// processing add site
if (isset ( $_REQUEST ['add_site'] ) && $_REQUEST ['add_site'] == 'Save New Site') {
    // first character to uppercase.
    $site_name = ucfirst ( $_REQUEST ['sitename'] );
    $site_enabled = "0"; // empty($_REQUEST ['add_enabled']) ? "0" : "1";
    //$l8_enabled = empty($_REQUEST ['chkL8Add']) ? "0" : "1";
    //print_r($_REQUEST);

    # Check if the site already exists
    # TODO: create an endpoint for checking if site with name exists
    $restResult = CallRestAPI("GET",  ConfigParams::$REST_SERVICES_URL . "/sites/");
     if ($restResult == "") {
        die ("Cannot get site lists! Please check that the sen2agri-services are started!");
        $message = "Cannot get site lists!";
        $result =  "Please check that the sen2agri-services are started!";
        $_SESSION['status'] =  "NOK"; $_SESSION['message'] = $message;  $_SESSION['result'] = $result;
        die(Header("Location: {$_SERVER['PHP_SELF']}"));
    } else {
        $jsonArr = json_decode($restResult, true);
        foreach($jsonArr as $siteRetr) {
            $retrSiteName      = $siteRetr['name'];
            if ($retrSiteName == $site_name) {
                $message = "Site with name " . $retrSiteName . " already exists!";
                $result =  "Please choose another name!";
                $_SESSION['status'] =  "NOK"; $_SESSION['message'] = $message;  $_SESSION['result'] = $result;
                die(Header("Location: {$_SERVER['PHP_SELF']}"));
            }
        }
    }

    function insertSite($site, $coord, $enbl) {
        $db = pg_connect ( ConfigParams::getConnection() ) or die ( "Could not connect" );
        $sql = "SELECT sp_dashboard_add_site($1,$2,$3)";
        $res = pg_prepare ( $db, "my_query", $sql );
        $res = pg_execute ( $db, "my_query", array (
                $site,
                $coord,
                $enbl
        ) ) or die ( "An error occurred." );
        $row = pg_fetch_row($res);
        return $row[0];
    }

    $date = date_create();
    $time_stamp = date_timestamp_get($date);

//    TODO: In the end, should be used the service to create the site (from the services)
//    For now,
//    $sourceFile = $_FILES["zip_fileAdd"]["tmp_name"];
//    $dataObj = new \stdClass();
//    $dataObj->name = $site_name;
//    $dataObj->zipFilePath = $sourceFile;
//    $dataObj->enabled = false;
//    $jsonObj = json_encode($dataObj);
//    $restResult = CallRestAPI("POST",  ConfigParams::$REST_SERVICES_URL . "/sites/" , $jsonObj);

    // upload polygons
    $upload = uploadReferencePolygons("zip_fileAdd", $site_name, $time_stamp, '');
    $polygons_file = $upload ['polygons_file'];
    $coord_geog = $upload ['result'];
    $message = $upload ['message'];
    if ($polygons_file) {
        $site_id = insertSite($site_name, $coord_geog, $site_enabled);
        // update also the L8 enable/disable status
        // $restResult = CallRestAPI("GET",  ConfigParams::$REST_SERVICES_URL . "/products/" . ($l8_enabled ? "enable":"disable") . "/2/" . $site_id);
        // ask services to refresh the configuration from DB
        //$restResult = CallRestAPI("GET",  ConfigParams::$REST_SERVICES_URL . "/refresh/");
        $_SESSION['status'] =  "OK"; $_SESSION['message'] = "Your site has been successfully added!";
    } else {
        $_SESSION['status'] =  "NOK"; $_SESSION['message'] = $message;  $_SESSION['result'] = $coord_geog;
    }

    // Prevent adding site when refreshing page
    die(Header("Location: {$_SERVER['PHP_SELF']}"));
}

// processing edit site
if (isset ( $_REQUEST ['edit_site'] ) && $_REQUEST ['edit_site'] == 'Save Site') {
    $site_id      = $_REQUEST ['edit_siteid'];
    $shortname    = $_REQUEST ['shortname'];
    $site_enabled = empty($_REQUEST ['edit_enabled']) ? "0" : "1";
    //print_r($_REQUEST);
    $l8_enabled = empty($_REQUEST ['chkL8Edit']) ? "0" : "1";
    $tilesS2 =  isset($_REQUEST ['S2Tiles']) ? $_REQUEST ['S2Tiles']:'';
   
    if($site_enabled){
        if($l8_enabled){
            $tilesL8 = isset($_REQUEST ['L8Tiles']) ? $_REQUEST ['L8Tiles']:'';
    
            $tilesL8 = explode(',', trim($tilesL8));
            $match = array();
            if(sizeof($tilesL8)>0){
                // get available tiles for L8
                $availableTiles = getTiles($site_id,2);
                foreach ($tilesL8 as $tileL8){
                    if(preg_match('/^(\d{6})(,\d{6})*/', $tileL8, $matches)){
                        if(in_array($matches[0], $availableTiles)){
                            $match[]= $matches[0];
                        }
                    }
                }
                if(sizeof($match)>0){
                    exec('filter_site_download_tiles.py -t 2 -s '.$shortname.' -e '.$site_enabled.' -l "'.implode(", ", $match).'"',$output, $ret);
                }
            }
        }
       
        //insert site tiles for satellite S2
        $tilesS2 = explode(',', trim($tilesS2));
        $matchS2 = array();
        if(sizeof($tilesS2)>0){
        // get available tiles for S2
            $availableTiles = getTiles($site_id,1);
            foreach ($tilesS2 as $tileS2){
                if(preg_match('/^(\d{2}[A-Z]{3})(,\d{2}[A-Z]{3})*/', $tileS2, $matches) ){
                    if(in_array($matches[0], $availableTiles)){
                        $matchS2[]= $matches[0];
                    }
                }
            }
        
            if(sizeof($matchS2)>0){
                exec('filter_site_download_tiles.py -t 1 -s '.$shortname.' -e '.$site_enabled.' -l "'.implode(", ", $matchS2).'"',$output, $ret);
            }
        }
    }
    
    function polygonFileSelected($name) {
        foreach($_FILES as $key => $val){
            if (($key == $name) && (strlen($_FILES[$key]['name'])) > 0) {
                return true;
            }
        }
        return false;
    }

    function updateSite($id, $enbl) {
        $db = pg_connect ( ConfigParams::getConnection() ) or die ( "Could not connect" );
        $res = pg_query_params ( $db, "SELECT sp_dashboard_update_site($1,$2)", array (
                $id,
                $enbl
        ) ) or die ( "An error occurred." );
    }

    $date = date_create();
    $time_stamp = date_timestamp_get($date);

    // upload polygons if zip file selected
    $status     = "OK";
    $message    = "";
    $insituMsg  = "";
    $strataMsg  = "";
    if (polygonFileSelected("siteInsituDataUpload")) {
        $upload        = uploadReferencePolygons("siteInsituDataUpload", $site_id, $time_stamp, "insitu", false);
        $polygons_file = $upload ['polygons_file'];
        $validationMsg = $upload ['result'];
        $insituMsg     = $upload ['message'];
        if ($polygons_file) {
            $upload_target_dir = $upload ['upload_target_dir'];
            $insituFolder = getInsituFolder($site_id, false);
            $resultMoveInsituFiles = moveInsituFiles($upload_target_dir, $insituFolder);
            if (!$resultMoveInsituFiles) {
                $insituMsg ="Cannot move insitu files from " . $upload_target_dir . " to " . $insituFolder . "\\n";
            } else {
                $insituMsg ="Insitu file successfuly uploaded!\\n";
            }
        } else {
            $errMsg = $upload ['message'];
            if ($validationMsg != '') {
                $errMsg = $validationMsg;
            }
            $insituMsg = "Error uploading insitu file. Error was: " . $errMsg . "\\n";
        }
    }
    if (polygonFileSelected("siteStrataNewData")) {
        $upload        = uploadReferencePolygons("siteStrataNewData", $site_id, $time_stamp, "strata", false);
        $polygons_file = $upload ['polygons_file'];
        $validationMsg = $upload ['result'];
        if ($polygons_file) {
            $upload_target_dir = $upload ['upload_target_dir'];
            $strataFolder = getInsituFolder($site_id, true);
            $resultMoveStrataFiles = moveInsituFiles($upload_target_dir, $strataFolder);
            if (!$resultMoveStrataFiles) {
                $strataMsg ="Cannot move strata files from " . $upload_target_dir . " to " . $strataFolder . "!\\n";
            } else {
                $strataMsg = "Strata file successfuly uploaded!\\n";
            }
        } else {
            $errMsg = $upload ['message'];
            if ($validationMsg != '') {
                $errMsg = $validationMsg;
            }
            $strataMsg = "Error uploading strata file. Error was: " . $errMsg . "\\n";
        }
    }

    if ($status == "OK") {
        updateSite($site_id, $site_enabled);
        
        // update also the L8 enable/disable status
        $restResult = CallRestAPI("GET",  ConfigParams::$REST_SERVICES_URL . "/products/" . ($l8_enabled == "1" ? "enable":"disable") . "/2/" . $site_id);
        // stop or enable the satellite for the L8 satellite or for both satellites if the site is disabled/enabled
        if ($site_enabled == "1") {
            $restResult = CallRestAPI("GET",  ConfigParams::$REST_SERVICES_URL . "/downloader/start/" . $site_id . "/1");
            if ($l8_enabled == "1") {
                $restResult = CallRestAPI("GET",  ConfigParams::$REST_SERVICES_URL . "/downloader/start/" . $site_id . "/2");
            } else {
                $restResult = CallRestAPI("GET",  ConfigParams::$REST_SERVICES_URL . "/downloader/stop/" . $site_id . "/2");
            }
        } else {
            $restResult = CallRestAPI("GET",  ConfigParams::$REST_SERVICES_URL . "/downloader/stop/" . $site_id . "/1");
            $restResult = CallRestAPI("GET",  ConfigParams::$REST_SERVICES_URL . "/downloader/stop/" . $site_id . "/2");
        }
        
        // Refresh the configuration
        //$restResult = CallRestAPI("GET",  ConfigParams::$REST_SERVICES_URL . "/refresh/");

        $message = "Your site has been successfully modified!";
    }
        if ($insituMsg != '' || $strataMsg != '') {
            $message = $message . "\\nAlso:\\n";
        }

        $message = $message . $insituMsg . $strataMsg;

    $_SESSION['status'] =  $status; $_SESSION['message'] = $message;

    // Prevent updating site when refreshing page
    die(Header("Location: {$_SERVER['PHP_SELF']}"));
}

// processing  delete_site
if (isset ( $_REQUEST ['delete_site_confirm'] ) && $_REQUEST ['delete_site_confirm'] == 'Confirm Delete Site') {
    $shortname      = $_REQUEST ['delete_site_short_name'];

    $arr_result = array();    
    foreach($_POST as $key => $value) {
        if (strpos($key, 'delete_chk') === 0) {
            // value starts with delete_chk
            $arr_result[] = (int)$value;
        }
    }
    $dataObj = new \stdClass();
    $dataObj->siteId = -1;
    $dataObj->siteShortName = $shortname;
    $dataObj->productTypeIds = $arr_result;
    $jsonObj = json_encode($dataObj);

    $restResult = CallRestAPI("DELETE",  ConfigParams::$REST_SERVICES_URL . "/sites/" , $jsonObj);

    $status     = "OK";
    $message    = "";

    if ($status == "OK") {
        //updateSite($site_id, $site_enabled);
        $message = "Your site " . $shortname . " has been successfully removed!";
    }
    $_SESSION['status'] =  $status; $_SESSION['message'] = $message;

    // Prevent updating site when refreshing page
    die(Header("Location: {$_SERVER['PHP_SELF']}"));
}

//end - delete site

?>
<div id="main">
    <div id="main2">
        <div id="main3">
            <!-- Start code for adding site---------- -->
            <div class="panel panel-default create-site">
                <div class="panel-body">
                    <?php
                    if (!$_SESSION['isAdmin']) {
                        // not admin ?>
                        <div class="panel-heading row">Seasons site details</div>
                    <?php } else {
                        // admin ?>
                        <div class="panel-heading row"><input name="addsite" type="button" class="add-edit-btn" value="Create new site" onclick="formAddSite()" style="width: 200px"></div>
                    <?php } ?>
                    <!---------------------------  form  add site ------------------------>
                    <div class="add-edit-site" id="div_addsite" style="display: none;">
                        <form enctype="multipart/form-data" id="siteform" action="create_site.php" method="post">
                            <div class="row">
                                <div class="col-md-1">
                                    <div class="form-group  form-group-sm">
                                        <label class="control-label" for="sitename">Site name:</label>
                                        <input type="text" class="form-control" id="sitename" name="sitename">
                                    </div>
<!--
                                    <div class="form-group form-group-sm sensor">
                                                <label  style="">Enabled sensor:</label>
                                                <input class="form-control chkS2" id="chkS2Add" type="checkbox" name="chkS2Add" value="S2" checked="checked" disabled>
                                                <label class="control-label" for="lchkS2Add">S2</label>
                                                <input class="form-control chkL8" id="chkL8Add" type="checkbox" name="chkL8Add" value="L8" checked="checked">
                                                <label class="control-label" for="chkL8Add">L8</label>
                                    </div>
-->
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-1">
                                    <div class="form-group form-group-sm">
                                        <label class="control-label" style="color:gray">Seasons:</label>
                                        <h6 style="color:gray;padding:0px 10px 10px 10px;">Seasons can only be added/modified after site creation</h6>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-1">
                                    <div class="form-group form-group-sm">
                                        <label class="control-label" for="zip_fileAdd">Upload site shape file:</label>
                                        <input type="file" class="form-control" id="zip_fileAdd" name="zip_fileAdd">
                                    </div>
                                </div>
                            </div>
                            <div class="submit-buttons">
                                <input class="add-edit-btn" name="add_site" type="submit" value="Save New Site">
                                <input class="add-edit-btn" name="abort_add" type="button" value="Abort" onclick="abortEditAdd('add')">
                            </div>
                        </form>
                    </div>
                    <!---------------------------- end form add ---------------------------------->

                    <!---------------------------  form  delete site ------------------------>
                    <div class="add-edit-site" id="div_deletesite" style="display: none;">
                        <form enctype="multipart/form-data" id="siteform" action="create_site.php" method="post">
                            <div class="row">
                                <div class="col-md-1">
                                    <div class="form-group  form-group-sm">
                                        <label class="control-label" for="delete_sitename">Site name: </label>
                                        <input type="text" class="form-control" id="delete_sitename" name="delete_sitename" value="" readonly>
                                        <input type="hidden" class="form-control" id="delete_site_short_name" name="delete_site_short_name" value="">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group form-group-sm sensor" id="delete_checkboxes_div">
                                <label  style="">To delete:</label>
                                <?php get_product_types_from_db(); ?>
                            </div>
                            <div class="submit-buttons">
                                <input class="delete-btn" name="delete_site_confirm" type="submit" value="Confirm Delete Site">
                                <input class="add-edit-btn" name="abort_add" type="button" value="Abort" onclick="abortEditAdd('delete_site')">
                            </div>
                        </form>
                    </div>
                    <!---------------------------- end form Delete ---------------------------------->

                    <!---------------------------- form edit sites ------------------------------->
                    <div class="add-edit-site" id="div_editsite" style="display: none;">
                        <form enctype="multipart/form-data" id="siteform_edit" action="create_site.php" method="post">
                            <div class="row">
                                <div class="col-md-1">
                                    <div class="form-group form-group-sm">
                                        <label class="control-label" for="edit_sitename">Site name:</label>
                                        <input type="text" class="form-control" id="edit_sitename" name="edit_sitename" value="" readonly>
                                        <input type="hidden" class="form-control" id="edit_siteid" name="edit_siteid" value="">
                                        <input type="hidden" class="form-control" id="shortname" name="shortname" value="">
                                    </div>
                                </div>
                            </div>
                            <div class="row">                             	                       
                            	<div class="col-md-1" style="width: 115px;padding-right:0px" >
                            		<label  style="">Enabled sensor:</label>   
                            	</div>
                            	
                            	<div class="col-md-9">  
                            	                          	
                            		<div class="row">                    		                                  
                                    	<div class="col-md-1" style="width: 10%;padding-right: 0px;">          
                                        	<div class="form-group form-group-sm">                                       	           
                                                <input class="chkS2" id="chkS2Edit" type="checkbox" name="chkS2Edit" value="S2" checked="checked" disabled ">
                                                <label class="control-label" for="chkS2Edit">S2</label>          
                                            </div>                            
                                        </div>
                                        <div class="col-md-9" >       
                                        	<div class="form-group form-group-sm">             
                                            	<textarea class='form-control' style="resize: vertical;" rows="2" name="S2Tiles"></textarea>
                                            	<span class="invalidTilesS2"></span>
                                            </div>
                                        </div>                                    	
                                    </div>
                                    
                                    <div class="row">
                                    	<div class="col-md-1" style="width: 10%;padding-right: 0px;">    
                                    		<div class="form-group form-group-sm">                 
                                                <input class="chkL8" id="chkL8Edit" type="checkbox" name="chkL8Edit" value="L8" checked="checked" onchange="enableDisable($(this), $('[name=\'edit_enabled\']').bootstrapSwitch('state'))">
                                                <label  for="chkL8Edit">L8</label>                                             
                                        	</div>
                                        </div>
                                        <div class="col-md-9" >          
                                       		<div class="form-group form-group-sm">                
                                            	<textarea class='form-control' style="resize: vertical;" rows="2" name="L8Tiles""></textarea>
                                            	<span class="invalidTilesL8"></span>
                                            </div>
                                        </div>
                                    </div>
                             
                              	</div>                       
                            </div>
                            
                            <div class="row">
                                <div class="col-md-1">
                                    <div class="form-group form-group-sm">
                                        <label class="control-label">List of Seasons</label>
                                        <div id="site-seasons"><img src="./images/loader.gif" width="64px" height="64px"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-1">
                                    <div class="form-group form-group-sm">
                                        <label class="control-label" for="edit_enabled">Enable site:</label>
                                        <input type="checkbox" name="edit_enabled" id="edit_enabled">
                                    </div>
                                </div>
                            </div>

                            <div class="container-fluid">
                                <div class="panel-group config" id="accordion">
                                    <div class="panel panel-default">
                                        <div class="panel-heading">
                                            <h4 class="panel-title">
                                                <a data-toggle="collapse" data-parent="#accordion" href="#siteInsituDataAcc" id="siteInsituDataAccHref">Insitu data</a>
                                            </h4>
                                        </div>

                                        <div id="siteInsituDataAcc" class="panel-collapse collapse">
                                            <div class="panel-body">
                                                <!-- <div class="subgroup lai">
                                                    <label class="control-label">In situ:</label> -->
                                                    <div class="form-group form-group-sm">
                                                        <label class="inputlabel" for="siteInsituData">Existing file:</label>
                                                        <input type="text" class="form-control labelinput" id="siteInsituData" name="siteInsituData" value="" readonly>
                                                    </div>
                                                    <div class="form-group form-group-sm">
                                                        <label class="inputlabel" for="siteInsituDataUpload">Upload file:</label>
                                                        <input type="file" class="form-control labelinput" id="siteInsituDataUpload" name="siteInsituDataUpload" onchange="onNewFileSelected('siteInsituDataAccHref');">
                                                    </div>
                                                <!-- </div>   -->
                                            </div>
                                        </div>
                                    </div>
                                    <div class="panel panel-default">
                                        <div class="panel-heading">
                                            <h4 class="panel-title">
                                                <a data-toggle="collapse" data-parent="#accordion" href="#siteStrataDataAcc" id="siteStrataDataAccHref">Strata data</a>
                                            </h4>
                                        </div>

                                        <div id="siteStrataDataAcc" class="panel-collapse collapse">
                                            <div class="panel-body">
                                                <!-- <div class="subgroup lai">
                                                    <label class="control-label">In situ:</label>-->
                                                    <div class="form-group form-group-sm">
                                                        <label class="inputlabel" for="siteStrataData">Existing file:</label>
                                                        <input type="text" class="form-control labelinput" id="siteStrataData" name="siteStrataData" value="" readonly>
                                                    </div>
                                                    <div class="form-group form-group-sm">
                                                        <label class="inputlabel" for="siteStrataNewData">Upload file:</label>
                                                        <input type="file" class="form-control labelinput" id="siteStrataNewData" name="siteStrataNewData" onchange="onNewFileSelected('siteStrataDataAccHref');">
                                                        <!-- $(this).trigger('blur') -->

                                                        <!--
                                                        <input type="text" class="form-control labelinput2" id="siteStrataNewData" name="siteStrataNewData" readonly>
                                                        <input type="file" id="selectedFile" style="display: none;" />
                                                        <input type="button" class="labelinputbutton" value="Browse..." onclick="document.getElementById('selectedFile').click();" />
                                                        -->

                                                    </div>

                                                <!-- </div>   -->
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="submit-buttons">
                                <input class="delete-btn" name="delete_site" type="button" value="Delete Site" onclick="formDeleteSite()">
                                <input class="add-edit-btn" name="edit_site" type="submit" value="Save Site">
                                <input class="add-edit-btn" name="abort_edit" type="button" value="Abort" onclick="abortEditAdd('edit')">
                            </div>
                        </form>
                    </div>
                    <!------------------------------ end form edit sites -------------------------------->

                    <!------------------------------ list of sites -------------------------------------->
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th rowspan="2">Site name</th>
                                <th rowspan="2">Short name</th>
                                <th class="seasons">Seasons</th>
                                <th rowspan="2">Edit</th>
                                <th rowspan="2">Enabled</th>
                            </tr>
                            <tr>
                                <th>
                                    <table class="subtable"><thead><th>Season name</th><th>Season start</th><th>Season mid</th><th>Season end</th><th>Enabled</th></thead><tbody></tbody></table>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php

                        $restResult = CallRestAPI("GET",  ConfigParams::$REST_SERVICES_URL . "/sites/");
                         if ($restResult == "") {
                            print_r ("Cannot get site lists! Please check that the sen2agri-services are started!");
                        } else {
                            $jsonArr = json_decode($restResult, true);
                            $userSites = (isset($_SESSION['siteId']) && sizeof($_SESSION['siteId'])>0)? $_SESSION['siteId']:array();
                            if(is_array($jsonArr)){
	                            foreach($jsonArr as $site) {                              
	                                if((sizeof($userSites)>0 && in_array($site['id'], $userSites)) || (sizeof($userSites) == 0 && $_SESSION['isAdmin'])){
	                                    $siteId        = $site['id'];
	                                    $siteName      = $site['name'];
	                                    $shortName     = $site['shortName'];
	                                    $site_enabled  = $site['enabled'];
	                                    $siteInsituFile = getInsituFileName($shortName, false);
	                                    $siteStrataFile = getInsituFileName($shortName, true);
	                                    $siteL8Enabled = (getSatelliteEnableStatus($siteId, 2) == "false" ? "" : "checked");  // only L8 for now
	                               

                            //}
                            //
                            //
                            //$result = "";
                            //$db = pg_connect ( ConfigParams::$CONN_STRING ) or die ( "Could not connect" );
                            //if (empty($_SESSION['siteId'])) {
                            //    $sql_select = "SELECT * FROM sp_get_sites(null)";
                            //    $result = pg_query_params ( $db, $sql_select, array () ) or die ( "Could not execute." );
                            //} else {
                            //    $sql_select = "SELECT * FROM sp_get_sites($1)";
                            //    $result = pg_query_params ( $db, $sql_select, array($_SESSION['siteId']) ) or die ( "Could not execute." );
                            //}
                            //while ( $row = pg_fetch_row ( $result ) ) {
                            //    $siteId        = $row[0];
                            //    $siteName      = $row[1];
                            //    $shortName     = $row[2];
                            //    $site_enabled  =($row[3] == "t") ? true : false;
                            //    $siteInsituFile = getInsituFileName($shortName, false);
                            //    $siteStrataFile = getInsituFileName($shortName, true);
                                ?>
                                <tr data-id="<?= $siteId ?>">
                                    <td><?= $siteName ?></td>
                                    <td><?= $shortName ?></td>
                                    <td class="seasons"></td>
                                    <td class="link"><a onclick='formEditSite(<?= $siteId ?>,"<?= $siteName ?>","<?= $shortName ?>",<?= $site_enabled ? "true" : "false" ?>, "<?= $siteInsituFile ?>", "<?= $siteStrataFile ?>",
                                    "<?= $siteL8Enabled ?>")'>Edit</a></td>
                                    <td><input type="checkbox" name="enabled-checkbox"<?= $site_enabled ? "checked" : "" ?>></td>
                                </tr>
                        <?php  } } } } ?>
                        </tbody>
                    </table>
                    <!------------------------------ end list sites ------------------------------>
                </div>
            </div>
            <!-- End code for adding site---------- -->
        </div>
    </div>
</div>

<!-- includes for datepicker -->
<link rel="stylesheet" href="libraries/jquery-ui/jquery-ui.min.css">
<script src="libraries/jquery-ui/jquery-ui.min.js"></script>

<!-- includes for bootstrap-switch -->
<link rel="stylesheet" href="libraries/bootstrap-switch/bootstrap-switch.min.css">
<script src="libraries/bootstrap-switch/bootstrap-switch.min.js"></script>

<!-- includes for validate form -->
<script src="libraries/jquery-validate/jquery.validate.min.js"></script>
<script src="libraries/jquery-validate/additional-methods.min.js"></script>

<script type="text/javascript">
$(document).ready( function() {
	// get all site seasons
    $("table.table td.seasons").each(function() {
        getSiteSeasons($(this).parent().data("id"));
    });

    // create dialog for add site form
    $("#div_addsite").dialog({
        title: "Add New Site",
        width: '560px',
        autoOpen: false,
        modal: true,
        resizable: false,
        beforeClose: function( event, ui ) { resetEditAdd("add"); }
    });

    // create dialog for edit site form
    $("#div_editsite").dialog({
        title: "Edit Site",
        width: '700px',
        autoOpen: false,
        modal: true,
        resizable: false,
        beforeClose: function( event, ui ) { resetEditAdd("edit"); }
    });

        // create dialog for delete site form
    $("#div_deletesite").dialog({
        title: "Delete Site",
        width: '700px',
        autoOpen: false,
        modal: true,
        resizable: false,
        beforeClose: function( event, ui ) { resetEditAdd("delete"); }
    });

    // change row style when site editing
    $( ".create-site a" ).click(function() {
        $(this).parent().parent().addClass("editing")
    });

    // create switches for all checkboxes in the sites list
    $("[name='enabled-checkbox']").bootstrapSwitch({
        size: "mini",
        onColor: "success",
        offColor: "default",
        disabled: true,
        handleWidth: 25
    });

    // create switch for enabled checkbox in the add site form
    $("[name='add_enabled']").bootstrapSwitch({
        size: "small",
        onColor: "success",
        offColor: "default"
    });

    // create switch for enabled checkbox in the edit site form
    $("[name='edit_enabled']").bootstrapSwitch({
        size: "small",
        onColor: "success",
        offColor: "default",
        onSwitchChange: function(event,state){
        	 enableDisable( $('#chkS2Edit'),state);
        	 enableDisable( $('#chkL8Edit'),state);
            }
    });

    // validate add site form
    $("#siteform").validate({
        rules: {
            sitename:{ required: true, pattern: "[A-Z]{1}[\\w ]*" },
            zip_fileAdd: "required"
        },
        messages: {
            sitename: { pattern : "First letter must be uppercase. Letters, digits, spaces and underscores are allowed" }
        },
        highlight: function(element, errorClass) {
            $(element).parent().addClass("has-error");
        },
        unhighlight: function(element, errorClass) {
            $(element).parent().removeClass("has-error");
        },
        errorPlacement: function(error, element) {
            error.appendTo(element.parent());
        },
        submitHandler: function(form) {
            $.ajax({
                url: $(form).attr('action'),
                type: $(form).attr('method'),
                data: new FormData(form),
                success: function(response) {
                    $("#siteform")[0].reset();
                }
            });
        },
        // set this class to error-labels to indicate valid fields
        success: function(label) {
            label.remove();
        },
    });

    // validate edit site form
    $("#siteform_edit").validate({
        rules: {
            shortname:{ required: true, pattern: "[a-z]{1}[a-z_ ]*" }
        },
        messages: {
            shortname: { pattern : "Only small letters,space and underscore are allowed" }
        },
        highlight: function(element, errorClass) {
            $(element).parent().addClass("has-error");
        },
        unhighlight: function(element, errorClass) {
            $(element).parent().removeClass("has-error");
        },
        errorPlacement: function(error, element) {
            error.appendTo(element.parent());
        },
        submitHandler: function(form) {
            // first send the enable status of the satellite for this site
            //sendSatelliteEnableStatus();
           // event.preventDefault();
            
			//check the tiles
			var tilesL8NotValid = '';var tilesS2NotValid = '';
			if($("#chkS2Edit").is(':checked')){
    			var s2Tiles = $('textarea[name="S2Tiles"]').val();
    
    			if(s2Tiles!=""){
    				tilesS2Arr = s2Tiles.split(',');
    				for(i = 0; i < tilesS2Arr.length; i++){
    					var noMatch = tilesS2Arr[i].replace(/^(\d{2}[A-Z]{3})(,\d{2}[A-Z]{3})*/gm,'');
    					if(noMatch.trim()!=''){
    						tilesS2NotValid = tilesS2NotValid+" "+tilesS2Arr[i];
    					}
    				}
    			}
			}

			if($("#chkL8Edit").is(':checked')){
    			var l8Tiles = $('textarea[name="L8Tiles"]').val();
    
    			if(l8Tiles!=""){
    				tilesL8Arr = l8Tiles.split(',');
    				for(i = 0; i < tilesL8Arr.length; i++){
    					var noMatch = tilesL8Arr[i].replace(/^(\d{6})(,\d{6})*/gm,'');
    					if(noMatch.trim()!=''){
    						tilesL8NotValid = tilesL8NotValid+" "+ tilesL8Arr[i];
    					}
    				}
    			}
			}
    		if(tilesS2NotValid!=''){
    			$(".invalidTilesS2").text("Invalid tile: "+tilesS2NotValid);
    		}else{
    			$(".invalidTilesS2").text("");
    			}		
    		if(tilesL8NotValid!=''){
    			$(".invalidTilesL8").text("Invalid tile: "+tilesL8NotValid);
    		}else{
    			$(".invalidTilesL8").text("");
    			}
			if(tilesS2NotValid=='' && tilesL8NotValid==''){
				
                $.ajax({
                    url: $(form).attr('action'),
                    type: $(form).attr('method'),
                    data: new FormData(form),
                    success: function(response) {}
                });
			}
        },
        // set this class to error-labels to indicate valid fields
        success: function(label) {
            label.remove();
        },
    });

    // display OK/NOK message after the form has been posted
    <?php
    if ( isset($_SESSION['status']) ){
        if ( $_SESSION['status'] =='OK' ) {
            echo "alert('".$_SESSION['message']."')";
            unset($_SESSION['status']);
            unset($_SESSION['message']);
        } else if ( $_SESSION['status']=='NOK' && isset($_SESSION['result']) ){
            echo "alert('FAILED: ".$_SESSION['message']." ".$_SESSION['result'] ."')";
            unset($_SESSION['status']);
            unset($_SESSION['message']);
            unset($_SESSION['result']);
        }
    }
    ?>
});

// TODO : Use this version when removing completely the PHP
//function sendSatelliteEnableStatus() {
//    var l3bchkL8Btn = $("#chkL8Edit");
//    var siteId =  ($("#edit_siteid")[0].value);
//    var l8Enabled = $("#chkL8Edit")[0].checked;
//    var url = "http://localhost:8080/products/" + (l8Enabled ? "enable":"disable") + "/2/" + siteId;
//    $.ajax({
//        url: url,
//        type: "get",
//        cache: false,
//        crosDomain: true,
//        dataType: "html",
//        success: function(data) {
//
//        },
//        error: function (responseData, textStatus, errorThrown) {
//            alert("Satellite enable/disable was not performed! Error was " + errorThrown);
//        }
//    });
//}

function getSiteSeasons(site_id) {
    var collection = "table.table tr[data-id='" + site_id + "'] td.seasons";
    $(collection).each(function() {
        var season = this;
        $.ajax({
            url: "getSiteSeasons.php",
            type: "get",
            cache: false,
            crosDomain: true,
            data: { "siteId": $(season).parent().data("id"), "action": "get" },
            dataType: "html",
            success: function(data) {
                if (data.length > 0) {
                    $(season).html(data);
                    $(season).find("input[name='season_enabled']").bootstrapSwitch({
                        size: "mini",
                        onColor: "success",
                        offColor: "default",
                        disabled: true,
                        handleWidth: 25
                    });
                } else {
                    $(season).html("-");
                }
            },
            error: function (responseData, textStatus, errorThrown) {
                console.log("Response: " + responseData + "   Status: " + textStatus + "   Error: " + errorThrown);
            }
        });
    });
}

// TODO : Use this version when removing completely the PHP
//function getSiteSeasons(site_id) {
//    var collection = "table.table tr[data-id='" + site_id + "'] td.seasons";
//    $(collection).each(function() {
//        var season = this;
//        $.ajax({
//            url: "http://localhost:8080/sites/seasons/" + $(season).parent().data("id"),
//            type: "get",
//            cache: false,
//            crosDomain: true,
//            dataType: "html",
//            success: function(data) {
//                var tableElem = getSeasonsHtml(data);
//                if (tableElem != null) {
//                    $(season).html(tableElem);
//                    $(season).find("input[name='season_enabled']").bootstrapSwitch({
//                        size: "mini",
//                        onColor: "success",
//                        offColor: "default",
//                        disabled: true,
//                        handleWidth: 25
//                    });
//                } else {
//                    $(season).html("-");
//                }
//            },
//            error: function (responseData, textStatus, errorThrown) {
//                console.log("Response: " + responseData + "   Status: " + textStatus + "   Error: " + errorThrown);
//            }
//        });
//    });
//}


// TODO : Use this version when removing completely the PHP
//function getSeasonsHtml(json) {
//    var arr = $.parseJSON(json);
//    if (arr.length > 0) {
//        var tableElem = document.createElement('table');
//        tableElem.setAttribute("class", "subtable");
//        for (var i = 0; i<arr.length; i++) {
//            var trElem = document.createElement('tr');
//
//            var tdElem = document.createElement('td');
//            tdElem.innerHTML = arr[i].name;
//            trElem.appendChild(tdElem);
//
//            tdElem = document.createElement('td');
//            tdElem.innerHTML = formatDateFromJSonObj(arr[i].startDate);
//            trElem.appendChild(tdElem);
//
//            tdElem = document.createElement('td');
//            tdElem.innerHTML = formatDateFromJSonObj(arr[i].midDate);
//            trElem.appendChild(tdElem);
//
//            tdElem = document.createElement('td');
//            tdElem.innerHTML = formatDateFromJSonObj(arr[i].endDate);
//            trElem.appendChild(tdElem);
//
//            tdElem = document.createElement('td');
//            var input = document.createElement('input');
//            input.setAttribute("class", "form-control");
//            input.setAttribute("type", "checkbox");
//            input.setAttribute("name", "season_enabled");
//            input.setAttribute("checked", arr[i].enabled ? "checked" : "");
//            tdElem.appendChild(input);
//            trElem.appendChild(tdElem);
//
//            tableElem.appendChild(trElem);
//
//        }
//        return tableElem;
//    }
//    return null;
//}

function formatDateFromJSonObj(jsonObj) {
    return (jsonObj.year + "-" + ("0" + jsonObj.monthValue).slice(-2) + "-" + ("0" + jsonObj.dayOfMonth).slice(-2));
}

function onNewFileSelected(id) {
    if (document.getElementById(id).innerHTML.indexOf("<font color=\"red\"> (changed)</font>") == -1) {
        document.getElementById(id).innerHTML = document.getElementById(id).innerHTML + "<font color=\"red\"> (changed)</font>";
    }
}

// Open add site form
function formAddSite(){
    // reset all form fields
    resetEditAdd("add");

    // open add site dialog and close all others
    $("#div_editsite").dialog("close");
    $("#div_addsite").dialog("open");
}

// open delete site dialog
function formDeleteSite(){
    // open add site dialog and close all others
    // $("#div_editsite").dialog({style : "display:none;"});
    $("#div_addsite").dialog("close");
    $("#div_deletesite").dialog("open");

     // TODO : Use this version when removing completely the PHP
    //createDeleteCheckboxes();
};

 // TODO : Use this version when removing completely the PHP
//function createDeleteCheckboxes() {
//    $.ajax({
//        url: "http://localhost:8080/products/types/",
//        type: "get",
//        cache: false,
//        contentType: 'application/json',
//        mimeType: 'application/json',
//        crosDomain: true,
//        success: function(data) {
//            // sort the array by description
//            data.sort(function(a,b) {return (a.description > b.description) ? 1 : ((b.description > a.description) ? -1 : 0);} );
//            for (var i = 0; i<data.length; i++) {
//                var entry = data[i];
//                var shortName = data[i].description.substring(0,3);
//                var input = document.createElement('input');
//                input.setAttribute("class", "form-control");
//                input.setAttribute("id", "delete_chk" + shortName);
//                input.setAttribute("type", "checkbox");
//                input.setAttribute("name", "delete_chk" + shortName);
//                input.setAttribute("value", data[i].id);
//                input.setAttribute("checked", "checked");
//
//                var label = document.createElement('label');
//                label.setAttribute("class", "control-label");
//                label.setAttribute("for", "delete_chk" + shortName);
//                label.innerHTML = shortName;
//
//                var body = $("#delete_checkboxes_div")[0];
//                body.appendChild(input);
//                body.appendChild(label);
//            }
//        },
//        error: function (responseData, textStatus, errorThrown) {
//            alert ("Cannot extract product types! \r\n The Error was: " + errorThrown);
//        }
//    });
//}

// TODO : Use this version when removing completely the PHP
//function onDeleteSiteBtn() {
//    var selectedFiles = $('*[id^="delete_chk"]');
//    var siteShortName =  ($("#delete_site_short_name")[0].value);
//    var idsArr = [];
//    for (var i = 0; i<selectedFiles.length; i++) {
//        if (selectedFiles[i].checked) {
//            idsArr.push(parseInt(selectedFiles[i].value));
//        }
//    }
//    var postData = JSON.stringify({siteId:-1,siteShortName:siteShortName,productTypeIds:idsArr});
//    $.ajax({
//        url: "http://localhost:8080/sites/",
//        type: "delete",
//        data: postData,
//        cache: false,
//        contentType: 'application/json',
//        mimeType: 'application/json',
//        crosDomain: true,
//        success: function(data) {
//            alert ("Site successfully deleted!");
//            // close both the delete and edit site forms
//            $("#div_deletesite").dialog("close");
//            $("#div_editsite").dialog("close");
//            location.reload();
//        },
//        error: function (responseData, textStatus, errorThrown) {
//            //console.log("Response: " + responseData + "   Status: " + textStatus + "   Error: " + errorThrown);
//            alert ("Site couldn't be deleted! \r\n The Error was: " + errorThrown);
//            $("#div_deletesite").dialog("close");
//        }
//    });
//}

// Open edit site form
function formEditSite(id, name, short_name, site_enabled, siteInsituFile, siteStrataFile, l8Enabled) {
    // set values for all edited fields
    removeAppendedColoredText("siteInsituDataAccHref");
    removeAppendedColoredText("siteStrataDataAccHref");

    $("#edit_sitename").val(name);
    $("#edit_siteid").val(id);
    $("#shortname").val(short_name);
    $("#siteInsituData").val(siteInsituFile);
    $("#siteStrataData").val(siteStrataFile);
    $("#edit_enabled").bootstrapSwitch('state', site_enabled);
    $("#accordion").collapse('hide');

    $("#delete_sitename").val(name);
    $("#delete_site_short_name").val(short_name);

    // open edit site dialog and close all others
    $("#div_addsite").dialog("close");
    $("#div_editsite").data("id", id);
    $("#div_editsite").dialog("open");

    document.getElementById("chkL8Edit").checked = l8Enabled;
    // document.getElementById("chkL8Add").checked = l8Enabled;

    enableDisable( $('#chkS2Edit'),site_enabled);
    enableDisable( $('#chkL8Edit'),site_enabled);
  
    $.ajax({
        url: "getSiteSeasons.php",
        type: "get",
        cache: false,
        crosDomain: true,
        data:  { "siteId": id, "action": "edit" },
        dataType: "html",
        success: function(data) {
            $("#site-seasons").html(data);
        },
        error: function (responseData, textStatus, errorThrown) {
            console.log("Response: " + responseData + "   Status: " + textStatus + "   Error: " + errorThrown);
        }
    });


	//get tiles for L8
	$.ajax({
		type: "GET",
		url: "processing.php",
		dataType: "json",		                    
		//data: {ajax: '1',site_id:id,satellite_id:2},
		data: {action: 'getTiles', siteId:id, satelliteId:2},
		success: function(data){
			$('textarea[name="L8Tiles"]').text(data);
			 },
		error: function (responseData, textStatus, errorThrown) {
		            console.log("Response: " + responseData + "   Status: " + textStatus + "   Error: " + errorThrown);
		    }
		});

    //get tiles for S2
    $.ajax({
    	type: "GET",
    	url: "processing.php",
    	dataType: "json",
    	data: {action: 'getTiles', siteId:id, satelliteId:1},
    	success: function(data){
    		$('textarea[name="S2Tiles"]').text(data);
    		 },
    	error: function (responseData, textStatus, errorThrown) {
    	            console.log("Response: " + responseData + "   Status: " + textStatus + "   Error: " + errorThrown);
    	    }
    	});
    
    // TODO : Use this version when removing completely the PHP
//    $.ajax({
//        url: "http://localhost:8080/products/enable/status/2/" + id,
//        type: "get",
//        cache: false,
//        crosDomain: true,
//        dataType: "html",
//        success: function(data) {
//            document.getElementById("chkL8Edit").checked = (data == "true") ? true : false;
//        },
//        error: function (responseData, textStatus, errorThrown) {
//            console.log("Response: " + responseData + "   Status: " + textStatus + "   Error: " + errorThrown);
//        }
//    });
};

function removeAppendedColoredText(id) {
    var str = document.getElementById(id).innerHTML;
    var modifIdx = str.indexOf('<font color');
    document.getElementById(id).innerHTML = str.substring(0, modifIdx > 0 ? modifIdx : str.length);
}

// Reset add/edit site event
function resetEditAdd(formName) {
    if (formName == "add") {
        var validator = $("#siteform").validate();
        validator.resetForm();
        $("#siteform")[0].reset();
    } else if (formName == "edit") {
        var validator = $("#siteform_edit").validate();
        validator.resetForm();
        $("#siteform_edit")[0].reset();

        // refresh seasons for edited/added site
        var site_id = $("#div_editsite").data("id");
        getSiteSeasons(site_id);
        $("#div_editsite").removeData("id");
    }
    else if (formName == "delete_site") {
        var validator = $("#siteform_delete").validate();
        validator.resetForm();
        $("#siteform_delete")[0].reset();

        // refresh seasons for edited/added site
//        var site_id = $("#div_deletesite").data("id");
//        getSiteSeasons(site_id);
//        $("#div_deletesite").removeData("id");
    }
    $( ".create-site tr").removeClass("editing");
}

// Abort add/edit site event
function abortEditAdd(abort){
    if (abort == 'add') {
        $("#div_addsite").dialog("close");
    } else if (abort == 'edit') {
        $("#div_editsite").dialog("close");
        
        //delete error messages for tiles
        $(".invalidTilesS2").text("");
        $(".invalidTilesL8").text("");
    } else if (abort == 'delete_site') {
        $("#div_deletesite").dialog("close");
    }
}

function enableDisable(elem,site_enabled){
	var value = elem.val();
	if(site_enabled){
		if(	elem.is(':checked')){	
	    	//enable textarea for editing tiles	
	    	$('textarea[name="'+value+'Tiles"]').prop('disabled', false);
	    }else{
	    	//disable textarea for editing tiles
	    	$('textarea[name="'+value+'Tiles"]').prop('disabled', true);
		}
	}else{
		$('textarea[name="'+value+'Tiles"]').prop('disabled', true);
		}

}
</script>

<?php include 'ms_foot.php'; ?>

