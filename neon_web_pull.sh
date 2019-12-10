#!/bin/sh

sudo git --git-dir /vol/data/speakintelligent.neon/.git  pull

sudo chown -R neon_sys:apache /vol/data/speakintelligent.neon

sudo find /vol/data/speakintelligent.neon -type d -exec chmod 575 {} +
sudo find /vol/data/speakintelligent.neon -type f -exec chmod 464 {} +

sudo chmod -R 770 /vol/data/speakintelligent.neon/app/storage
sudo chmod -R 770 /vol/data/speakintelligent.neon/public/neon.api/storage

sudo chmod -R 575 /vol/data/speakintelligent.neon/wkhtmltox/bin/wkhtmltopdf
sudo chmod -R 575 /vol/data/speakintelligent.neon/wkhtmltox/bin/wkhtmltopdf

#sudo chown -R neon_sys:apache /vol/data/tmp
#sudo find /vol/data/tmp -type d -exec chmod 775 {} +
#sudo find /vol/data/tmp -type f -exec chmod 775 {} +

echo "success"