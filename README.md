This is a PHP app in three Docker containers : PHP8.2-Apache, mysql and phpmyadmin
==============================================================================

For DEMO :

1. Lancer Docker Desktop
2. taper docker-compose up dans un CLI
3. tester:
		http://localhost
    http://localhost/display-message.php?message=mathias2
    http://localhost:8080  // phpmyadmin (user: root, password: pw)
    http://localhost/insert_to_db.php


Tutos:
https://www.youtube.com/watch?v=xgFu26FWx5Y&ab_channel=Abstractprogrammer
https://www.youtube.com/watch?v=2ygog4MHXws&t=806s


Docker de A à Z :
===============

First Basic step, create a simple container :   
			
			docker run -d -p 80:80 --name php-con php:apache

			// options: -i keep CLI open, -t  Allocate a pseudo-TTY, bin/bash is the program that will be executed

Then enter this container :

			docker exec -it php-cont /bin/bash    

			root@1aa0588f7294:/var/www/html# apt update    // update the packages

			root@1aa0588f7294:/var/www/html# apt install nano   // install editor Nano and create a index file for Apache to serve HTTP requests

			nano index.php

					<?php
						phpinfo();
					?>

			save (ctrl+o then enter) and exit nano (ctrl+x)

Go to http:localhost or http://localhost:80 and check the PHP version and Apache version


This container is not persistent, this means if we stop it (docker stop php-con) 
and relaunch with docker run -d -p 80:80 --name php-cont php:apache, our index.php is disappeared.

To make a persistent container we need the -v flag (volume), 
and map a local folder on our Windows host to the container /var/www/html folder :

- create a folder dotdev in the host (windows), and an index.php 

- remove all stopped containers  'docker container prune'

- docker run -d -p 80:80 -v C:/Users/matha/OneDrive/Desktop/PHP_WS/dotdev:/var/www/html --name php-cont php:apache

- http://localhost

- Now we can stop the container (docker stop php-con or stop it in Docker Desktop)
	and restart it and retrieve our persisted index page


Create a MySQL container 
------------------------

docker run -d -v C:/Users/matha/OneDrive/Desktop/PHP_WS/DB:/var/lib/mysql --name mysql-con -e MYSQL_ROOT_PASSWORD=pw mysql 

// -d detach, -e set environment variables, -p ports

To connect the mysql-con to our php-con we need its IP address :

docker inspect mysql-con | findstr IPAddr   // output : 172.17.0.3

And finally create a DB and some table, rows.
Let's do that with a PHPMYADMIN container: 


Create a PHPMYADMIN container and connect it to our new mysql server
--------------------------------------------------------------------

docker run -d --name phpmyadmin -e PMA_HOST=172.17.0.3 -p 8080:80 phpmyadmin

open http:localhost:8080     (user root - password pw)

create a new db and table in phpmyadmin

Create the file insert_to_db.php in the dotdev folder

then go to http://localhost/insert_to_db.php 

!! this will give an error because the mysqli php extension is not yet installed

we could install the mysqli extension manually in the mysql container with docker exec bash command

but if we do that, each time we restart the container, we will have to install again the mysql php extension

because everything we install localy in the container is temp, 
the persistent solution is to create a Dockerfile (no extension) at the root of the project with content :
        
        FROM php:apache
        RUN docker-php-ext-install mysqli
        EXPOSE 80

Then remove our php:apache container :

	docker container ls   
	docker stop php-con
	docker container prune             // this will remove the stopped containers (e.g. php-con) but not the others (mysql-con and phpmyadmin)

	note : You can also stop and remove containers via Docker Desktop.

Create an new php apache image with the php mysqli extension, named 'apache-mysqli' build with our new dockerfile in current folder (.) :

	docker build -t apache-mysqli .    // -t = tag

Create the new container based on this new image :

	docker run -d -p 80:80 -v C:/Users/matha/OneDrive/Desktop/PHP_WS/dotdev:/var/www/html  --name php-con apache-mysqli

since we use a VOLUME (-v), the php files are available right away, go again to localhost/insert_to_db.php



Now We can streamline this whole process by creating and using a docker-compose.yml file !


delete all containers (mysql-con, phpmyadmin, php-con) in Docker Desktop and execute the docker-compose.yml

docker compose up -d

3. Explications du docker-compose.yml :

services:                           // les 3 containers
  apache-php:                       // le nom que je décide de donner à mon premier container/service
    image: apache-mysqli            // ce container/service sera monté avec l'image (que je baptise apache-msqli) que je build avec le Dockerfile qui, lui, pull l'image php:apache (php 8 + Apache) de la registry (hub.docker.com) et y installe/active l'extension php msqli
    build:
      context: .
      dockerfile: Dockerfile
    volumes:
      - D:/Projects/docker-project/dotdev:/var/www/html         // mapping du folder web local au folder root de Apache dans le container 
    ports:
      - "80:80"
    networks:
      - app-network                         // les containers qui partagent un réseau leur permettent de s'accéder via leur nom de service (aka apache-php, mysql, phpmyadmin) 

  mysql:
    image: mysql                            // ce container sera monté avec l'image "mysql" de la registry (hub.docker.com)
    volumes:
      - D:/Projects/docker-project/DB:/var/lib/mysql
    restart: always
    environment:
      MYSQL_ROOT_PASSWORD: pw
    networks:
      - app-network

  phpmyadmin:
    image: phpmyadmin
    ports:
      - "8080:80"
    environment:
      PMA_HOST: mysql
    depends_on:
      - mysql
    networks:
      - app-network

networks:
  app-network:
    driver: bridge
