cd ../
mkdir tmp
./bin/vendor/bin/phpdoc -d ../src -t tmp/ --template=xml --visibility=public
./bin/vendor/bin/phpdocmd tmp/structure.xml .
rm -rf tmp
