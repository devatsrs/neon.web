Install Nginx
----
sudo yum install nginx

#FPM Required
sudo yum install php55w-fpm

#Configure
 * open port 8080
 * sudo vi /etc/sysconfig/iptables
 * Add following line 
        #Nginx
        -A INPUT -m state --state NEW -m tcp -p tcp --dport 8080 -j ACCEPT

#/etc/nginx/nginx.conf
sudo vi /etc/nginx/nginx.conf

change port 80 to 8080


# .conf file /etc/nginx/conf.d/neon.conf

```` 
server {

    listen 80;
    listen neon.speakintelligence.com:80;
    
    #listen 8080;
    #listen neon.speakintelligence.com:8080;

    root /var/www/html/speakintelligent.neon/public;

    index  index.php index.html index.htm;

    server_name  neon.speakintelligence.com;



    location / {
        try_files $uri $uri/ /index.php?$is_args$args;
    }

   location /neon.api/public {

        root /var/www/html/speakintelligent.neon/public/neon.api/public;
        rewrite ^/neon.api/public/(.*)$ /$1 break;
        try_files $uri $uri/ /index.php?$args;


   }

   location ~ \.php$ {

        set $newurl $request_uri;
        if ($newurl ~ ^/neon.api/public(.*)$) {
                set $newurl $1;
                root /var/www/html/speakintelligent.neon/public/neon.api/public;
        }

        include /etc/nginx/fastcgi_params;
        fastcgi_pass  127.0.0.1:9000;
        fastcgi_index index.php;
        fastcgi_param REQUEST_URI $newurl;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;

    }

}
````
 
#Permission
chmod -R 0777 /home/www/staging.neon.nginx

#Services
service php-fpm start
service nginx start

