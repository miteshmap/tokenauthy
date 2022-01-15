# TokenAuthy
Authenticate and login user with token


Functionality
-------------
The module provides token based authentication. On module installation it creates a field for user, and generates token for all existing users.
Whenever an account gets created it creates an auth token for that user.


Configuration
-------------
- The module creates "field_auth_token" field for user.
- Provides a config "tokenauthy.settings" with two configurable keys
  - _**token_size**_: **32** (default token length) 
  - _**autogenerate**_: **true** (default to generate token for user on user creation.)
- Settings can be updated using drush command: `drush cset tokenauthy.settings token_size 16`
- Set **autogenerate** to false, if you don't want to generate token automatically.

Usage
-----
- The module provides a way to login using the token generated for each user.
- On any page you have to add a query string: `?authtoken=b4621382faddf23lkjvuysdftbadsreb`
- this will log you in with the user that matches with the passed token.
