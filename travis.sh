#!/bin/bash -e

git clone https://github.com/maliknaik16/webthing-php.git
cd webthing-php

# clone the webthing-tester
git clone https://github.com/mozilla-iot/webthing-tester
pip3 install --user -r webthing-tester/requirements.txt

composer install

export PHP_PATH=.
# build and test the single-thing example
php examples/single-thing.php &
EXAMPLE_PID=$!
sleep 5
./webthing-tester/test-client.py --protocol http --host localhost --port 8888 --debug
kill -15 $EXAMPLE_PID

# build and test the multiple-things example
php examples/multiple-things.php &
EXAMPLE_PID=$!
sleep 5
./webthing-tester/test-client.py --protocol http --host localhost --port 8888 --path-prefix "/0" --debug
kill -15 $EXAMPLE_PID
