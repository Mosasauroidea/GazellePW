s/('SQLHOST', *')localhost/\1mysql/
s/('SPHINX(QL)?_HOST', *')(localhost|127\.0\.0\.1)/\1sphinxsearch/
s|('host' *=>) *'unix:///var/run/memcached.sock'(, *'port' *=>) *0|\1 'memcached'\2 11211|
