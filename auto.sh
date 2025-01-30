#!/bin/bash

# Change Directory
cd /var

# Install unzip
sudo apt update
sudo apt install unzip

# Get Last Version
wget https://github.com/vpaneladmin/sanaei-api/archive/refs/heads/main.zip

# Unzip Source
unzip main.zip

# Rename Folder
mv /var/sanaei-api-main /var/www

# Change Directory
cd /var/www

# Run Install Bash
sudo /bin/bash install.sh