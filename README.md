# Tatter\Files
File uploads and management, for CodeIgniter 4

## Quick Start

1. Install with Composer: `> composer require tatter/files`
2. Update the database: `> php spark migrate -all` `> spark db:seed "Tatter\Files\Database\Seeds\FileSeeder"`
3. Start managing files: https://[yourdomain.com]/files

## Features

The Files module is a self-contained set of routes and functions that adds uploading and
CRUD controls to any project. It uses [DropzoneJS](https://www.dropzonejs.com) for
drag-and-drop uploads, and supports a number of extensions for directing files to other
locations (WIP).

## Installation

Install easily via Composer to take advantage of CodeIgniter 4's autoloading capabilities
and always be up-to-date:
* `> composer require tatter/files`

Or, install manually by downloading the source files and adding the directory to
`app/Config/Autoload.php`.

Once the files are downloaded and included in the autoload, run any library migrations
to ensure the database is setup correctly:
* `> php spark migrate -all`

**Pro Tip:** You can add the spark command to your composer.json to ensure your database is
always current with the latest release:
```
{
	...
    "scripts": {
        "post-update-cmd": [
            "@composer dump-autoload",
            "php spark migrate -all"
        ]
    },
	...
```

Finally, run the seeder to install necessary database settings:
`spark db:seed "Tatter\Files\Database\Seeds\FileSeeder"`

**NOTE**: If your project is part of a tracking repository you probably want to add the file
storage to your **.gitignore**
```
writable/files/*
!writable/files/index.html
```

## Configuration (optional)

The library's default behavior can be altered by extending its config file. Copy
**bin/Files.php** to **app/Config/** and follow the instructions
in the comments. If no config file is found in **app/Config** the library will use its own.

## Usage

Default routes:
* **files/index** - If user is allowed `mayList()` then shows all files, otherwise tries to fall back to the current logged in user
* **files/user** - Shows files for a single user; if no user ID is supplied it defaults to the current logged in user

Available formats:
* **?format=cards** - Default view with thumbnail on responsive layout
* **?format=list** - An efficient list of files in table format
* **?format=select** - Can be used to create selectable files, e.g. as part of a form

## Access control

This library uses **Tatter\Permits** to control access to files, both generally (list, create)
and specifically per user or group. The super-permit `mayAdmin()` can be added to a user or
group for global file access.

By default the **files/** routes are available as soon as the module is installed. In most
cases you will want to use Route Filters to restrict some or all of the routes.
