This prototype is andassignment which is done utilizing Laravel.

the system already has 2 SQLite databases for testing and the actual system.

to make the schema into DB in case it does not exist please run following codes

==== to run on testing db ====

php artisan migrate --database testing_sqlite

==== to run on actual db ====

php artisan migrate 

to create 100 users run following command (there is no UI for creating users)

php artisan db:seed

to create offers please go to the offers page and create an offer by entering a name and picking a date. just in case date-picker was not wokring properly (2018-05-29) is an example of accepted date format for the system.

then go to the vouchers page and generate vouchers for a selected offer in drop down.


for the API endpoint

there are 2 endpoints as follows:


============== verify ===============

+ to verify the vouchers 

http://voucher-project.test/api/verify/{recipient email}/ {voucher code}

+ to show the list of "valid vouchers" for the given recipient email. (excluding, expired vouchers and used ones)

http://voucher-project.test/api/verify/{recipient email}


============== get ===============


+ simply is a replica for the previous endpoint and lists down the "valid vouchers" for the given recipient email address.


http://voucher-project.test/api/get/{recipient email}

