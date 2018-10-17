FROM debian:9

SHELL ["/bin/bash", "-exo", "pipefail", "-c"]

RUN apt-get update ;\
	DEBIAN_FRONTEND=noninteractive apt-get install --no-install-recommends --no-install-suggests -y \
	apache2 icingaweb2 php7.0-{intl,imagick,mysql} locales ;\
	apt-get clean ;\
	rm -vrf /var/lib/apt/lists/* /etc/icingaweb2 ;\
	a2dissite 000-default

ADD --chown=www-data:icingaweb2 . /usr/share/icingaweb2/modules/masifupgrader

COPY --from=ochinchina/supervisord:latest /usr/local/bin/supervisord /usr/local/bin/

RUN ln -vs /usr/share/icingaweb2/modules/masifupgrader/_docker/supervisord.conf /etc/ ;\
	ln -vs /usr/share/icingaweb2/modules/masifupgrader/_docker/apache2-site.conf /etc/apache2/sites-available/icingaweb2.conf ;\
	a2ensite icingaweb2 ;\
	ln -vs /usr/share/icingaweb2/modules/masifupgrader/_docker/php-icingaweb2.ini /etc/php/7.0/apache2/conf.d/99-icingaweb2.ini ;\
	ln -vs /usr/share/icingaweb2/modules/masifupgrader/_docker/icingaweb2 /etc/ ;\
	install -o root -g icingaweb2 -m 02770 -d /var/log/icingaweb2 ;\
	install -o www-data -g icingaweb2 -m 02770 -d /etc/icingaweb2/enabledModules ;\
	ln -vs /usr/share/icingaweb2/modules/masifupgrader /etc/icingaweb2/enabledModules/ ;\
	perl -pi -e 'if (!%locales) { %locales = (); for my $d ("", "/modules/*") { for my $f (glob "/usr/share/icingaweb2${d}/application/locale/*_*") { if ($f =~ m~/(\w+)$~) { $locales{$1} = undef } } } } s/^# ?// if (/ UTF-8$/ && /^# (\w+)/ && exists $locales{$1})' /etc/locale.gen ;\
	locale-gen -j 4

CMD ["/usr/local/bin/supervisord", "-c", "/etc/supervisord.conf"]
