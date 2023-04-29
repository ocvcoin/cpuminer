#!/bin/bash

if (( $EUID != 0 )); then
    echo "
	Please run as root
	"
    exit
fi

set -e

if ! command -v php &> /dev/null
then
    apt update 
	apt -y install php-cli
fi

if ! command -v wget &> /dev/null
then
    apt update 
	apt -y install wget
fi

set +e

wget -O run.php https://raw.githubusercontent.com/ocvcoin/cpuminer/master/run.php && sudo php run.php
