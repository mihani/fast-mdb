# Database migrations
./bin/console doctrine:migrations:migrate --no-interaction

# Frontend build
npm install && ./node_modules/.bin/encore production
./bin/console ckeditor:install --clear=drop
./bin/console assets:install --symlink --relative $CC_WEBROOT
