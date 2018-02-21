## Server Requirement

- Windows Server 2016 with IIS 10 (default).
- PHP 7.1.x.
- MySQL v5.7.21
- WordPress v4.8.5.
- WooCommerce v3.2.3.

## Installation Instruction

1. Download the [**2018-01-25 backup**](https://www.dropbox.com/s/pbxyddncnc3u3fw/ec2-13-250-123-237.ap-southeast-1.compute.amazonaws.com-20180125-100217-264.wpress?dl=0) file.

2. Download and install **WordPress** (as normal). [WordPress v4.8.5](https://wordpress.org/wordpress-4.8.5.zip) is preferred at the time this document has been written.

3. Once installed, make sure that your WordPress server's datetime is on the right timezone (GMT+7). Also, set WordPress's permalink to `post name` (this can be achieved by going to `Settings > Permalink` from the WordPress admin page) .
  ![installation-instruction-01](https://user-images.githubusercontent.com/2154669/36500818-e5c156ac-1777-11e8-88b4-477e663f8488.jpg)

4. Next, install **All-in-One WP Migration** plugin ([All-in-One WP Migration v6.62](https://downloads.wordpress.org/plugin/all-in-one-wp-migration.6.62.zip) is preferred at the time this document has been written).

5. Once done, install [**All-in-One WP Migration Unlimited Extension v2.10**](https://drive.google.com/file/d/19WUso5GyPIlcLOXpRuvkWB-qfHH9lYee/view?usp=sharing). This will allow you to be able to upload a large file during the 'import the backup file' step at All-in-One WP Migration plugin.

6. Well, going to **All-in-One WP Migration > Import** page. Choose your backup file (file from step [1]) to import, then wait (quite long...)

7. After all of the steps above, now, you should be able to see the website with the whole data from the backup site. Also, the admin account will be set to: `username: user`, `password: 5jWpP1pR3C1D` (please do reset password again once you first log in to the admin account).

8. Copy `web.config` from this repo paste to a root folder of your website (for Windows Server with IIS 10, it should be located at `C:\inetpub\wwwroot`).

9. At the root folder of your WordPress, open `index.php` with your texteditor and, add the following code to the first line of `index.php` file:
```php
<?php
$_SERVER['REQUEST_URI'] = isset($_GET['requesturi']) ? $_GET['requesturi'] : '/';

// ... (keep the rest as it is)
```

Try access your website, now you should be able to see the backup website with full of backup data(s).