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

// Set an unique session name
session_name("consentUUID");

// Set session cookie details
// Parameters: Lifetime, Path, Domain, SECURE, HTTPonly
session_set_cookie_params(1440, "/", ".".$_SERVER["HTTP_HOST"], false, true);

// Start new or resume existing session
session_start();

// Generate a unique id for php session
if (function_exists('random_bytes')) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
}
else {
        $_SESSION['csrf'] = bin2hex(openssl_random_pseudo_bytes(32));
}

// check php version
if (version_compare(phpversion(), '8.0', '<')) {
	exit('This PHP Version '.phpversion().' is not supportet.');
}

// check if php function proc_open is usable
if( !function_exists("proc_open") ) {
	exit('The PHP function proc_open is not usable. Please modify your php.ini.');
}

// check if php function proc_get_status is usable
if( !function_exists("proc_get_status") ) {
	exit('The PHP function proc_get_status is not usable. Please modify your php.ini.');
}

// lazy config check/load
if (file_exists('LookingGlass/Config.php')) {
  require 'LookingGlass/Config.php';

  if (!isset($siteName, $siteUrl, $serverLocation, $testFiles)) {
    exit('Configuration variable/s missing. Please run configure.sh');
  }
} else {
  exit('Config.php does not exist. Please run configure.sh');
}

// check if php pdo for sqlite installed on the server
if( (!in_array("sqlite", PDO::getAvailableDrivers())) and (empty($sqlite3)) ) {
	exit('PDO driver for SQLite is not installed on this system (e.g. apt install php-sqlite3).');
}

// include multi language sytem
if ( (isset($_GET["lang"])) && (preg_match("/^[a-z]{2}\_[A-Z]{2}$/",$_GET["lang"])) ) {
	$locale = $_GET["lang"];
	setlocale(LC_MESSAGES, [$locale, $locale.".UTF-8"]);
	bindtextdomain("messages", "./locale");
	textdomain("messages");
	bind_textdomain_codeset("messages", 'UTF-8');
}
else {
	$locale = "en_US.UTF-8";
}

// Choose the defined theme
if ( !isset ($_SESSION["theme"])) {
	$_SESSION["theme"] = "light";
}

?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="<?php echo $_SESSION["theme"]; ?>">
  <head>

	<!-- General settings -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="LookingGlass - Open source PHP looking glass">
    <meta name="author" content="Daniel Wydler">

    <!-- Website title -->
    <title><?php echo $siteName; ?></title>

	<script>
	// Translation for JavaScript -->
	RunTest = "<?php echo _("Run Test"); ?>"
	Loading = "<?php echo _("Loading"); ?>"
	</script>

    <!-- Styles -->
    <link href="assets/css/bootstrap-5.3.2.min.css" rel="stylesheet">
  </head>
  <body>
	<!-- Container -->
	<div class="container">

		<!-- Header -->
		<div class="row" id="header">
			<div class="col-12">
				<div class="page-header pb-2 mt-4 mb-2 border-bottom">
					<h1><a id="title" href="<?php echo $siteUrl; ?>?lang=<?php echo $locale;?>"><?php echo $siteName; ?></a></h1>
				</div>
			</div>
		</div>
		<!-- /Header -->

		<!-- Network Information -->
		<div class="row">
			<div class="col-md-6 mt-2 mb-3">
				<div class="card">
					<div class="card-header">
						<?php
						echo _("Network information")." ";

						if ( (!empty($siteUrlv4)) &&  (!empty($siteUrlv6)) ) {
							echo "( <a href=\"".$siteUrl."?lang=".$locale."\">"._("DualStack")."</a> | 
								<a href=\"".$siteUrlv4."?lang=".$locale."\">"._("Only IPv4")."</a> |
								<a href=\"".$siteUrlv6."?lang=".$locale."\">"._("Only IPv6")."</a> )";
						}
						?>
					</div>
					<div class="card-body" style="height: 200px;">
						<p><?php echo _("Server Location"); ?>: <strong><?php echo $serverLocation; ?></strong></p>
						<p><?php echo _("IPv4 Address").": ".$ipv4; ?></p>
						<?php if (!empty($ipv6)) { echo "<p>"._("IPv6 Address").": ".$ipv6."</p>"; } ?>
						<p><?php echo _("Your IP Address"); ?>: <strong><a href="#tests" id="userip"><?php echo $_SERVER['REMOTE_ADDR']; ?></a></strong></p>	
					</div>
				</div>
			</div>
			<div class="col-md-6 mt-2 mb-3">
				<div class="card">
					<div class="card-header"><?php echo _("Network Test Files"); ?></div>
					<div class="card-body" style="height: 200px;">
						<h4><?php echo _("IPv4 Download Test"); ?></h4>
						<?php
						foreach ($testFiles as $val) {
							echo "<a href=\"";
							if ( !empty($siteUrlv4)) { echo $siteUrlv4; }
							else  { echo $siteUrl; }
							echo "/{$val}.bin\" class=\"btn btn-xs btn-secondary\">{$val}</a> ";
						}
	
						if (!empty($ipv6)) {
							echo "<h4>"._("IPv6 Download Test")."</h4>";
							foreach ($testFiles as $val) {
								echo "<a href=\"";
								if ( !empty($siteUrlv6)) { echo $siteUrlv6; }
								else  { echo $siteUrl; }
								echo "/{$val}.bin\" class=\"btn btn-xs btn-secondary\">{$val}</a> ";
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
			<div class="col-12">
				<div class="card">
					<div class="card-header"><?php echo _("Network tests"); ?></div>
					<div class="card-body">
						<form class="form-inline" id="networktest" action="#results" method="post">
							<div class="row">
								<div id="hosterror" class="form-group col">
									<div class="controls">
										<input id="host" name="host" type="text" class="form-control" placeholder="<?php echo _("Host or IP address"); ?>">
									</div>
								</div>
								<div class="form-group mr-1 col">
									<select name="cmd" class="form-select">
										<?php
										if ( ((!empty($ipv4)) or (!empty($ipv6))) and (empty($host)) ) { echo '<option value="host">host/host6</option>'; }
										if ( (!empty($ipv4)) and (empty($mtr)) ) { echo '<option value="mtr">mtr</option>'; }
										if ( (!empty($ipv6)) and (empty($mtr)) ) { echo '<option value="mtr6">mtr6</option>'; }
										if ( (!empty($ipv4)) and (empty($ping)) ) { echo '<option value="ping" selected="selected">ping</option>'; }
										if ( (!empty($ipv6)) and (empty($ping)) ) { echo '<option value="ping6">ping6</option>'; }
										if ( (!empty($ipv4)) and (empty($traceroute)) ) { echo '<option value="traceroute">traceroute</option>'; }
										if ( (!empty($ipv6)) and (empty($traceroute)) ) { echo '<option value="traceroute6">traceroute6</option>'; }
										?>
									</select>
								</div>
							
								<input type="hidden" name="csrf" value="<?php echo $_SESSION["csrf"]; ?>">
								<div class="col">
									<button type="submit" id="submit" name="submit" class="btn btn-success"><?php echo _("Run Test"); ?></button>
								</div>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>									
		<!-- /Network Tests -->

		<!-- Results -->
		<div class="row" id="results" style="display:none">
			<div class="col-12">
				<div class="card mt-3">
					<div class="card-header"><?php echo _("Results"); ?></div>
					<div class="card-body">
						<pre id="response" style="display:none"></pre>
					</div>
				</div>
			</div>
		</div>
		<!-- /Results -->

      	<!-- Footer -->
      	<footer class="footer mt-2 mb-2">
			<div class="row">
				<div class="col col-lg-auto me-0 pe-0">
					<?php echo _("Powered by").": "; ?><a target="_blank" href="https://github.com/telephone/LookingGlass">LookingGlass</a>&nbsp;|&nbsp;
				</div>
				<div class="col col-lg-auto mx-0 px-0">
					<?php echo _("Modified by").": "; ?><a target="_blank" href="https://github.com/dwydler/LookingGlass">Daniel Wydler</a>&nbsp;|&nbsp;
				</div>
				<div class="col col-lg-auto mx-0 px-0">
					<?php echo _("Language").": "; ?> <a href="?lang=en_US">EN</a> <a href="?lang=de_DE">DE</a>
				</div>
				<?php
				if (!empty($privacyurl)) {
					echo "<div class=\"col col-lg-auto mx-0 px-0\">";
					echo "&nbsp;|&nbsp;<a href=\"".$privacyurl."\" target=\"_blank\">"._("Privacy")."</a> ";
					echo "</div>";
				}
				if (!empty($imprinturl)) {
					echo "<div class=\"col col-lg-auto mx-0 px-0\">";
					echo "&nbsp;|&nbsp;<a href=\"".$imprinturl."\" target=\"_blank\">"._("Imprint")."</a>";
					echo "</div>";
				}
				?>
				<div class="col">
				</div>
				<div class="col col-lg-auto">
					<div class="d-inline-block">Darkmode:</div>
					<div class="form-check form-switch d-inline-block">
						<input class="form-check-input" type="checkbox" id="lightSwitch" style="cursor: pointer;"
						<?php if ($_SESSION["theme"] == "dark") { echo "checked"; } ?>>
						<label for="lightSwitch" class="form-check-label">On</label>
					</div>
				</div>
				<div class="col col-lg-1 text-end">
					<a href="#"><?php echo _("Back to top"); ?></a>
				</div>
			</div>
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