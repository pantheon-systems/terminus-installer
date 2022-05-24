#!/bin/bash

set -ex

echo "Delete and recreate folder as needed..."
rm -r ../installer-results
mkdir ../installer-results

echo "Install terminus in given folder..."
./bin/installer install --install-dir=../installer-results | grep "File downloaded successfully"
../installer-results/terminus --version

echo "Install terminus in given folder and remove outdated..."
./bin/installer install --install-dir=../installer-results --remove-outdated | grep "File downloaded successfully"
../installer-results/terminus --version

echo "Install specific terminus version..."
./bin/installer install --install-dir=../installer-results --version=3.0.4 --remove-outdated | grep "File downloaded successfully"
../installer-results/terminus --version | grep "3.0.4"

echo "Install without removing outdated..."
./bin/installer install --install-dir=../installer-results | grep "Nothing to do"
../installer-results/terminus --version | grep "3.0.4"

echo "Install without removing outdated..."
rm ../installer-results/terminus
./bin/installer install --install-dir=../installer-results | grep "File downloaded successfully"
../installer-results/terminus --version | grep -v "3.0.4"