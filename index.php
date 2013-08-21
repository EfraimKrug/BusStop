<?php 
/*************************************************************
 * index.php:
 * 1) starts the session
 * 2) checks parameter for returning with errors...
 *		- if we got further along with no 'user name'
 * 3) retrieves cookies for returning visitor
 * 4) presents 2 forms
 * 		- 1 entry for user
 *		- 2 just to see who is here - you can get a list 
 *			without giving any info at all
 *************************************************************
 *** 1) starts the session   */
session_start();
/* 2) check parameter for returning with errors... */
$problemMessage = "";
if(isset($_GET['problem'])){
	if ($_GET['problem'] == "noname"){
		$problemMessage = "Forgive me, I don't know what to call you!";
		}
	elseif($_GET['problem'] == "nogeo"){
		$problemMessage = "Forgive me, I didn't get your geo location!";
	}
}
/* 3) retrieves cookies for returning visitor */
$user = "";
if(isset($_COOKIE['user'])){
	$user = $_COOKIE['user']; 
	}
?>

<!DOCTYPE html>
<!--[if IE 8]> 				 <html class="no-js lt-ie9" lang="en" > <![endif]-->
<!--[if gt IE 8]><!--> 
<html lang="en" > <!--<![endif]-->
<head><meta charset="utf-8" />
  <meta name="viewport" content="width=device-width" />
  <title>I Wonder Who Is Here!</title>

  <link rel="stylesheet" href="css/foundation.css" />
  <link rel="stylesheet" href="css/busstop.css" />
 
  <script src="js/vendor/custom.modernizr.js"></script>
  <script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script>
  <script type="text/javascript" src="./js/vendor/jquery.js" ></script>

<script>
$(document).ready(function(){
	//alert('done loading...');
	getLocation();
	});
</script>
<script>
/**
 * Cosmetic
 * jQuery: changes image from "just standing" to "active conversation"
 **/
$(document).ready(function() {
	//getLocation();
	$('.circular').mouseover(function()
		{
			jQuery('.circular').css("background-image", "url('./img/meeting04.jpg')");
		});
	$('.circular').mouseout(function()
		{
		
			$(".circular").stop().animate({opacity: 0},1000,function(){
					$(this).css({'background-image': "url('./img/BWCrowd.jpg')"})
					.animate({opacity: 1},{duration:1000});
				});
		});
	/*$('#Place1').val('kansas');*/
	//getLocation();
	});
</script>
<script>
/**
 * Functional javascript:
 * Google geolocoation: gets geo coordinates
 * looks up formatted address
 * loads both forms
 *
 * NOTE: TEST_ENVIRONMENT: just allows me to use dummy values to test
 **/

var TEST_ENVIRONMENT = 0;
function getLocation()
  {
  if(TEST_ENVIRONMENT == 1){
		$('#Latitude1').val(32.152637);
		$('#Latitude2').val(32.152637);
		$('#Longitude1').val(-87.736251);
		$('#Longitude2').val(-87.736251);
		return;
	}

  if (navigator.geolocation)
    {
    navigator.geolocation.getCurrentPosition(showPosition);
	//alert(position.coords.latitude);
	
		navigator.geolocation.getCurrentPosition(function(pos) {
			geocoder = new google.maps.Geocoder();
			
			var latlng = new google.maps.LatLng(pos.coords.latitude,pos.coords.longitude);	
				geocoder.geocode({'latLng': latlng}, function(results, status) {
				var result = results[0];
				document.getElementById("Place1").value =  result.formatted_address;
				document.getElementById("Place2").value =  result.formatted_address;
				});
			});
    }
  }
  
function showPosition(position)
  {
  $('#Latitude1').val(position.coords.latitude);
  $('#Latitude2').val(position.coords.latitude);
  $('#Longitude1').val(position.coords.longitude);
  $('#Longitude2').val(position.coords.longitude);
  }
  
</script>
</head>
<body>
	<div class="row">
		<div class="large-12 columns">
			<h2>Waiting For The (metaphorical) Bus</h2>
			<hr />
		</div>
	</div>

	<div class="row">
		<div class="large-8 columns">
			<div class="row">
				<div class="large-6 columns">
						<img src='./img/AirportSleep01.jpg'/>
						
				</div>
				<div class="large-6 columns">
					<p>What do you do while you're waiting?!?</p>
					<p>If you are waiting 20 minutes/day - that is
					100 minutes/week and over 6 hours/month!</p>
					<p>You <i>could</i> be meeting people!</p>
						<!-- <p><img src='./img/4KidsWaiting.jpg'/></p> -->
				</div>
			</div>

			<h3><hr></h3>

      <div class="row">
        <div class="large-6 columns">
			<?php if ($problemMessage != ""){
				echo "<p>$problemMessage</p>";
				}
			?>
			<form action='./php/post48.php' method='post'> 
			<?php echo "<input type='text' placeholder='How will someone recognize you? (6 words)' size=54 name='desc' value='"; 
			echo $user;
			echo "'>";
			?>
				<input type='text' size=17 placeholder='What should they call you? (name)' name='name' value=''>
				<input type='text' size=17 placeholder='Phone Number / Email' name='phone' value=''>
				<input type='hidden' id='Latitude1' name='Lat' value=''>
				<input type='hidden' id='Longitude1' name='Lon' value=''>
				<input type='hidden' id='Place1' name='Place1' value=''>
				<input class='small button' type='submit' value='Let them find you!'>
			</form>
        </div>
        <div class="large-6 columns">
				<form 	action='./php/WhoIsHere2.php' method='post'> 
				<input type='hidden' id='Latitude2' name='Lat2' value=''>
				<input type='hidden' id='Longitude2' name='Lon2' value=''>
				<input type='hidden' id='Place2' name='Place2' value=''>
				<input class='small button' type='submit' value='Anyone at the stop with you?'>
				</form>
				<div class="circular" ></div>
        </div>
      </div>
		</div>

		<div class="large-4 columns">
			<h4>The World's a Party!</h4>
			<div id="rightSide">We think this is a great idea... and we hope you do to! When you are standing someplace, waiting and there are others around who are also waiting... 
			maybe you can meet someone else! Check out if they logged onto our site... the logon matches day of the week and time of day and location - so 
			anyone who was waiting in about the same place, about the same time... if they signed in here you will see them!</div>

			<h4>Other Stuff</h4>
			<!--
			<p>You should check out:</p>
			<ul class="disc">
				<li><a href="http://foundation.zurb.com/docs">Foundation Documentation</a><br />Everything you need to know about using the framework.</li>
				<li><a href="http://github.com/zurb/foundation">Foundation on Github</a><br />Latest code, issue reports, feature requests and more.</li>
				<li><a href="http://twitter.com/foundationzurb">@foundationzurb</a><br />Ping us on Twitter if you have questions. If you build something with this we'd love to see it (and send you a totally boss sticker).</li>
			</ul>
			-->
		</div>
	</div>

  <script>
  document.write('<script src=' +
  ('__proto__' in {} ? 'js/vendor/zepto' : 'js/vendor/jquery') +
  '.js><\/script>')
  </script>
  
  <script src="js/foundation.min.js"></script>
  
  <script src="js/foundation/foundation.js"></script>
  
  <script src="js/foundation/foundation.interchange.js"></script>
  
  <script src="js/foundation/foundation.dropdown.js"></script>
  
  <script src="js/foundation/foundation.placeholder.js"></script>
  
  <script src="js/foundation/foundation.forms.js"></script>
  
  <script src="js/foundation/foundation.alerts.js"></script>
  
  <script src="js/foundation/foundation.magellan.js"></script>
  
  <script src="js/foundation/foundation.reveal.js"></script>
  
  <script src="js/foundation/foundation.tooltips.js"></script>
  
  <script src="js/foundation/foundation.clearing.js"></script>
  
  <script src="js/foundation/foundation.cookie.js"></script>
  
  <script src="js/foundation/foundation.joyride.js"></script>
  
  <script src="js/foundation/foundation.orbit.js"></script>
  
  <script src="js/foundation/foundation.section.js"></script>
  
  <script src="js/foundation/foundation.topbar.js"></script>
 
  
  <script>
    $(document).foundation();
  </script>
</body>
</html>
