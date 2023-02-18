# Mini Aspire

## Local Setup :

#### Use sail to setup the project
- ./vendor/bin/sail up

#### Clear the Database
- php artisan db:wipe

#### Migrate Database
- php artisan migrate --seed

#### Generate JWT Secret
- php artisan jwt:secret

#### Clear the cache
- php artisan clear-compiled

#### Generate autoload file
- composer dump-autoload

#### Regenerate cache
- php artisan optimize

#### Admin Login Details
{
"email": "admin@aspire.com",
"password": "123456"
}

#### PostMan Collection: **[Mini Aspire](/mini_aspire.postman_collection.json)**
