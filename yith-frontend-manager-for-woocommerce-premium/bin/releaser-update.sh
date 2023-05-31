#!/bin/bash

BLUE_BOLD='\033[1;34m'
COLOR_RESET='\033[0m'
LINE="--------------------------------------------------------"
RELEASER_BASE_URI='https://utilities.yithemes.com/plugin-releaser/'
status() {
  echo -e "${BLUE_BOLD}$1${COLOR_RESET}\n"
}

download() {
  FILE_URI="${RELEASER_BASE_URI}$1"
  if [ $(which curl) ]; then
    curl -s "${FILE_URI}" >"$2"
  elif [ $(which wget) ]; then
    wget -nv -O "$2" "${FILE_URI}"
  fi
}

status "$LINE\nRELEASER UPDATE\n$LINE"

# Change to the expected directory.
cd "$(dirname "$0")"
cd ..

download releaser-install.sh ./bin/releaser-install.sh

# Install
chmod +x ./bin/releaser-install.sh
./bin/releaser-install.sh

