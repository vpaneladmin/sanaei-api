#!/bin/bash

# Set TimeZone
sudo timedatectl set-timezone Asia/Tehran

# Install PHP 8.2 and required extensions
sudo apt update
sudo apt install curl software-properties-common -y
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install php8.2 php8.2-fpm php8.2-xml php8.2-curl php8.2-sqlite3 php8.2-dom -y

# Enable required PHP extensions
sudo phpenmod xml
sudo phpenmod curl
sudo phpenmod sqlite3
sudo phpenmod dom

# Restart PHP FPM
sudo systemctl restart php8.2-fpm

# Install Composer
sudo wget https://getcomposer.org/installer -O composer-installer.php
php composer-installer.php --filename=composer --install-dir=/usr/local/bin

# Get IP
echo "Enter Server IP:"
read ip
echo "php artisan serve --host="$ip" --port=8009" >> vpanel.sh

# Copy Service & Shell
sudo cp vpanel.service /etc/systemd/system
sudo cp vpanel.sh /usr/local/bin

# Install Files
cd /var/www/api
php /usr/local/bin/composer install

# Firewall Rule
ufw allow 8009

# Enable Services
sudo systemctl start vpanel.service
sudo systemctl enable vpanel.service
sudo systemctl daemon-reload