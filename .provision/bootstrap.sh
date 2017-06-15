#!/usr/bin/env bash

# switch to Portuguese keyboard layout
sudo sed -i 's/"us"/"pt"/g' /etc/default/keyboard
sudo DEBIAN_FRONTEND=noninteractive apt-get install -y console-common

# set timezone to German timezone
echo "Europe/Lisbon" | sudo tee /etc/timezone
sudo dpkg-reconfigure -f noninteractive tzdata