#!/bin/bash
export IFS='
'
DOMAIN=ziba.shopnix.org;
WIDTH="610px";
HEIGHT="459px";


echo "update tShopSetting set value='$HEIGHT' where name='home_banner_height' and shop_id=(select shop_id from tShop where domain='$DOMAIN');" | mysql -u shopnix -psnix1233 shopnix
echo "update tShopSetting set value='$WIDTH' where name='home_banner_width' and shop_id=(select shop_id from tShop where domain='$DOMAIN');" | mysql -u shopnix -psnix1233 shopnix
