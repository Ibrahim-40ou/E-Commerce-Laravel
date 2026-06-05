<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['email', 'code', 'expires_at', 'is_verified', 'email_verified_at'])]
#[Hidden(['attempts'])]
class EmailVerification extends Model
{
    public function casts(): array
    {
        return ['expires_at' => 'datetime', 'is_verified' => 'boolean', 'email_verified_at' => 'datetime'];
    }
}
