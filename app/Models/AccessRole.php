<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;

class AccessRole extends SpatieRole
{
    protected $table = 'access_roles';

    protected $fillable = ['name', 'guard_name', 'description'];
}
