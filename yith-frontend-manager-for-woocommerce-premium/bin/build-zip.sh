#!/bin/bash

BLUE_BOLD='\033[1;34m';
COLOR_RESET='\033[0m';
status () {
	echo -e "\n${BLUE_BOLD}$1${COLOR_RESET}\n"
}

# Exit if any command fails.
set -e

# Change to the expected directory.
cd "$(dirname "$0")"
cd ..

# Plugin Framework update
status "Plugin Framework and Upgrade updating..."
git submodule update --init --recursive && git submodule foreach --recursive git pull origin master

# Run build.
status "Build: uglify JS, generate POT and download translations..."
npm run build

# Generate the plugin zip file.
npm run build-zip:package