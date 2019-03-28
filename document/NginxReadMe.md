Install Nginx
----
sudo yum install nginx

#FPM Required
sudo yum install php55w-fpm

#Configure
 * open port 8080
 * vi /etc/sysconfig/iptables
 * Add following line 
        #Nginx
        -A INPUT -m state --state NEW -m tcp -p tcp --dport 8080 -j ACCEPT

# .conf file /etc/nginx/conf.d/virtual.conf

```` 
 server {
    listen 8080;
    listen staging.neon-soft.com:8080;
    root /home/www/staging.neon.nginx/public;
    index  index.php index.html index.htm;
    server_name  staging.neon-soft.com;

    location / {
        try_files $uri $uri/ /index.php?$query_string;        
    }

  
    location ~ \.php$ {
        include /etc/nginx/fastcgi_params;
        fastcgi_pass  127.0.0.1:9000;
        fastcgi_index index.php;
        fastcgi_param  SCRIPT_FILENAME /home/www/staging.neon.nginx/public$fastcgi_script_name;
    }

}
````
 
#Permission
chmod -R 0777 /home/www/staging.neon.nginx

#Services
service php-fpm start
service nginx start

