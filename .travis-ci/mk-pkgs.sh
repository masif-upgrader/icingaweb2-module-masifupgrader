#!/bin/bash

set -e
set -o pipefail
set -x

PKG_NAME='icingaweb2-module-masifupgrader'
PKG_VERSION="$(git describe)"
PKG_VERSION="${PKG_VERSION/v/}"

mkdir -p pkgroot/usr/share/icingaweb2/modules/masifupgrader

cp -r LICENSE application configuration.php library module.info public pkgroot/usr/share/icingaweb2/modules/masifupgrader

rm -f pkgpayload.tar

pushd pkgroot

tar -cf ../pkgpayload.tar *

popd

for LSBDISTID in Debian Raspbian; do
	fpm -s tar -t deb --log debug --verbose --debug \
		-n "$PKG_NAME" \
		-v "$PKG_VERSION" \
		-a all \
		-m 'Alexander A. Klimov <grandmaster@al2klimov.de>' \
		--description 'The Masif Upgrader UI is a component of Masif Upgrader.
Consult Masif Upgrader'"'"'s manual on its purpose and the UI'"'"'s role in its architecture:
https://github.com/masif-upgrader/manual' \
		--url 'https://github.com/masif-upgrader/icingaweb2-module-masifupgrader' \
		-p "${PKG_NAME}-${PKG_VERSION}-${LSBDISTID}-all.deb" \
		-d icingaweb2 -d php -d php-mysql --no-auto-depends \
		pkgpayload.tar
done
