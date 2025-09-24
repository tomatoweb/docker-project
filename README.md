
This is a ready-to-use Docker containerized App PHP : PHP with mysqli extension, Apache, mysql, phpmyadmin

1. Lancer Docker Desktop
2. taper docker-compose up dans un cmder, et aller sur
     http://localhost/display-message.php?message=mathias2
     http://localhost:8080                                          // phpmyadmin
     http://localhost/insert_to_db.php

3. Explications du docker-compose.yml :

services:                           // les 3 containers
  apache-php:                       // le nom que je décide de donner à mon premier container/service
    image: apache-mysqli            // ce container/service sera monté avec l'image (que je baptise apache-msqli) que je build avec le Dockerfile qui, lui, pull l'image php:apache (php 8 + Apache) de la registry (hub.docker.com) et y installe/active l'extension php msqli
    build:
      context: .
      dockerfile: Dockerfile
    volumes:
      - C:/Users/matha/OneDrive/Desktop/docker-project/dotdev:/var/www/html         // mapping du folder web local au folder root de Apache dans le container 
    ports:
      - "80:80"
    networks:
      - app-network                         // les containers qui partagent un réseau leur permettent de s'accéder via leur nom de service (aka apache-php, mysql, phpmyadmin) 

  mysql:
    image: mysql                            // ce container sera monté avec l'image "mysql" de la registry (hub.docker.com)
    volumes:
      - C:/Users/matha/OneDrive/Desktop/docker-project/DB:/var/lib/mysql
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

-----------------------------------------------------------------------------------------------------------------

Tutos:
https://www.youtube.com/watch?v=xgFu26FWx5Y&ab_channel=Abstractprogrammer
https://www.youtube.com/watch?v=2ygog4MHXws&t=806s


How To de A à Z :
===============

Create a container      (pour les options : docker run --help)
------------------

docker run -d -p 80:80 --name php-con php:apache    // -d détache (run in background), -p ports, php-con is the name I give to the container ("con" for container)

docker exec -it php-con /bin/bash    // -i keep CLI open, -t  Allocate a pseudo-TTY,  bin/bash is the program that will be executed

root@1aa0588f7294:/var/www/html# apt update  // update the packages

root@1aa0588f7294:/var/www/html# apt install nano

nano index.php

		<?php
             phpinfo();
		?>

Browse localhost:80

!! But this way our container is not persistent, if we close it, our index.php will disappear.

To make a persistent container we need the -v flag, and map a local (host) folder to a container's folder
so, let's create a folder Dotdev in the current project (not in the php-con container) and map it to a container :

We can now create a new container, but first remove all stopped containers
docker container prune
docker run -d -p 80:80 -v C:/Users/matha/OneDrive/Desktop/docker-project/dotdev:/var/www/html --name php-con php:apache   // -d run container in background, -p ports, -v volume


Now we have a working PHP server on http://localhost/display-message.php?message=mathias  (80 as default http port)


docker stop php-con
docker start php-con   // remember we need first docker desktop to be started


Add MySQL
---------
docker run -d -v C:/Users/matha/OneDrive/Desktop/docker-project/DB:/var/lib/mysql --name mysql-con -e MYSQL_ROOT_PASSWORD=pw  mysql

// to access the mysql-con container from another container (php-con), we need its IP address (172.17.0.3)
docker inspect mysql-con | findstr IPAddr

// Create a phpmyadmin container
docker run -d --name phpmyadmin -e PMA_HOST=172.17.0.3 -p 8080:80 phpmyadmin       // -d detach, -e environment, -p ports
test http:ocalhost:8080/ (root pw)

create some new table with phpmyadmin

lets try to use PHP to use MySQL by creating insert_to_db.php in dotdev folder
then go to localhost:80/insert_to_db.php 
this will give an error because the mysqli php extension is not yet installed
we could install the mysqli extension by login in the container and install it
but if we do that, if we have to restart the container, we will have to again install the mysql extension
as everything we install localy in the container is temporary
that's why we do that by creating a Dockerfile 

create the Dockerfile 

Create a new IMAGE based on this Dockerfile
docker container ls
docker stop php-con
docker container prune // this will remove the stopped container (php-con) but not the others (mysql-con and phpmyadmin)
docker build -t apache-mysqli .    // -t tag, apache-mysqli is the name I give to this image build, . is the folder of the Dockerfile

// and create the new container based on this new image
docker run -d -p 80:80 -v C:/Users/matha/OneDrive/Desktop/docker-project/dotdev:/var/www/html  --name php-con apache-mysqli

// since we use a VOLUME (-v) the php files are available right away, go again to localhost/insert_to_db.php

// We can streamline this whole process by creating and using a docker-compose.yml file

// when docker-compose.yml is created, delete all containers (mysql-con, phpmyadmin, php-con) in Docker Desktop 
// and execute
docker compose up -d