# Security

Passwords use Laravel hashing. Sessions regenerate after login and are invalidated on logout. Login and registration have named rate limiters. Public registration creates only `partner` users. Admin credentials are never stored in source code; the first admin is created interactively by an Artisan command.

Before production: configure HTTPS, secure cookie settings, SMTP, a dedicated MySQL database, backups, queue supervision, application logs, and Policies for every tenant-owned resource. Scraper API traffic must remain on an internal network. Do not store scraper or SMTP secrets in Git.
