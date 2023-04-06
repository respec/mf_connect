<?php
session_start();

require 'Slim/Slim.php';
use Slim\Slim;
\Slim\Slim::registerAutoloader();

$json = file_get_contents('../config.json');
$config = json_decode($json, true);
$dbname = $config['city'][$_SESSION["form_city"]]["database"];
$table = $config['data']['table'];
$table_mapfeeder_side = $config['data']['table_mapfeeder_side'];
$table_mapfeeder_side_pid_col = $config['data']['table_mapfeeder_side_primary_id_column'];
$fields = implode(', ', $config['data']['fields']);
$titleField = $config['marker']['titleField'];
$dir = 'http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']);

# Build SQL SELECT statement including x and y columns
$sql = 'SELECT ' . $fields . ' FROM ' . $table;
$sql = str_replace('uploads', "'" . $dir . "/uploads/' || uploads", $sql);

$app = new Slim();

$app->get('/geojson', 'getFeatures');
$app->get('/geojson/:id', 'getFeature');
$app->get('/csv', 'getCSV');
$app->get('/kml', 'getKML');
$app->get('/gpx', 'getGPX');
$app->get('/comments/:id', 'getComments');
$app->get('/likes/:id', 'getLikes');
$app->get('/incrementlikes/:id', 'addLike');
$app->get('/decrementlikes/:id', 'addDislike');
$app->post('/comment', 'newComment');
$app->post('/feature', 'newFeature');
$app->run();

function verifyFormToken($form) {


  // check if a session is started and a token is transmitted, if not return an error
  if (!isset($_SESSION[$form.'_token'])) {
    return false;
  }
  // check if the form is sent with token in it
  if (!isset($_POST['token'])) {
    return false;
  }
  // compare the tokens against each other if they are still the same
  if ($_SESSION[$form.'_token'] !== $_POST['token']) {
    return false;
  }
  return true;
}

function formatGeoJSON($sql) {
  global $dbname;
  try {
    // Do not return comments for the MVPMPO map
    error_log('ERROR: '. "============", 0);
    error_log('ERROR: '. "formatGeoJSON()", 0);
    error_log('dbname: '. print_r($dbname,true), 0);
    # Build GeoJSON feature collection array
    $geojson = array(
       'type'      => 'FeatureCollection',
       'features'  => array()
    );
    // do not return the comment text values for the mfc_ak_mvpmpo map
    $db = getConnection();
    $stmt = $db->prepare($sql);
    $stmt->execute();
    # Loop through rows to build feature arrays
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $properties = $row;
        # Remove x and y fields from properties (optional)
        unset($properties['x']);
        unset($properties['y']);

        $feature = array(
          'type' => 'Feature',
          'geometry' => array(
            'type' => 'Point',
            'coordinates' => array(
              $row['x'],
              $row['y']
            )
          ),
          'properties' => $properties
        );
        # Add feature arrays to feature collection array
        array_push($geojson['features'], $feature);
    }
    header('Access-Control-Allow-Origin: *');
    header('Content-type: application/json');
    $db = null;
    echo json_encode($geojson, JSON_NUMERIC_CHECK);
  } catch(PDOException $e) {
    error_log('ERROR: '. $e->getMessage(), 0);
    echo '{"error":{"text":'. $e->getMessage() .'}}';
  }
}

function getFeatures() {
  global $sql;
  formatGeoJSON($sql);
}

function getFeature($id) {
  global $sql;
  $sql = $sql . ' WHERE id = ' . $id;
  formatGeoJSON($sql);
}

function getCSV() {
  global $sql, $table;
  try {
    $db = getConnection();
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $header = array();
    $csv = fopen('php://output', 'w');
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
      if(empty($header)){ // do it only once!
        $header = array_keys($row); // get the column names
        fputcsv($csv, $header); // put them in csv
      }
      fputcsv($csv, $row);
    }
    header('Access-Control-Allow-Origin: *');
    header('Content-type: text/csv');
    //header('Content-Disposition: attachment; filename="'.$table.'.csv"');
    $db = null;
  } catch(PDOException $e) {
    error_log('ERROR: '. $e->getMessage(), 0);
    echo '{"error":{"text":'. $e->getMessage() .'}}';
  }
}

function getKML() {
  global $sql, $table, $titleField;
  try {
    $db = getConnection();
    $stmt = $db->prepare($sql);
    $stmt->execute();
    # Create an array of strings to hold the lines of the KML file.
    $kml   = array(
      '<?xml version="1.0" encoding="UTF-8"?>'
    );
    $kml[] = '<kml xmlns="http://earth.google.com/kml/2.1">';
    $kml[] = '<Document>';
    $kml[] = '<Style id="generic">';
    $kml[] = '<IconStyle>';
    $kml[] = '<scale>1.3</scale>';
    $kml[] = '<Icon>';
    $kml[] = '<href>http://maps.google.com/mapfiles/kml/pushpin/red-pushpin.png</href>';
    $kml[] = '</Icon>';
    $kml[] = '<hotSpot x="20" y="2" xunits="pixels" yunits="pixels"/>';
    $kml[] = '</IconStyle>';
    $kml[] = '</Style>';

    # Loop through rows to build placemarks
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
      $data = $row;
      # Remove x and y fields from properties (optional)
      unset($data['x']);
      unset($data['y']);
      $kml[] = '<Placemark>';
      $kml[] = '<name>' . htmlentities($data[$titleField]) . '</name>';
      $kml[] = '<ExtendedData>';
      # Build extended data from fields
      foreach ($data as $key => $value) {
        $kml[] = '<Data name="' . $key . '">';
        $kml[] = '<value><![CDATA[' . $value . ']]></value>';
        $kml[] = '</Data>';
      }
      $kml[] = '</ExtendedData>';
      $kml[] = '<styleUrl>#generic</styleUrl>';
      $kml[] = '<Point>';
      $kml[] = '<coordinates>' . $row['x'] . ',' . $row['y'] . ',0</coordinates>';
      $kml[] = '</Point>';
      $kml[] = '</Placemark>';
    }

    $kml[] = '</Document>';
    $kml[] = '</kml>';
    $kmlOutput = join("\n", $kml);

    header('Content-type: application/vnd.google-earth.kml+xml kml');
    //header('Content-Disposition: attachment; filename="'.$table.'.kml"');
    //header ("Content-Type:text/xml");  // For debugging
    $db = null;
    echo $kmlOutput;
  } catch(PDOException $e) {
    error_log('ERROR: '. $e->getMessage(), 0);
    echo '{"error":{"text":'. $e->getMessage() .'}}';
  }
}

function getGPX() {
  global $sql, $table, $titleField;
  try {
    $db = getConnection();
    $stmt = $db->prepare($sql);
    $stmt->execute();
    # Create an array of strings to hold the lines of the GPX file.
    $gpx = array('<?xml version="1.0" encoding="UTF-8"?>');
    $gpx[] = '<gpx version="1.1" creator="GDAL 1.9.1" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:ogr="http://osgeo.org/gdal" xmlns="http://www.topografix.com/GPX/1/1" xsi:schemaLocation="http://www.topografix.com/GPX/1/1 http://www.topografix.com/GPX/1/1/gpx.xsd">';

    # Loop through rows to build placemarks
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
      $data = $row;
      # Remove x and y fields from properties (optional)
      unset($data['x']);
      unset($data['y']);
      $gpx[] = '<wpt lat="' . $row['y'] . '" lon="' . $row['x'] . '">';
      $gpx[] = '<name>' . htmlentities($data[$titleField]) . '</name>';
      $gpx[] = '<cmt>' . htmlentities($data[$titleField]) . '</cmt>';
      $gpx[] = '</wpt>';
    }
    $gpx[] = '</gpx>';
    $gpxOutput = join("\n", $gpx);
    header('Content-type: text/xml');
    //header('Content-Disposition: attachment; filename="'.$table.'.gpx"');
    $db = null;
    echo $gpxOutput;
  } catch(PDOException $e) {
    error_log('ERROR: '. $e->getMessage(), 0);
    echo '{"error":{"text":'. $e->getMessage() .'}}';
  }
}

function getLikes($id) {
  error_log('ERROR: '. "getLikes()", 0);
  $sql = "SELECT likes, dislikes FROM workorder.mfc_data WHERE id = " . $id;
  try {
    $db = getConnection();
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $res = $stmt->fetchAll(PDO::FETCH_OBJ);

    echo json_encode($res[0]);

    $db = null;
  } catch(PDOException $e) {
    error_log('ERROR: '. $e->getMessage(), 0);
    echo '{"error":{"text":'. $e->getMessage() .'}}';
  }
}


function incrementAttribute($id,$attribute) {
    error_log('ERROR: '. "============", 0);
    error_log('ERROR: '. "incrementAttribute()", 0);
    error_log('ERROR: '. print_r($id,true), 0);
    error_log('ERROR: '. print_r($attribute,true), 0);

  $sql = "UPDATE workorder.mfc_data SET " . $attribute . " = Coalesce(" . $attribute . ",0) + 1 WHERE id = " . $id;
  try {
    error_log('ERROR: '. print_r($sql,true), 0);
    $db = getConnection();
    $stmt = $db->prepare($sql);
    $stmt->execute();

    return getLikes($id);

    $db = null;
  } catch(PDOException $e) {
    error_log('ERROR: '. $e->getMessage(), 0);
    echo '{"error":{"text":'. $e->getMessage() .'}}';
  }
}

function addLike($id) {
    error_log('ERROR: '. "addLike()", 0);
  try {
    return incrementAttribute($id, "likes");

  } catch(PDOException $e) {
    error_log('ERROR: '. $e->getMessage(), 0);
    echo '{"error":{"text":'. $e->getMessage() .'}}';
  }
}

function addDislike($id) {
    error_log('ERROR: '. "addDislike()", 0);
  try {
    return incrementAttribute($id, "dislikes");

  } catch(PDOException $e) {
    error_log('ERROR: '. $e->getMessage(), 0);
    echo '{"error":{"text":'. $e->getMessage() .'}}';
  }
}

function getComments($id) {
  $sql = "SELECT id, name, comment, \"timestamp\"::date AS date FROM workorder.mfc_comments WHERE feature_id = " . $id . " ORDER BY \"timestamp\" DESC";
  try {
    $db = getConnection();
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $comments = $stmt->fetchAll(PDO::FETCH_OBJ);
    echo '{"comments": ' . json_encode($comments) . '}';
    $db = null;
  } catch(PDOException $e) {
    error_log('ERROR: '. $e->getMessage(), 0);
    echo '{"error":{"text":'. $e->getMessage() .'}}';
  }
}

function newComment() {
  if (verifyFormToken('form')) {
    $fields = array();
    $values = array();
    foreach ($_POST as $key => $value) {
      if ($key !== 'token') {
        $fields[] = trim($key);
        $values[] = trim($value);
      }
    }
    $sql = "INSERT INTO workorder.mfc_comments (" . implode(', ', $fields) . ") VALUES (" . ':' . implode(', :', $fields) . ");";
    try {
      $db = getConnection();
      $stmt = $db->prepare($sql);
      $stmt->execute($values);
      $db = null;
      echo "Success";
    } catch(PDOException $e) {
      error_log('ERROR: '. $e->getMessage(), 0);
      echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
  }
}

function newFeature() {
  try{
    global $table;
    global $table_mapfeeder_side;
    global $table_mapfeeder_side_pid_col;
    global $dbname;
    if (verifyFormToken('form')) {
      $fields = array();
      $values = array();
      foreach ($_POST as $key => $value) {
        if ($key !== 'token') {
          if (is_array($value)) {
            $value = implode(', ', $value);
          }
          $fields[] = trim($key);
          $values[] = trim($value);
        }
      }
      // error_log('ERROR: '. print_r($fields,true), 0);
      // error_log('ERROR: '. print_r($values,true), 0);
      try{
        $tableSplit = explode('.', $table);
        $subscriberName = $dbname;
        $moduleName = $tableSplit[0];
        $tableName = $tableSplit[1];
      } catch(Exception $e) {}

      $uploadsList = array();
      foreach ($_FILES['uploads']['error'] as $key => $error) {
        if ($error === UPLOAD_ERR_OK) {
          $filename = $_FILES['uploads']['name'][$key];
          $file_basename = substr($filename, 0, strripos($filename, '.'));
          $file_ext = substr($filename, strripos($filename, '.'));
          $newfilename = md5($file_basename) .rand() . $file_ext;
          $uploaddir = 'uploads/';
          $uploadfile = $uploaddir . $newfilename;
          move_uploaded_file($_FILES['uploads']['tmp_name'][$key], $uploadfile);
          $uploadsList[] = $newfilename;
        }
      }

      if (count($uploadsList) > 0) {
        $fields[] = 'uploads';
        $values[] = implode(',', $uploadsList);
      }
      
      $sql_insert = "INSERT INTO $table (" . implode(', ', $fields) . ") VALUES (" . ':' . implode(', :', $fields) . ");";
      
      $whereCondition = "";
      $num = count($values);
      $and = "";
      for ($i=0; $i < $num; $i++) {
        if ($i > 0) {
          $and = "AND ";
        }
        $whereCondition .= $and . $fields[$i] . " ='" . $values[$i] . "' ";
      }

      $newPID = false;
      try {
        // insert record
        $db = getConnection();
        $stmt = $db->prepare($sql_insert);
        $stmt->execute($values);
        $db = null;

        // select record to get new primary id
        $sql_getPID = "SELECT id from $table WHERE " . $whereCondition;
        $db = getConnection();
        $re = $db->query($sql_getPID);
        $retv = $re->execute();
        $val = $re->fetch();
        $newPID = $val[0];

        // sleep for one second to give the database trigger a change to run
        sleep(1);
        // get the PID of the record created by the trigger in the table that Mapfeeder will be checking
        $sql_get_mf_PID = "SELECT $table_mapfeeder_side_pid_col from $moduleName.$table_mapfeeder_side WHERE connect_id = $newPID";

        $db = getConnection();
        $re = $db->query($sql_get_mf_PID);
        $retv = $re->execute();
        $val = $re->fetch();

        $newActualPID = $val[0];
      } catch(Exception $e) {
        error_log('ERROR: '. $e->getMessage(), 0);
        $db = null;
      }
      $db = null;

      $uploaddir = 'uploads/';
      $mf_uploads_path = '/data2/mapfeeder-uploads/' . $subscriberName . "/" . $moduleName . "/" . $table_mapfeeder_side . "/" . $newActualPID;
      if((! is_dir($mf_uploads_path)) && (count($uploadsList) > 0)){
        mkdir($mf_uploads_path);
      }

      foreach ($uploadsList as $key => $value) {
        # for each copy to mapfeeder uploads dir
        $uploadfile = $uploaddir . $value;
        copy($uploadfile, $mf_uploads_path . "/" . $value);
      }
    }
  }catch (Exception $e) {
    error_log('Caught exception: ' . print_r($e->getMessage(),true), 0);
  }
}

function getConnection() {
  global $dbname;
  $dbh = new PDO('pgsql:host=localhost;port=5432;dbname=' . $dbname . '', 'mf_connect', 'mfconnect123');
  $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  return $dbh;
}
?>