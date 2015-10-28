#!/bin/bash
PWD=`pwd`

if [ `basename $PWD` == "build" ]; then
	echo "Wrong directory, run this from parent directory.";
	exit;
fi

phpdoc -d lib -f \*.php -t docs -ti "phpAPRS Class Documentation" -s 