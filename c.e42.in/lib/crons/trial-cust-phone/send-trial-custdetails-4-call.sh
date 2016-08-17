#!/bin/bash

cd /var/www/shop.shopnix.in/admin/lib/crons/trial-cust-phone/
export IFS='
'

### Signed up yesterday
echo "select s.domain as store,u.fname as 'Customer Name',u.mobile as 'Phone No',u.email_id,date(s.created_dt) as created_date,ss.value as expiry_date,datediff(ss.value,s.created_dt) as expired_days,datediff(now(),s.created_dt) as trial_day  from tShop s, tUser u, tShopSetting ss where s.shop_id=u.shop_id and s.shop_id=ss.shop_id and u.mobile is not NULL and ss.name='subscription_end_date' having trial_day=0 order by expired_days,s.created_dt;"|  mysql -u shopnix -psnix1233 shopnix | sed 's/\t/,/g' > /tmp/trial-start.csv



### 7th day of trial
echo "select s.domain as store,u.fname as 'Customer Name',u.mobile as 'Phone No',u.email_id,date(s.created_dt) as created_date,ss.value as expiry_date,datediff(ss.value,s.created_dt) as expired_days,datediff(now(),s.created_dt) as trial_day  from tShop s, tUser u, tShopSetting ss where s.shop_id=u.shop_id and s.shop_id=ss.shop_id and u.mobile is not NULL and ss.name='subscription_end_date' having trial_day=6 order by expired_days,s.created_dt;" |  mysql -u shopnix -psnix1233 shopnix | sed 's/\t/,/g' > /tmp/trial-middle.csv





### 12th day of trialth
echo "select s.domain as store,u.fname as 'Customer Name',u.mobile as 'Phone No',u.email_id,date(s.created_dt) as created_date,ss.value as expiry_date,datediff(ss.value,s.created_dt) as expired_days,datediff(now(),s.created_dt) as trial_day  from tShop s, tUser u, tShopSetting ss where s.shop_id=u.shop_id and s.shop_id=ss.shop_id and u.mobile is not NULL and ss.name='subscription_end_date' having trial_day=11 order by expired_days,s.created_dt;" |  mysql -u shopnix -psnix1233 shopnix | sed 's/\t/,/g' > /tmp/trial-end.csv



##### EMAIL
DATE=`date | tr -s  " "  | cut -f 1-3,6 -d " "`
echo "Sending email"
mutt -F ./.muttrc -a /tmp/trial-start.csv -a /tmp/trial-middle.csv -a /tmp/trial-end.csv -s "Shopnix Trial Customers for $DATE" -c avinash@cloudnix.com -- subhrajit@shopnix.in < message.txt

