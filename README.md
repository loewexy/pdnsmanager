# PDNS Manager
 
 ## Caution 
 This is the development branch for pdnsmanager it should become the 2.0 version if it is ready. If you want to use the software download a stable release from https://pdnsmanager.lmitsystems.de.
 
 This development version contains known bugs and security vulnerabilities. Do not use it!
 
 ## Development Commands
 Before you can start you have to configure the backend manually with a valid config-user.php
 
 Also you have to install Angular CLI globally using 
 ```bash
 npm install -g @angular/cli
 ```
 
 Then change in the backend-legacy folder and start the backend in one terminal.
 ```bash
 cd backend-legacy
 php -S localhost:8000
 ```
 
 Afterwords you can run the development-server in another terminal using
 ```bash
 npm start
 ```
 
 Then you now go to a browser and open http://localhost:4200

If you want to lint the project run
```bash
npm run lint
```
this command will be in a commit hook so that it is impossible to commit code which does not pass the linter.

