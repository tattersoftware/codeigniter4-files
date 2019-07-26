# Tatter\Files
Job task control through dynamic workflows, for CodeIgniter 4

## Quick Start

1. Install with Composer: `> composer require tatter/files`
2. Update the database: `> php spark migrate:latest -all`
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
* `> php spark migrate:latest -all`

**Pro Tip:** You can add the spark command to your composer.json to ensure your database is
always current with the latest release:
```
{
	...
    "scripts": {
        "post-update-cmd": [
            "composer dump-autoload",
            "php spark migrate:latest -all"
        ]
    },
	...
```

## Configuration (optional)

The library's default behavior can be altered by extending its config file. Copy
**bin/Files.php** to **app/Config/** and follow the instructions
in the comments. If no config file is found in **app/Config** the library will use its own.

## Usage

Default routes:
* **files/index** - Shows all files; the controller will check `mayManage()` to determine if files are read-only or editable
* **files/manage** - Show all files; files are editable
* **files/user** - Shows files for a single user; if no user ID is supplied it defaults to the current logged in user; files are editable

Additional views:
* **Views/files/cards/select** - Can be used to create selectable files, say as part of a form
* **Views/files/list/select** - Can be used to create selectable files, say as part of a form
* **Views/files/dropzone/config** - Default config for Dropzone; 