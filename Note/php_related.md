1、apache 多版本php (使用fastcgi)
----------------

设置默认php-fastcgi        
```html
LoadModule fcgid_module modules/mod_fcgid.so        
<IfModule fcgid_module>        
    Include conf/extra/httpd-fcgid.conf        
    FcgidInitialEnv PHPRC "F:/phpStudy/php/php-5.6.27-nts/"        
    AddHandler fcgid-script .php        
    FcgidWrapper "F:/phpStudy/php/php-5.6.27-nts/php-cgi.exe" .php        
</IfModule>       
```

在apache的vhosts.conf配置文件添加：（为每个项目指定php版本）
```html
<VirtualHost *:80>        
    FcgidInitialEnv PHPRC "F:/phpdev/php-7.1.26"        
    FcgidWrapper "F:/phpdev/php-7.1.26/php-cgi.exe" .php        
    ServerName manage.me        
    ServerAlias manage.me        
    ServerAdmin manage.me@bbc.me        
    DocumentRoot "F:\phpdev\www\manager\public"        
    ErrorLog "logs/manage.me-error.log"        
    CustomLog "logs/manage.me-access.log" common        
</VirtualHost>
```

2、
----------------
