# LDAP integration

Basic LDAP authentication support can be enabled in the configuration file.
LDAP users can login with their credentials and manage PowerDNS zones.
It is possible to restrict the access to users with certain LDAP attributes.

## Configuration Example

In order to enable LDAP support put the following lines into your config-user.php file and adjust the parameters for your setup.

    $config['auth_type'] = 'ldap';
    $config['ldap_uri'] = 'ldap://ldap.example.com/';
    $config['ldap_bind_dn'] = 'uid=admin,ou=users,dc=example,dc=com';
    $config['ldap_bind_pw'] = 'password';
    $config['ldap_base_dn'] = 'dc=example,dc=com';
    $config['ldap_search'] = '(&(uid=%user%)(memberof=cn=dns,ou=groups,dc=example,dc=com))';

Adjust the ldap_search parameter to control which users are allowed to login and to manage PowerDNS zones.

## Known limitations

 * LDAP has to be enabled and configured manually in the configuration file.
 * Either database or LDAP can be used for authentication. Internal authentication will be disabled if LDAP is enabled.
 * LDAP users have either full rights for all zones or are not allowed to login. Use the ldap_search parameter to adjust which users can login.

