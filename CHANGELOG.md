## v2.0.1 - 2024/05/26

### Security
* Fixed SRI Hashes for all css and js files

### Bugfixes
* Fixed URL to correct GitHub repository (README.md)


## v2.0.0 - 2024/05/26

### Security
* Added different Security Headers for Apache und NGINX configuration
* Added Cross-Site Request Forgery (CSRF) protection
* Source code adapted for PHP 8.x and higher. PHP 7.x not supported anymore.

### Changes
* Added  Darkmode to hte UI
* jQuery and Bootstrap CSS updated to the latest version
* Layout of the page completely revised
* Added support for the tool iPerf / iPerf3
* Added support for IPv4 oder IPv6 only operation
* README.md completely revised and expanded
* Added sample configuration for Apache and NGINX Webserver
* Added link for legal notice and privacy policy
* Added multi language support to the UI
* HTML source code is now W3C compliant
* Hyperlinks now open in a new tab/window by default.

### Bugs
* The ping function now only uses the IPv4 address of the target.
* If the FQDN does not have a valid IPv4 or IPv6 address, an error message is now output.


## 1.3.0 - 2015/01/25
* Fix RDNS XSS
* Fix '&nbsp;' being escaped by temporary patch (SHA a421a8e)
* Fix 'REQUEST_URI' XSS (URL is now hard-coded via config)
* Catch error when using IPv6 hostname with IPv4 command, and vice versa
* Added .htaccess (fixes readable subdirectory)
* Added sample Nginx configuration (fixes readable subdirectory)
* GNU shred to create test files (fixes gzip and ssl compression)
* Update configure.sh (add site url, sudo for centOS, and user:group chown)
* Update cerulean and united to Bootstrap v2.3.2
* Update readable and spacelab to Bootstrap v2.2.1
* Update Jquery to v1.11.2
* Update XMLHttpRequest.js

## 1.2.0 - 2012/10/01
* Multiple themes
* Rate limiting

## 1.1.0 - 2012/09/24
* Added --report-wide to MTR
* Fix MTR on RHEL OS'

## 1.0.0 - 2012/09/23
* Added network commands
* Automated install via bash script
* Long polling via output buffering