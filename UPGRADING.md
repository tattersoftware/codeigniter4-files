# Upgrade Guide

## Version 2 to 3
***

* Switches to `Tatter\Preferences` for managing persistent settings; read more below
* Drops `Tatter\Audits` as a dependency and adds it as a suggestion; read more below
* Access rights are now handled via Config file; see [Tatter\Permits](https://github.com/tattersoftware/codeigniter4-permits) for more information
* The package dependency for `Tatter\Handlers` (via `Exports` and `Thumbnails`) has had a major update; please read the Upgrade Guides for those repos if you have any custom handlers or extensions

### `Settings` Migration

`Preferences` relies on `CodeIgniter\Settings` instead of the abandoned `Tatter\Settings`, but
both packages had migrations for a `settings` database table and the schemas are not compatible.
You should archive any global and user-specific settings you would like to keep, then drop
or rename the old table. You may also need to update your `migrations` table in case your
project complains about a "gap in the migrations". Read more on the migration process at the
[Tatter\Settings](https://github.com/tattersoftware/codeigniter4-settings) repo.

### Audits

In order to simplify this library `Tatter\Audits` is no longer included by default. If you
want that level of user-specific logging to database changes then you may install the package
and provide a model extension with the events to reenable. You may also need to update your
`migrations` table in case your project complains about a "gap in the migrations".

Installation:
```bash
composer require tatter/audits
php spark migrate --all
```

Example model file in **app/Models/FileModel.php**:
```php
<?php

namespace App\Models;

use Tatter\Audits\Traits\AuditsTrait;
use Tatter\Files\Models\FileModel as BaseModel;

class FileModel extends BaseModel
{
    use AuditsTrait;

    // Audits
    protected $afterInsert = ['auditInsert'];
    protected $afterUpdate = ['auditUpdate'];
    protected $afterDelete = ['auditDelete'];
}
```
