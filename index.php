<?php
  session_start();
  $json = file_get_contents("config.json");
  $config = json_decode($json, true);
  function generateFormToken($form) {
    $token = md5(uniqid(microtime(), true));
    // Write the generated token to the session variable to check it against the hidden field when the form is sent
    $_SESSION[$form . "_token"] = $token;
    $_SESSION[$form . "_city"] = $_GET["city"];
    if (isset($_GET["map"])) {
      $_SESSION[$form . "_city"] = $_GET["map"];
    }
    return $token;
  }
  $newToken = generateFormToken("form");
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="initial-scale=1,user-scalable=no,maximum-scale=1,width=device-width">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="description" content=<?php echo '"' . $config["app"]["description"] . '"';?>>
    <meta name="author" content=<?php echo '"' . $config["app"]["author"] . '"';?>>
    <title><?php echo $config["app"]["title"]; ?></title>

    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/fancybox/2.1.5/jquery.fancybox.min.css">
    
    <!-- What was working -->
    <!-- <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.3/leaflet.css"> -->

    <!-- <link rel="stylesheet" href="//api.tiles.mapbox.com/mapbox.js/plugins/leaflet-markercluster/v0.4.0/MarkerCluster.css"> -->
    <!-- <link rel="stylesheet" href="//api.tiles.mapbox.com/mapbox.js/plugins/leaflet-markercluster/v0.4.0/MarkerCluster.Default.css"> -->
    <!-- <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css" integrity="sha256-kLaT2GOSpHechhsozzB+flnD+zUyjE2LlfWPgU04xyI=" crossorigin="" /> -->
    
    <!-- <link rel="stylesheet" href="assets/plugins/MarkerCluster/MarkerCluster.css"> -->
    <!-- <link rel="stylesheet" href="assets/plugins/MarkerCluster/MarkerCluster.Default.css"> -->
    <!-- <link rel="stylesheet" href="https://unpkg.com/leaflet@1.3.1/dist/leaflet.css" integrity="sha512-Rksm5RenBEKSKFjgI3a41vrjkw4EVPlJ3+OiI65vTjIdo9brlAacEuKOiQ5OFh7cOI1bkDwLqdLw3Zg0cRJAAQ==" crossorigin="" /> -->
    <!-- <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.3.0/dist/MarkerCluster.css" /> -->
    <!-- <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.3.0/dist/MarkerCluster.Default.css" /> -->


    <!-- From the leaflet marker cluster package example -->
    <!-- <link rel="stylesheet" href="https://unpkg.com/leaflet@1.0.3/dist/leaflet.css" integrity="sha512-07I2e+7D8p6he1SIM+1twR5TIrhUQn9+I6yjqD53JQjFiMf8EtC93ty0/5vJTZGF8aAocvHYNEDJajGdNx1IsQ==" crossorigin="" /> -->
    <!-- <script src="https://unpkg.com/leaflet@1.0.3/dist/leaflet-src.js" integrity="sha512-WXoSHqw/t26DszhdMhOXOkI7qCiv5QWXhH9R7CgvgZMHz1ImlkVQ3uNsiQKu5wwbbxtPzFXd1hK4tzno2VqhpA==" crossorigin=""></script> -->

    <!-- <script src="assets/Leaflet.markercluster-1.4.1/leaflet.markercluster.js"></script> -->
    <!-- <link rel="stylesheet" href="https://unpkg.com/leaflet@1.0.3/dist/leaflet.css"> -->
    <!-- <link rel="stylesheet" href="assets/Leaflet.markercluster-1.4.1/MarkerCluster.css" /> -->
    <!-- <link rel="stylesheet" href="assets/Leaflet.markercluster-1.4.1/MarkerCluster.Default.css" /> -->


    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.3.1/dist/leaflet.css" integrity="sha512-Rksm5RenBEKSKFjgI3a41vrjkw4EVPlJ3+OiI65vTjIdo9brlAacEuKOiQ5OFh7cOI1bkDwLqdLw3Zg0cRJAAQ==" crossorigin="" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.3.0/dist/MarkerCluster.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.3.0/dist/MarkerCluster.Default.css" />

    <script src="https://unpkg.com/leaflet@1.3.1/dist/leaflet.js" integrity="sha512-/Nsx9X4HebavoBvEBuyp3I7od5tA0UzAxs+j83KgC8PU0kgB4XiK4Lfe4y4cgBtaRJQEIFCW+oC506aPT2L1zw==" crossorigin=""></script>
    <script src="https://unpkg.com/leaflet.markercluster@1.3.0/dist/leaflet.markercluster.js"></script>






    
    <link rel="stylesheet" href="assets/css/app.css">

    <link rel="apple-touch-icon" sizes="76x76" href="assets/img/favicons/favicon-76.png">
    <link rel="apple-touch-icon" sizes="120x120" href="assets/img/favicons/favicon-120.png">
    <link rel="apple-touch-icon" sizes="152x152" href="assets/img/favicons/favicon-152.png">
    <link rel="icon" sizes="196x196" href="assets/img/favicons/favicon-196.png">
    <link rel="icon" type="image/x-icon" href="assets/img/favicon.ico">
  </head>

  <body>
    <div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
      <div class="container-fluid">
        <div class="navbar-header">
          <div class="navbar-icon-container">
            <a href="#" class="navbar-icon pull-right visible-xs" id="nav-btn"><i class="fa fa-bars fa-lg fa-white"></i></a>
            <a href="#" class="navbar-icon pull-right visible-xs search-btn"><i class="fa fa-search fa-lg fa-white"></i></a>
            <a href="#" class="navbar-icon pull-right visible-xs new-item-btn" style="margin-left: 5px;"><i class="fa fa-plus fa-lg fa-red"><span style="font-size:75%;font-weight:bold;"> Add</span></i></a>
          </div>
          <div class="navbar-brand"><?php if ($config["defaultNavbar"]["icon"]) {echo "<img src='" . $config["defaultNavbar"]["icon"] . "'>";} echo $config["defaultNavbar"]["title"]; ?></div>
        </div>
        <div class="navbar-collapse collapse">
          <ul class="nav navbar-nav">
            <li><a href="#" data-toggle="collapse" data-target=".navbar-collapse.in" id="about-btn"><i class="fa fa-question-circle fa-white"></i>&nbsp;&nbsp;About</a></li>
            <li class="dropdown">
              <a class="dropdown-toggle" id="toolsDrop" href="#" role="button" data-toggle="dropdown"><i class="fa fa-globe fa-white"></i>&nbsp;&nbsp;Tools <b class="caret"></b></a>
              <ul class="dropdown-menu">
                <li><a href="#" data-toggle="collapse" data-target=".navbar-collapse.in" id="full-extent-btn"><i class="fa fa-arrows-alt"></i>&nbsp;&nbsp;Zoom To Full Extent</a></li>
                <li><a href="#" data-toggle="collapse" data-target=".navbar-collapse.in" id="refresh-btn"><i class="fa fa-refresh"></i>&nbsp;&nbsp;Refresh Data</a></li>
              </ul>
            </li>

            <li class="hidden-xs"><a href="#" data-toggle="collapse" data-target=".navbar-collapse.in" class="search-btn"><i class="fa fa-search fa-white"></i>&nbsp;&nbsp;Search Comments</a></li>
          </ul>
          <div class="navbar-form navbar-right hidden-xs">
            <button type="submit" class="btn btn-primary new-item-btn"><i class="fa fa-plus"></i>&nbsp;&nbsp;<?php echo $config["text"]["newItem"]; ?></button>
          </div>
        </div><!--/.navbar-collapse -->
      </div>
    </div>
    <div class="main-container">
      <div id="sidebar">
        <div class="sidebar-wrapper">
          <div class="panel panel-default" id="features">
            <div class="panel-heading">
              <h3 class="panel-title">Search
              <button type="button" class="btn btn-xs btn-default pull-right" id="sidebar-hide-btn"><i class="fa fa-chevron-left"></i></button></h3>
            </div>
            <div class="panel-body">
              <div class="row">
                <div class="col-xs-8">
                  <input type="text" class="form-control search" placeholder="Search Comments" />
                </div>
                <div class="col-xs-4">
                  <button type="button" class="btn btn-primary pull-right sort" data-sort="feature-name" id="sort-btn"><i class="fa fa-sort"></i>&nbsp;&nbsp;Sort</button>
                </div>
              </div>
            </div>
            <div class="sidebar-table">
              <table class="table table-hover" id="feature-list">
                <thead class="hidden">
                  <tr>
                    <th>Name</th>
                  </tr>
                  <tr>
                    <th>Icon</th>
                  </tr>
                </thead>
                <tbody class="list"></tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
      <div id="map"></div>
    </div>
    <div id="loading">
      <div class="loading-indicator">
        <div class="progress progress-striped active">
          <div class="progress-bar progress-bar-info progress-bar-full"></div>
        </div>
      </div>
    </div>

    <div class="modal fade" id="aboutModal" tabindex="-1" role="dialog">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <button class="close" type="button" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title text-primary"><?php echo $config["defaultAboutModal"]["title"]; ?></h4>
          </div>
          <div class="modal-body">
            <div><?php echo $config["defaultAboutModal"]["text"]; ?></div>
	    <!-- <div class="well well-sm" id="about"><a href="<?php echo $config["city"][$_SESSION["form_city"]]["web"]; ?>" target="_city"><?php echo $config["city"][$_SESSION["form_city"]]["name"]; ?></a></div> -->
	    <div class="well well-sm hidden" id="help"><?php echo $config["defaultAboutModal"]["help"]; ?></div>
	    <div class="well well-sm hidden" id="thanks"><?php echo $config["defaultAboutModal"]["thanks"]; ?></div>
	    <div class="well well-sm hidden" id="attribution"></div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>

    <div class="modal fade" id="questionModal" tabindex="-1" role="dialog">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <button class="close" type="button" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title text-primary">Submit a New Issue</h4>
          </div>
          <div class="modal-body">
            <div><strong>Is the issue at your current location?</strong></div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default cancel-new-item-btn" data-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-default no-new-item-btn" data-dismiss="modal">No</button>
            <button type="button" class="btn btn-default yes-new-item-btn" data-dismiss="modal">Yes</button>
          </div>
        </div>
      </div>
    </div>

    <div class="modal fade" id="formModal" data-backdrop="static" tabindex="-1" role="dialog">
      <div class="modal-dialog">
        <div class="modal-content">
          <form class="ws-validate" id="data-form" method="post" action="" enctype="multipart/form-data" autocomplete="off">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
              <h4 class="modal-title text-primary"><?php echo $config["text"]["newItem"]; ?></h4>
            </div>
            <div class="modal-body">
              <div class="form-group hidden">
                <label for="token">token</label>
                <input type="text" class="form-control" name="token" value="<?php echo $newToken; ?>">
              </div>
              <?php include("form.html") ?>
			  <div class="panel panel-default">
                <div class="panel-heading">
                  <h3 class="panel-title">Location</h3>
                </div>
                <div class="panel-body">
                  <div class="row">
                    <div class="col-xs-6">
                      <div class="form-group">
                        <label for="lat">Latitude</label>
                        <input type="text" class="form-control" id="lat" name="lat" readonly>
                      </div>
                    </div>
                    <div class="col-xs-6">
                      <div class="form-group">
                        <label for="lng">Longitude</label>
                        <input type="text" class="form-control" id="lng" name="lng" readonly>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-danger pull-left" data-dismiss="modal" id="cancel-btn">Cancel</button>
              <button type="submit" class="btn btn-default" id="editLocation" data-dismiss="modal" title="Edit location"><i class="fa fa-map-marker"></i>&nbsp;Edit</button>
              <button type="submit" class="btn btn-primary pull-right" title="Submit form">Submit</button>
            </div>
          </form>
        </div>
      </div>
    </div> <!-- /#formModal -->

    <div class="modal fade" id="featureModal" tabindex="-1" role="dialog">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <button class="close" type="button" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title text-primary" id="feature-title"></h4>
          </div>
          <div class="modal-body" id="feature-info">
            <!-- Nav tabs -->
            <ul class="nav nav-tabs" role="tablist" id="feature-tabs">
              <li class="active"><a href="#info-tab" role="tab" data-toggle="tab"><i class="fa fa-info-circle"></i>&nbsp;Info</a></li>
              <li id="comments-tab-button"><a href="#comments-tab" role="tab" data-toggle="tab"><i class="fa fa-comments"></i>&nbsp;Comments</a></li>
            </ul>
            <!-- Tab panes -->
            <div class="tab-content">
              <div class="tab-pane active" id="info-tab">
              </div>
              <div class="tab-pane" id="comments-tab">
                <div id="comment-panes"></div>
                <div class="panel panel-primary">
                  <div class="panel-heading">
                    <h3 class="panel-title">Leave a comment</h3>
                  </div>
                  <div class="panel-body">
                    <form class="form-horizontal ws-validate" role="form" id="comment-form" method="post" action="" enctype="multipart/form-data">
                      <div class="form-group hidden">
                        <label for="token">token</label>
                        <input type="text" class="form-control" id="token" name="token" value="<?php echo $newToken; ?>">
                      </div>
                      <div class="form-group hidden">
                        <label for="feature_id" class="col-sm-2 control-label">Feature Id</label>
                        <div class="col-sm-10">
                          <input type="text" class="form-control" name="feature_id">
                        </div>
                      </div>
                      <div class="form-group">
                        <label for="name" class="col-sm-2 control-label">Name:</label>
                        <div class="col-sm-10">
                          <input type="text" class="form-control" name="name" data-errormessage='{"valueMissing": ""}'>
                        </div>
                      </div>
                      <div class="form-group">
                        <label for="email" class="col-sm-2 control-label">Email:</label>
                        <div class="col-sm-10">
                          <input type="email" class="form-control" name="email" data-errormessage='{"valueMissing": "", "typeMismatch": "Incorrect email format"}'>
                        </div>
                      </div>
                      <div class="form-group">
                        <label for="comment" class="col-sm-2 control-label">Comment:</label>
                        <div class="col-sm-10">
                          <textarea class="form-control" rows="3" name="comment" data-errormessage='{"valueMissing": "This is a required field"}' required></textarea>
                        </div>
                      </div>
                      <button type="submit" class="btn btn-primary pull-right">Submit Comment</button>
                    </form>
                  </div> <!-- /.panel-body -->
                </div> <!-- /.panel-primary -->
              </div> <!-- /.tab-pane -->
            </div> <!-- /.tab-content -->
          </div> <!-- /.modal-body -->
          <div class="modal-footer">
            <button type="button" class="btn btn-default" id="ThumbsUpButton">
              <i class="fa fa-thumbs-up"></i><div id="ThumbsUpNumber"  style="float: right; margin-left: 6px;">0</div>
            </button>
            <button type="button" class="btn btn-default" id="ThumbsDownButton">
              <i class="fa fa-thumbs-down"></i><div id="ThumbsDownNumber"  style="float: right; margin-left: 6px;">0</div>
            </button>
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
          </div> <!-- /.modal-footer -->
        </div>
      </div>
    </div>
    <script src="//maps.googleapis.com/maps/api/js?v=3.exp&key=AIzaSyCdsgRtty0oOxSeLd3S0NGs4qrkMkK37_Y"></script>
    <script src="//code.jquery.com/jquery-2.1.3.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/webshim/1.15.7/minified/polyfiller.js"></script>
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/fancybox/2.1.5/jquery.fancybox.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/list.js/1.1.1/list.min.js"></script>
    <!-- What was working -->
    <!-- <script src="//cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.3/leaflet.js"></script> -->
    <!-- <script src="//api.tiles.mapbox.com/mapbox.js/plugins/leaflet-markercluster/v0.4.0/leaflet.markercluster.js"></script> -->

    <!-- <script src="https://unpkg.com/leaflet@1.9.3/dist/leaflet.js" integrity="sha256-WBkoXOwTeyKclOHuWtc+i2uENFpDZ9YPdf5Hf+D7ewM=" crossorigin=""></script> -->
    <!-- <script src="assets/plugins/MarkerCluster/leaflet.markercluster.js"></script> -->

    <!-- From the leaflet marker cluster package example -->
    <!-- <script src="https://unpkg.com/leaflet@1.0.3/dist/leaflet-src.js"></script> -->
    <!-- <script src="assets/Leaflet.markercluster-1.4.1/leaflet.markercluster.js"></script> -->
    
    <!-- <script src="https://unpkg.com/leaflet@1.3.1/dist/leaflet.js" integrity="sha512-/Nsx9X4HebavoBvEBuyp3I7od5tA0UzAxs+j83KgC8PU0kgB4XiK4Lfe4y4cgBtaRJQEIFCW+oC506aPT2L1zw==" crossorigin=""></script> -->
    <!-- <script src="https://unpkg.com/leaflet.markercluster@1.3. 0/dist/leaflet.markercluster.js"></script> -->
    <script src="//api.tiles.mapbox.com/mapbox.js/plugins/leaflet-locatecontrol/v0.24.0/L.Control.Locate.js"></script>

    <script src="https://unpkg.com/leaflet.vectorgrid@latest/dist/Leaflet.VectorGrid.bundled.js"></script>
    
    <script src="https://twitter.github.io/typeahead.js/releases/latest/typeahead.bundle.js"></script>

    <script src="assets/js/geocode.js"></script>
    <script src="assets/js/app.js"></script>
    <script src="assets/plugins/jquery.cookie.js"></script>

    <!-- fails because of mixed content type error, cannot get the file via https and http fails -->
    <!-- <script src="//matchingnotes.com/javascripts/leaflet-google.js"></script> -->
    <script src="assets/js/leaflet-google.js"></script>


    <?php
      $urlCityName = $_SESSION["form_city"];
      $configCity = json_encode($config["city"][$_SESSION["form_city"]]);
      echo "<script>window.urlCityName = '$urlCityName';window.configCity = $configCity;</script>";
    ?>

/*
    <script>
        // Parameterize the URL query string
        var urlParams={};
        if (location.search) {
            var parts = location.search.substring(1).split('&');

            for (var i = 0; i < parts.length; i++) {
                var nv = parts[i].split('=');
                if (!nv[0]) continue;
                urlParams[nv[0]] = nv[1] || true;
            }
        }
        if (urlParams.map) {
          console.log('map param found, setting to city so this will work with minimal changes.');
          urlParams.city = urlParams.map;
        }

        if (urlParams.city === undefined || urlParams.city === true) {
          alert("WARNING: No city or map has been supplied");
        } else {
    	    checkCity(decodeURIComponent(urlParams.city));
        }

        console.log(window.urlCityName);
        console.log(window.configCity);
        if(window.configCity.hideNewItemButton == true){
          $('.new-item-btn').hide();
        }

    </script>
*/
  </body>
</html>
