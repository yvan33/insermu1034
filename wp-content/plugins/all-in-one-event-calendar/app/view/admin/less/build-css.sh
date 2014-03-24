#!/bin/bash

LESSC="lessc --no-color --yui-compress --include-path=."

which lessc >/dev/null

if [[ $? -eq 0 ]]
then
	$LESSC timely-bootstrap.less > ../css/bootstrap.min.css
	exit 0
else
	echo 'Error: lessc not found. Install Node.js then: npm install -g less'
	exit 1
fi
