![Version](https://img.shields.io/badge/version-0.0.1-blue.svg?style=for-the-badge)
![Status](https://img.shields.io/badge/status-active-green.svg?style=for-the-badge)
![License](https://img.shields.io/badge/license-GPLv3-lightgrey.svg?style=for-the-badge)
![ActivityPub](https://img.shields.io/badge/protocol-ActivityPub-orange.svg?style=for-the-badge)
![Contributors](https://img.shields.io/github/contributors/0xd011f4ce/OurSpace?style=for-the-badge)
![Forks](https://img.shields.io/github/forks/0xd011f4ce/OurSpace?style=for-the-badge)
![Stargazers](https://img.shields.io/github/stars/0xd011f4ce/OurSpace?style=for-the-badge)
![Issues](https://img.shields.io/github/issues/0xd011f4ce/OurSpace?style=for-the-badge)

<br>

<div align="center">
  <h3 align="center">OurSpace</h3>

  <p align="center">A decentralised clone of MySpace based in the ActivityPub protocol.</p>
</div>

<details>
  <summary>Table of Contents</summary>

  <ol>
    <li>
      <a href="#about-the-project">About The Project</a>
      <ul>
        <li><a href="#built-with">Built With</a></li>
      </ul>
    </li>
    <li>
      <a href="#getting-started">Getting Started</a>
      <ul>
        <li><a href="#prerequisites">Prerequisites</a></li>
        <li><a href="#installation">Installation</a></li>
      </ul>
    </li>
    <li>
      <a href="#todo">TODO</a>
    </li>
  </ol>
</details>

## About The Project

![](img/OurSpaceHome.png)

**OurSpace** is a decentralised social networking platform inspired by the early concepts of MySpace but built using the ActivityPub protocol for enhanced privacy and interoperability. The goal of OurSpace is to provide users with a space to express themselves, share content, and connect with others in a federated environment that prioritises user privacy and data ownership.

### Built With

- ![Laravel](https://img.shields.io/badge/Laravel-v11-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
- ![PHP](https://img.shields.io/badge/PHP-v8-777BB4?style=for-the-badge&logo=php&logoColor=white)
- ![Redis](https://img.shields.io/badge/redis-%23DD0031.svg?style=for-the-badge&logo=redis&logoColor=white)

## Getting Started

OurSpace is meant to be easy to set up and run on your own server. Below are the steps to get started with OurSpace.

### Prerequisites

To use OurSpace we need to have installed PHP 8.2 or later and Redis. We can install them in a Debian-based system with the following commands:

```bash
sudo apt update
sudo apt install nginx mysql-server php php-fpm php-mbstring php-xml php-bcmath php-curl php-redis redis nginx zip unzip composer nodejs npm
```

Now, enable nginx, mysql and php-fom services:

```bash
sudo systemctl enable --now nginx mysql php-fpm redis-server
```

Create a database for OurSpace:

```bash
mysql
CREATE DATABASE ourspace;
CREATE USER 'ourspace_user'@'localhost' IDENTIFIED BY 'password';
GRANT ALL ON ourspace.* TO 'ourspace_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### Installation

Once you have all the required software installed and the database created, you can proceed to install OurSpace. First, clone the repository:

```bash
cd /var/www/html
git clone https://github.com/0xd011f4ce/OurSpace
```

Now, edit the `.env` file with your database, redis, and mail credentials. You can copy the `.env.example` file to `.env` and edit it with your favourite text editor.

Install all the dependencies:

```bash
composer install
npm install
```

Now, create the key and run the migrations:

```bash
php artisan key:generate
php artisan migrate
```

Build the styles:

```bash
npm run build
```

Give the proper permissions:

```bash
chown -R www-data:www-data /var/www/html/OurSpace
chmod -R 755 /var/www/html/OurSpace
```

Finally, create a virtual host for OurSpace. You can create a file in `/etc/nginx/sites-available/ourspace` with the following content:

```nginx
server {
    listen 80;
    server_name ourspace.lat;
    root /var/www/html/OurSpace/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-XSS-Protection "1; mode=block";
    add_header X-Content-Type-Options "nosniff";

    index index.html index.htm index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Enable the config:

```bash
sudo ln -s /etc/nginx/sites-available/ourspace.conf /etc/nginx/sites-enabled/
```

Restart nginx:

```bash
sudo systemctl restart nginx
```

And finally, link the storage to the public folder:

```bash
php artisan storage:link
```

## TODO:

For a list of planned features and improvements, please refer to the [TODO](TODO) file.
