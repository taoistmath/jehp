#!/bin/bash

# get values from config file
source ./config

# get the new xml
./pullXml.sh -u ${u} -p ${p} -s ${s}

# parse the new xml save group data
php pageWriter.php
