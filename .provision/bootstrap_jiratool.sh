#!/usr/bin/env bash

#nginx
sudo cp ~/code/jira-tool/.provision/nginx/nginx.conf /etc/nginx/sites-available/jiratools.conf
sudo chmod 644 /etc/nginx/sites-available/jiratools.conf
sudo ln -s /etc/nginx/sites-available/jiratools.conf /etc/nginx/sites-enabled/000-jiratools
sudo mkdir /srv/www
sudo ln -s ~/code/jira-tool/ /srv/www/jira-tool
sudo service nginx start

#app
echo "Dependencies Install"
cd ~/code/jira-tool
composer install

#database
echo "DB Migrations"
mysql -u homestead -psecret -e "CREATE DATABASE jira_tools;"
/home/vagrant/code/jira-tool/vendor/bin/phinx migrate
/home/vagrant/code/jira-tool/vendor/bin/phinx seed:run

##### optional
 cat ~/code/jira-tool/.provision/ssh/mnobrega_key.pub >> ~/.ssh/authorized_keys