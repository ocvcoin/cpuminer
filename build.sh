#!/bin/bash

if (( $EUID != 0 )); then
    echo "
	Please run as root
	"
    exit
fi



if [ -z "$1" ]; then
  echo "



This script builds the Ocvcoin Cpuminer
(only works on ubuntu) (tested ubuntu versions: 22)

Type Target:

1 - Build Experimental Miner (much faster)(work on all CPUs)

2 - Build Standalone Miner (If you have very low share rate with first option, try this option) (x86 based 64 BIT CPU REQUIRED)

"




read varname
else
  varname="$1"
fi





if [[( "$varname" != "1" ) && ( "$varname" != "2" )]]; then
    echo "
	
	Incorrect! You must enter 1 or 2
	
	"
    exit
fi




cd ~

rm -rf ocvcoin_cpuminer
mkdir ocvcoin_cpuminer

cd ocvcoin_cpuminer





export DEBIAN_FRONTEND=noninteractive

set -e

apt update
apt -y install  git automake  libcurl4-openssl-dev
 

set +e


git clone https://github.com/ocvcoin/ocv2_algo.git

cd ocv2_algo




if ((( $varname == "1" ))); then

	bash build_experimental.sh 1
    
fi

if ((( $varname == "2" ))); then

	bash build.sh 1
    
fi

cd ..

git clone https://github.com/ocvcoin/cpuminer.git

cd cpuminer

bash autogen.sh


./configure CFLAGS="-I/usr/local/include -L/usr/local/lib -locv2" LDFLAGS="-I/usr/local/include -L/usr/local/lib -locv2" LIBS="-I/usr/local/include -L/usr/local/lib -locv2"


make

chmod +x minerd

echo "


	You can start mining via this command:


	/$PWD/minerd -a ocv2 -o stratum+tcp://fi.mining4people.com:3376 -u YourOcvcoinAddressHere -p x
	
	
	(Dont forget to change YourOcvcoinAddressHere)


"
