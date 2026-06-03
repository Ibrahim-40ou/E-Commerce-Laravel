<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['email', 'code', 'expires_at', 'is_verified'])]
class EmailVerification extends Model
{
    public function casts(): array
    {
        return ['expires_at' => 'datetime', 'is_verified' => 'boolean'];
    }
}
