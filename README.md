# LookingGlass

## Overview

LookingGlass is a user-friendly PHP based looking glass that allows the public (via a web interface) to execute network
commands on behalf of your server.

## Demo
[Looking Glass](https://lg.daniel.wydler.eu/)

Demo is running on a Cloud Server of Hetzner Online GmbH. 

## Features

* Automated configuration via bash script
* IPv4 & IPv6 support (DualStack, Only IPv4 or IPv6)
* Live output via long polling
* Multi Language supported. New Languages are Welcome!
* Rate limiting of network commands
* Dark Mode

## Implemented commands

* host (IPv4 und IPv6)
* mtr (IPv4 und IPv6)
* ping (IPv4 und IPv6)
* traceroute  (IPv4 und IPv6)
* iPerf/iPerf3 (IPv4 und IPv6)

> [!IMPORTANT]
> IPv4 commands will only available if your server has external IPv4 address.  
> IPv6 commands will only available if your server has external IPv6 address.

## Requirements

* Debian/Ubuntu Server
* PHP >= 8.0
* PHP PDO with SQLite driver (required for rate-limit), `apt install -y php-sqlite3`
* SSH/Terminal access (able to install commands/functions if non-existent)
* Make sure the PHP function proc_open and proc_get_status is usable

## Install Looking Glass

1. Clone the repository to the correct folder:
  ```
   git clone -b customize https://github.com/dwydler/LookingGlass.git /var/www/html/LookingGlass
   git -C /var/www/html/LookingGlass checkout $(git -C /var/www/html/LookingGlass tag | tail -1)
  ```
2. Navigate to the `LookingGlass` subdirectory in terminal:
  ```
  cd /var/www/html/LookingGlass/LookingGlass/
  ```  
3. Run `bash configure.sh`.
4. Follow the instructions and `configure.sh` will take care of the rest.
	- Note: Re-enter test files to create random test files from `GNU shred`.

_Forgot a setting? Simply run the `bash configure.sh` script again._

## Update Looking Glass
1. When you're ready to update the code, you can checkout the latest tag:
  ```
   ( cd /var/www/html/LookingGlass && git fetch && git checkout $(git tag | tail -1) )
  ```
2. . Navigate to the `LookingGlass` subdirectory in terminal:
  ```
  cd /var/www/html/LookingGlass/LookingGlass/
  ```  
3. Run `bash configure.sh`.
4. Follow the instructions and `configure.sh` will take care of the rest.

## Setup iperf/iperf3 as a service (optional)
If you want to use the iPerf / iPerf3 tool and have installed it, it must also be configured as a systemd service. This means that the tool is automatically started every time the server is restarted. This is described here. **Starting with Debian 12/Ubuntu 24.04, the service is set up automatically.**

1. Code for the systemd service file:
```
cat <<- EOF > /etc/systemd/system/iperf3.service
[Unit]
Description=iperf3 server
After=syslog.target network.target auditd.service

[Service]
ExecStart=/usr/bin/iperf3 -s

[Install]
WantedBy=multi-user.target
EOF
```
2. Enable the service: `systemctl enable iperf3.service`.
4. Start the service: `systemctl start iperf3.service`.
5. Check the status of the service: `systemctl status iperf3.service`.

## Apache
1. Install Apache + PHP-FPM: `apt update && apt install -y apache2 php-fpm`.
2. Enable the "headers", “proxy_fcgi” and “proxy” modules `a2enmod headers proxy_fcgi proxy && systemctl restart apache2`.
3. A rudimentary configuration for Apache can be found here: [HTTP setup](misc/lookingglass-http.apache.conf)

> [!NOTE]
> An .htaccess is included which protects the rate-limit database, disables indexes, and disables gzip on test files.
Ensure `AllowOverride` is on for .htaccess to take effect. Output buffering __should__ work by default.

For an HTTPS setup, please visit: [Mozilla SSL Configuration Generator](https://ssl-config.mozilla.org/)

## Nginx

1. Install NGINX  + PHP-FPM: `apt update && apt install -y nginx php-fpm`
3. To enable output buffering, and disable gzip on test files please refer to the provided configuration: [HTTP setup](misc/lookingglass-http.nginx.conf). The provided config is setup for LookingGlass to be on a subdomain/domain root.

For an HTTPS setup please visit: [Mozilla SSL Configuration Generator](https://ssl-config.mozilla.org/)

## License

Code is licensed under MIT Public License.

* If you wish to support my efforts, keep the "Powered by LookingGlass" link intact.
