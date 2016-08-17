#!/bin/bash
export IFS='
'

DOMAIN=$1
if [ "$DOMAIN" == "" ]
then
	echo "Please specify a domain name"
	echo "./insert_payment_gateway.sh <DOMAIN>"
	exit
fi

echo "INSERT INTO tShopSetting (name,value,type,category,description,is_hidden,shop_id)  SELECT 'payment_gateway','PAYUPAISA|PAYU|DIRECPAY|EBS|ZAAKPAY|PAYPAL','gateway','GENERAL','The payment gateway name',0,shop_id from tShop where domain='$DOMAIN';" | mysql -u shopnix -psnix1233 shopnix

echo "INSERT INTO tShopSetting (name,value,type,category,description,is_hidden,shop_id)  SELECT 'payment_gateway_merchant_id','CHANGEME','Merchant ID','GENERAL','Your payment gateway merchant ID',0,shop_id from tShop where domain='$DOMAIN';" | mysql -u shopnix -psnix1233 shopnix


echo "INSERT INTO tShopSetting (name,value,type,category,description,is_hidden,shop_id)  SELECT 'payment_gateway_merchant_salt','CHANGEME','Merchant Salt','GENERAL','Your payment gateway merchant salt',0,shop_id from tShop where domain='$DOMAIN';" | mysql -u shopnix -psnix1233 shopnix


echo "INSERT INTO tShopSetting (name,value,type,category,description,is_hidden,shop_id)  SELECT 'payment_gateway_live_mode','0','bool','GENERAL','Enable or disable live mode',0,shop_id from tShop where domain='$DOMAIN';" | mysql -u shopnix -psnix1233 shopnix




