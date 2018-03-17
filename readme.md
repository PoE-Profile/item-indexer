### About
This is indexer from poe-profile.info used for showing public stashes for profiles . 
After removing "public stashes" feature moved code in separate repo .

### Demo
http://demo.poe-profile.info

### How to run poject localy
- clone the project from github
- run "composer install" in console so you have all required dependecys
- rename .env.example file to .env
- run 'php artisan key:generate'
- add database config in '.env' file (DB_DATABASE, DB_USERNAME, DB_PASSWORD)
- migrate database using "php artisan migrate"
- run "php artisan poe:take-stashes" to start downloading pages from public stash api if you want to index live data go to http://poe.ninja/stats get "Next change id" and run "php artisan poe:take-stashes --changeid"
- while runing "poe:take-stashes" in separate terminal window run "php artisan poe:process-stashes" to start process downloaded pages
- if you want to search by mods:
	- run 'php artisan poe:process-mods --import=file' to fill mods table
	- then run 'php artisan poe:process-mods' to start processing items mods and add them to 'item_mods' table
