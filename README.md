# PDNS Manager
 
[PDNS Manager](https://pdnsmanager.org) is a simple yet powerful free administration tool for the Powerdns authoritative nameserver. It supports master, native and slave zones.

PNDS Manager was developed from scratch to achieve a user-friendly
and pretty looking interface.

PDNS Manager also features a powerful API to set records programatically.
This can be used e.g. for a dynamic DNS service, but also to obtain certificates from [Let's Encrypt](https://letsencrypt.org/) via the dns-01 challenge.

PDNS Managers Backend is written in PHP using [Slim Framework](https://www.slimframework.com/). The backend uses a MySQL/Maria DB database. The database is also used by Powerdns using the pdns-backend-mysql backend. The Frontend is based on [Angular](https://angular.io/) and [Bootstrap](https://getbootstrap.com/).

PDNS Manager also features a plugin API to support different session caches or authentication strategies. If you want to contribute a new plugin here feel free to contact me.

## More information
You can find more information and documentation as well as contact information on [pdnsmanager.org](https://pdnsmanager.org). There are also some tutorials to get you quickly up and running.

## Contribute
If you are looking for a new feature or you found a bug, feel free to create a pull request or open a issue.
