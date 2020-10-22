# Tatter\Files
File uploads and management, for CodeIgniter 4

[![](https://github.com/tattersoftware/codeigniter4-files/workflows/PHPUnit/badge.svg)](https://github.com/tattersoftware/codeigniter4-files/actions?query=workflow%3A%22PHPUnit%22)
[![](https://github.com/tattersoftware/codeigniter4-files/workflows/PHPStan/badge.svg)](https://github.com/tattersoftware/codeigniter4-files/actions?query=workflow%3A%PHPStan%22)

## Quick Start

1. Install with Composer: `> composer require tatter/files`
2. Migrate the database: `> php spark migrate -all`
2. Seed the database: `> php spark db:seed "Tatter\Files\Database\Seeds\FileSeeder"`
3. Start managing files: https://example.com/files

![image](https://user-images.githubusercontent.com/17572847/96811765-ff82c500-13e9-11eb-9f1d-c9461ef1a438.png)
![image](https://user-images.githubusercontent.com/17572847/96811782-00b3f200-13ea-11eb-9f39-df56362e1d2b.png)
![image](https://user-images.githubusercontent.com/17572847/96811800-01e51f00-13ea-11eb-8a2d-f06ae5dff469.png)

## Features

The Files module is a self-contained set of routes and functions that adds uploading and
CRUD controls to any project. It uses [DropzoneJS](https://www.dropzonejs.com) for
drag-and-drop uploads, and supports a number of extensions for generating file thumbnails
and exporting files to various destinations.

## Installation

Install easily via Composer to take advantage of CodeIgniter 4's autoloading capabilities
and always be up-to-date:
* `> composer require tatter/files`

Or, install manually by downloading the source files and adding the directory to
`app/Config/Autoload.php`.

Once the files are downloaded and included in the autoload, run any library migrations
to ensure the database is setup correctly:
* `> php spark migrate -all`

Finally, run the seeder to install necessary database settings:
`php spark db:seed "Tatter\Files\Database\Seeds\FileSeeder"`

**NOTE**: If your project is part of a tracking repository you probably want to add the file
storage to your **.gitignore**
```
writable/files/*
!writable/files/index.html
```

## Configuration (optional)

The library's default behavior can be altered by extending its config file. Copy
**examples/Files.php** to **app/Config/** and follow the instructions
in the comments. If no config file is found in **app/Config** the library will use its own.

## Usage

Default routes:
* **files/index** - If user is allowed `mayList()` then shows all files, otherwise tries to fall back to the current logged in user
* **files/user/{userId}** - Shows files for a single user; if no user ID is supplied it defaults to the current logged in user
* **files/thumbnail/{fileId}** - Displays the thumbnail for a file

CRUD:
* **files/new** - Basic Dropzone form
* **files/upload** - Accepts AJAX upload requests from Dropzone
* **files/delete/{fileId}** - Removes a file
* **files/rename/{fileId}** - Accepts POST data to rename a file

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

## Extending

**Controllers/Files.php** is the heart of the module, using cascading options to choose
which files to display when. This controller has a `setData()` method to allow you to
intercept this process to provide your own settings at any point. Simply extend the
controller to your own and then provide whatever changes you would like, followed
by the `display()` method. E.g.:

```
<?php namespace App\Controller;

class WidgetFiles
{
	public function index($widgetId)
	{
		$this->setData([
			'format' => 'cards',
			'files'  => model(WidgetModel::class)->getFiles($widgetId),
			'layout' => 'manage',
		]);

		return $this->display();
	}
}

```

These are the default options for `setData()`, but you may also supply anything else you
need in your view:

* `source` - The name of the controller method making the call
* `layout` - The view layout to use (see **Config/Files.php**)
* `files` - An array of Files to display
* `selected` - Files to pre-select (for the `select` format)
* `userId` - ID of a user to filter for Files`
* `username` - Display name of the user for the default layout title
* `ajax` - Whether to process the request as an AJAX call (skips layout wrapping)
* `search` - Search term to filter Files
* `sort` - File sort field
* `order` - File sort order
* `format` - Display format for files (cards, list, select, or your own!)
* `perPage` - Number of items to display per page
* `page` - Page number (leave `null` for default Pager handling)
* `pager` - `Pager` instance to handle pagination
* `access` - Whether the files can be modified, "manage" or "display"
* `exports` - Destinations a File may be sent to (see `Tatter\Exports`)
* `bulks` - Bulk destinations for a group of Files (see `Tatter\Exports`)
