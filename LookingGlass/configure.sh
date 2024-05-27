#!/bin/bash
################################
# LookingGlass - User friendly PHP Looking Glass
#
# package     LookingGlass
# author      Nick Adams <nick@iamtelephone.com>
# copyright   2015 Nick Adams.
# link        http://iamtelephone.com
# license     http://opensource.org/licenses/MIT MIT License
################################

#######################
##                   ##
##     Functions     ##
##                   ##
#######################

##
# Create Config.php
##
function createConfig()
{
  cat > "$DIR/$CONFIG" <<EOF
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

// IPv4 address
\$ipv4 = '${IPV4}';

// IPv6 address (can be blank)
\$ipv6 = '${IPV6}';

// Rate limit
\$rateLimit = (int) '${RATELIMIT}';

// Site name (header)
\$siteName = '${SITE}';

// Site URL
\$siteUrl = '${URL}';

// Site URLv4
\$siteUrlv4 = '${URLV4}';

// Site URLv6
\$siteUrlv6 = '${URLV6}';

// Server location
\$serverLocation = '${LOCATION}';

// HOST
\$host = '${HOST}';

// MTR
\$mtr = '${MTR}';

// PING
\$ping = '${PING}';

// TRACEROUTE
\$traceroute = '${TRACEROUTE}';

// IPERF3
\$iperf3 = '${IPERF3}';

// SQLITE3
\$sqlite3 = '${SQLITE3}';

// Privacy Url
\$privacyurl = '${PRIVACYURL}';

// Imprint Url
\$imprinturl = '${IMPRINTURL}';

// Iperf Port
\$iperfport = '${IPERFPORT}';

// Test files
\$testFiles = array();
EOF

  for i in "${TEST[@]}"; do
    echo "\$testFiles[] = '${i}';" >> "$DIR/$CONFIG"
  done

  sleep 1
}

##
# Create/Load config varialbes
##
function config()
{
  sleep 1
  # Check if previous config exists
  if [ ! -f $CONFIG ]; then
    # Create config file
    echo 'Creating Config.php...'
    echo ' ' > "$DIR/$CONFIG"
  else
    echo 'Loading Config.php...'
  fi

  sleep 1

  # Read Config line by line
  while IFS="=" read -r f1 f2 || [ -n "$f1" ]; do
    # Read variables
    if [ "$(echo $f1 | head -c 1)" = '$' ]; then
      # Set Variables
      if [ $f1 = '$ipv4' ]; then
        IPV4="$(echo $f2 | awk -F\' '{print $(NF-1)}')"
      elif [ $f1 = '$ipv6' ]; then
        IPV6="$(echo $f2 | awk -F\' '{print $(NF-1)}')"
      elif [ $f1 = '$rateLimit' ]; then
        RATELIMIT=("$(echo $f2 | awk -F\' '{print $(NF-1)}')")
      elif [ $f1 = '$serverLocation' ]; then
        LOCATION="$(echo $f2 | awk -F\' '{print $(NF-1)}')"
      elif [ $f1 = '$siteName' ]; then
        SITE=("$(echo $f2 | awk -F\' '{print $(NF-1)}')")
      elif [ $f1 = '$siteUrl' ]; then
        URL=("$(echo $f2 | awk -F\' '{print $(NF-1)}')")
      elif [ $f1 = '$siteUrlv4' ]; then
			  URLV4=("$(echo $f2 | awk -F\' '{print $(NF-1)}')")
		  elif [ $f1 = '$siteUrlv6' ]; then
			  URLV6=("$(echo $f2 | awk -F\' '{print $(NF-1)}')")
      elif [ $f1 = '$privacyurl' ]; then
        PRIVACYURL=("$(echo $f2 | awk -F\' '{print $(NF-1)}')")
      elif [ $f1 = '$imprinturl' ]; then
        IMPRINTURL=("$(echo $f2 | awk -F\' '{print $(NF-1)}')")
      elif [ $f1 = '$iperfport' ]; then
        IPERFPORT=("$(echo $f2 | awk -F\' '{print $(NF-1)}')")
      elif [ $f1 = '$testFiles[]' ]; then
        TEST+=("$(echo $f2 | awk -F\' '{print $(NF-1)}')")
      fi
    fi
  done < "$DIR/$CONFIG"
}

##
# Create SQLite database
##
function database()
{
    if [ ! -f "${DIR}/ratelimit.db" ]; then
      echo
      echo 'Creating SQLite database...'
      sqlite3 ratelimit.db  'CREATE TABLE RateLimit (ip TEXT UNIQUE NOT NULL, hits INTEGER NOT NULL DEFAULT 0, accessed INTEGER NOT NULL);'
      sqlite3 ratelimit.db 'CREATE UNIQUE INDEX "RateLimit_ip" ON "RateLimit" ("ip");'
      read -e -p 'Enter the username of your webserver (E.g. www-data): ' -i "$(whoami)" USER
      read -e -p 'Enter the user group of your webserver (E.g. www-data): ' -i "$(id -g -n)" GROUP
      # Change owner of folder & DB
      if [[ -n $USER ]]; then
          if [[ -n $GROUP ]]; then
            chown $USER:$GROUP "${DIR}"
            chown $USER:$GROUP ratelimit.db
          else
            chown $USER:$USER "${DIR}"
            chown $USER:$USER ratelimit.db
          fi
      else
        cat <<EOF

##### IMPORTANT #####
Please set the owner of LookingGlass (subdirectory) and ratelimit.db
to that of your webserver:
chown user:group LookingGlass
chown user:group ratelimit.db
#####################
EOF
      fi
    fi
}

##
# Fix MTR on REHL based OS
##
function mtrFix()
{
  # Check permissions for MTR & Symbolic link
  if [ $(stat --format="%a" /usr/sbin/mtr) -ne 4755 ] || [ ! -f "/usr/bin/mtr" ]; then
    if [ $(id -u) = "0" ]; then
      echo 'Fixing MTR permissions...'
      chmod 4755 /usr/sbin/mtr
      ln -s /usr/sbin/mtr /usr/bin/mtr
    else
      cat <<EOF

##### IMPORTANT #####
You are not root. Please log into root and run:
chmod 4755 /usr/sbin/mtr
ln -s /usr/sbin/mtr /usr/bin/mtr
#####################
EOF
    fi
  fi
}

##
#
##

function StopScript() {
  echo 'Installation stopped :('
  echo
  exit
}
##
# Check and install script requirements
##
function requirements()
{
  sleep 1
  # Check for apt/yum
  if [ -f /usr/bin/apt ]; then
    # Check for root
    if [ $(id -u) != "0" ]; then
      INSTALL='sudo apt'
    else
      INSTALL='apt'
    fi
  elif [ -f /usr/bin/yum ]; then
    # Check for root
    if [ $(id -u) != "0" ]; then
      INSTALL='sudo yum'
    else
      INSTALL='yum'
    fi
  else
    cat <<EOF

##### IMPORTANT #####
Unknown Operating system. Install dependencies manually:
net-tools host mtr iputils-ping traceroute sqlite3

EOF
    StopScript

  fi

  # command ifconfig
  echo 'Checking for ifconfig...'
  if [ ! -f "/sbin/ifconfig" ] && [ ! -f "/bin/ifconfig" ] ; then
    echo "Please install: ${INSTALL} -y install net-tools."
    StopScript
  fi

  # command host
  echo 'Checking for host...'
  if [ ! -f "/usr/bin/host" ]; then
    HOST='NULL'

    if [ $INSTALL = 'yum' ]; then
      echo "Please install: ${INSTALL} -y install bind-utils."
    else
      echo "Please install: ${INSTALL} -y install host."
    fi
    echo
  fi  

  # command mtr
  echo 'Checking for mtr...'
  if [ ! -f "/usr/bin/mtr" ] && [ ! -f "/usr/sbin/mtr" ] ; then
    MTR='NULL'
    echo "Please install: ${INSTALL} -y install mtr."
    echo
  fi

  # command ping
  echo 'Checking for ping...'
  if [ ! -f "/bin/ping" ]; then
    PING='NULL'
    echo "Please install: ${INSTALL} -y install iputils-ping."
    echo
  fi

  # command traceroute
  echo 'Checking for traceroute...'
  if [ ! -f "/usr/bin/traceroute" ] && [ ! -f "/usr/sbin/traceroute" ]; then
    TRACEROUTE='NULL'
    echo "Please install: ${INSTALL} -y install traceroute."
    echo
  fi

  # command sqlite3
  echo 'Checking for sqlite3...'
  if [ ! -f "/usr/bin/sqlite3" ]; then
    SQLITE3='NULL'

    if [ "$INSTALL" = "yum" ]; then
      echo "Please install: ${INSTALL} -y install sqlite-devel."
    else
      echo "Please install: ${INSTALL} -y install sqlite3 php-sqlite3."
    fi
    echo
  fi

  # command iperf3
  echo 'Checking for iperf3...'
  if [ ! -f "/usr/bin/iperf3" ]; then
    IPERF3='NULL'

    if [ "$INSTALL" = "yum" ]; then
      echo "Please install: ${INSTALL} -y install iperf3."
    else
      echo "Please install: ${INSTALL} -y install iperf3."
    fi
    echo
  fi
}

##
# Setup parameters for PHP file creation
##
function setup()
{
  sleep 1

  # Local vars
  local IP4=$(ifconfig | sed -n '2 p' | awk '{print $2}')
  local IP6=$(ifconfig | sed -n '3 p' | awk '{print $2}')
  local LOC=
  local S=
  local T=
  local U=

  # User input
  read -e -p "Enter your website name (Header/Logo): " -i "$SITE" S
  read -e -p "Enter the public URL to this LG (e.g. https://lg.domain.de): " -i "$URL" U
  read -e -p "Enter the public IPv4 address of this server: " -i "$IP4" IP4
  read -e -p "Enter the public IPv6 address of this server: " -i "$IP6" IP6

  if [ -n "$IP4" ] && [ -n "$IP6" ]; then
    read -e -p "Enter the public URLv4 to this LG (e.g. https://4.lg.domain.de): " -i "$URLV4" UV4
    read -e -p "Enter the public URLv6 to this LG (e.g. https://6.lg.domain.de): " -i "$URLV6" UV6
  fi

  read -e -p "Enter the public URL to an Privacy (e.g. https://domain.de/pr): " -i "$PRIVACYURL" PRIURL
  read -e -p "Enter the public URL to an Imprint (e.g. https://domain.de/im): " -i "$IMPRINTURL" IMPURL
  read -e -p "Enter the servers location (e.g. DE, Frankfurt): " -i "$LOCATION" LOC


  if [ -z "$IPERF3" ]; then
    read -e -p "Enter the Port for the Ipref Server (e.g. 5201): " -i "$IPERFPORT" IPP
  fi

  read -e -p "Enter the size of test files in MB (e.g.: 500MB 1GB 10GB): " -i "${TEST[*]}" T

  if [ -z $SQLITE3 ]; then
    # Set default value
    YESNO="y"

    # Check if perviously set an rate limit
    if [ -z "$RATELIMIT" ]; then
      YESNO="n"  
    elif [ $RATELIMIT -eq "0" ]; then
      YESNO="n"
    fi
                
    read -e -p "Do you wish to enable rate limiting of network commands? (y/n): " -i "$YESNO" RATE
  fi

  # Assign entered value to script variable, can be left blank
  SITE=$S
  URL=$U
  IPV4=$IP4
  IPV6=$IP6
  URLV4=$UV4
  URLV6=$UV6
  PRIVACYURL=$PRIURL
  IMPRINTURL=$IMPURL
  LOCATION=$LOC
  IPERFPORT=$IPP

  # Rate limit
  if [[ "$RATE" = 'y' ]] || [[ "$RATE" = 'yes' ]]; then
    read -e -p "Enter the # of commands allowed per hour (per IP) [${RATELIMIT}]: " RATE
    if [[ -n $RATE ]]; then
      if [ "$RATE" != "$RATELIMIT" ]; then
        RATELIMIT=$RATE
      fi
    fi
  else
    RATELIMIT=0
  fi

  # Output blank line
  echo

  # Delete and/or create test files
  if [[ -z $T ]]; then
    # Delete old test files
    DeleteTestFiles

    # Output blank line
    echo

    # Assigned content of a variable to a new variable
    TEST=($T)
  fi
  
  if [[ -n $T ]]; then
    # Delete old test files
    DeleteTestFiles

    # Assigned content of a variable to a new variable
    TEST=($T)

    # Create new test files
    CreateTestFiles
  fi
}

##
# Delete test files
##
function DeleteTestFiles() {
  sleep 1
  echo "Removing old test files:"

   # Local var/s
  local A=0
  
  # Delete old test files
  local REMOVE=($(ls ../*.bin 2>/dev/null))
  for i in "${REMOVE[@]}"; do
    if [ -f "${i}" ]; then
      echo "Removing ${i}"
      rm "${i}"
      A=$((A+1))
      sleep 1
    fi
  done

  # No test files were created
  if [ $A = 0 ]; then
    echo 'Test files already removed...'
  fi
}
##
# Create test files
##
function CreateTestFiles() {
  sleep 1

  echo "Creating new test files:"

  # Local var/s
  local A=0

  # Check for and/or create test file
  for i in "${TEST[@]}"; do
    if [[ -n i ]] && [ ! -f "../${i}.bin" ]; then
      echo "Creating $i test file"
      shred --exact --iterations=1 --size="${i}" - > "../${i}.bin"
      A=$((A+1))
      sleep 1
    fi
  done

  # No test files were created
  if [ $A = 0 ]; then
    echo 'Test files already exist...'
  fi
}


###########################
##                       ##
##     Configuration     ##
##                       ##
###########################

# Clear terminal
clear

# Welcome message
cat <<EOF
########################################
#
# LookingGlass is a user-friendly script
# to create a functional Looking Glass
# for your network.
#
# Created by Nick Adams (telephone)
# http://iamtelephone.com
#
########################################

EOF

read -e -p "Do you wish to install LookingGlass? (y/n): " ANSWER

if [[ "$ANSWER" = 'y' ]] || [[ "$ANSWER" = 'yes' ]]; then
  cat <<EOF

###              ###
# Starting install #
###              ###

EOF
  sleep 1
else
  echo 'Installation stopped :('
  echo
  exit
fi

# Global vars
CONFIG='Config.php'
DIR="$(cd "$(dirname "$0")" && pwd)"
IPV4=
IPV6=
LOCATION=
RATELIMIT=
SITE=
URL=
URLV4=
URLV6=
HOST=
MTR=
PING=
TRACEROUTE=
IPERF3=
SQLITE3=
PRIVACYURL=
IMPRINTURL=
IPERFPORT=
TEST=()

# Install required scripts
echo 'Checking script requirements:'
requirements
read -p "Press any key to continue or CTRL+C to cancel..." -n1 -s
echo
# Read Config file
echo 'Checking for previous config:'
config
echo
# Create test files
CreateTestFiles
echo

# Follow setup
cat <<EOF

###                    ###
# Starting configuration #
###                    ###

EOF
echo 'Running setup:'
setup
echo
# Create Config.php file
echo 'Creating Config.php...'
createConfig

# Create DB
if [ -z $SQLITE3 ]; then
  database
fi

# Check for RHEL mtr
if [ "$INSTALL" = 'yum' ]; then
  mtrFix
fi
# All done
cat <<EOF

Installation is complete

EOF
sleep 1
