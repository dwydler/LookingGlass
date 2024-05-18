<?php
/**
 * LookingGlass - User friendly PHP Looking Glass
 *
 * @package     LookingGlass
 * @author      Nick Adams <nick@iamtelephone.com>
 * @copyright   2015 Nick Adams.
 * @link        http://iamtelephone.com
 * @license     http://opensource.org/licenses/MIT MIT License
 */

// lazy config check/load
if (file_exists('LookingGlass/Config.php')) {
  require 'LookingGlass/Config.php';
  if (!isset($ipv4, $ipv6, $siteName, $siteUrl, $serverLocation, $testFiles)) {
    exit('Configuration variable/s missing. Please run configure.sh');
  }
} else {
  exit('Config.php does not exist. Please run configure.sh');
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- General settings -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="description" content="LookingGlass - Open source PHP looking glass" />
    <meta name="author" content="Daniel Wydler" />

    <!-- Website title -->
    <title><?php echo $siteName; ?></title>

    <!-- IE6-8 support of HTML elements -->
    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->

    <!-- Styles -->
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
  </head>
  <body>
	<!-- Container -->
	<div class="container">

		<!-- Header -->
		<div class="row" id="header">
			<div class="col-xs-12">
				<div class="page-header">
					<h1><a id="title" href="<?php echo $siteUrl; ?>"><?php echo $siteName; ?></a></h1>
				</div>
			</div>
		</div>
		<!-- /Header -->

		<!-- Network Information -->
		<div class="row">
			<div class="col-sm-6">
				<div class="panel panel-default">
					<div class="panel-heading">Network information</div>
					<div class="panel-body">
						<p>Server Location: <strong><?php echo $serverLocation; ?></strong></p>
						<p>IPv4 Address: <?php echo $ipv4; ?></p>
						<?php if (!empty($ipv6)) { echo '<p>IPv6 Address: '; echo $ipv6; echo '</p>'; } ?>
						<p>Your IP Address: <strong><a href="#tests" id="userip"><?php echo $_SERVER['REMOTE_ADDR']; ?></a></strong></p>
					</div>
				</div>
			</div>
			<div class="col-sm-6">
				<div class="panel panel-default">
					<div class="panel-heading">Network Test Files</div>
					<div class="panel-body">
						<h4>IPv4 Download Test</h4>
						<?php
							foreach ($testFiles as $val) 
							{
								echo "<a href=\"//{$ipv4}/{$val}.bin\" class=\"btn btn-xs btn-default\">{$val}</a> ";
							}
						?>

						<?php if (!empty($ipv6)) 
							{
								echo "<h4>IPv6 Download Test</h4>";
								foreach ($testFiles as $val) 
									{
										echo "<a href=\"//[{$ipv6}]/{$val}.bin\" class=\"btn btn-xs btn-default\">{$val}</a> ";
									}
								} 
							?>
					</div>
				</div>
			</div>
		</div>
		<!-- /Network Information -->

		<!-- Network Tests -->
		<div class="row">
			<div class="col-xs-12">
				<div class="panel panel-default">
					<div class="panel-heading">Network tests</div>
					<div class="panel-body">
						<form class="form-inline" id="networktest" action="#results" method="post">
							<div id="hosterror" class="form-group">
								<div class="controls">
									<input id="host" name="host" type="text" class="form-control" placeholder="Host or IP address">
								</div>
							</div>
							<div class="form-group">
								<select name="cmd" class="form-control">
									<option value="host">host</option>
                  					<option value="mtr">mtr</option>
                  					<?php if (!empty($ipv6)) { echo '<option value="mtr6">mtr6</option>'; } ?>
                  					<option value="ping" selected="selected">ping</option>
                  					<?php if (!empty($ipv6)) { echo '<option value="ping6">ping6</option>'; } ?>
                  					<option value="traceroute">traceroute</option>
                  					<?php if (!empty($ipv6)) { echo '<option value="traceroute6">traceroute6</option>'; } ?>
								</select>
							</div>

							<button type="submit" id="submit" name="submit" class="btn btn-success">Run Test</button>
						</form>
					</div>
				</div>
			</div>
		</div>									
		<!-- /Network Tests -->

		<!-- Results -->
		<div class="row" id="results" style="display:none">
			<div class="col-xs-12">
				<div class="panel panel-default">
					<div class="panel-heading">Results</div>
					<div class="panel-body">
						<pre id="response" style="display:none"></pre>
					</div>
				</div>
			</div>
		</div>
		<!-- /Results -->

      	<!-- Footer -->
      	<footer class="footer">
			<p class="pull-right">
				<a href="#">Back to top</a>
        	</p>

        	<p>Powered by <a target="_blank" href="http://github.com/telephone/LookingGlass">LookingGlass</a> | Modified by <a target="_blank" href="https://github.com/dwydler/LookingGlass">Daniel Wydler</a></p>
      	</footer>
		<!-- /Footer -->

    </div>
    <!-- /Container -->

    <!-- Javascript -->
    <script src="assets/js/jquery-3.7.0.min.js"></script>
    <script src="assets/js/LookingGlass.min.js"></script>
    <script src="assets/js/XMLHttpRequest.min.js"></script>
  </body>
</html>