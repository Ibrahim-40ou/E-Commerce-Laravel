<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $email
 * @property string $code
 * @property \Illuminate\Support\Carbon $expires_at
 * @property bool $is_verified
 * @property int $attempts
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailVerification newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailVerification newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailVerification query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailVerification whereAttempts($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailVerification whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailVerification whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailVerification whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailVerification whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailVerification whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailVerification whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailVerification whereIsVerified($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailVerification whereUpdatedAt($value)
 * @mixin \Eloquent
 */
#[Fillable(['email', 'code', 'expires_at', 'is_verified', 'email_verified_at'])]
#[Hidden(['attempts'])]
class EmailVerification extends Model
{
    public function casts(): array
    {
        return ['expires_at' => 'datetime', 'is_verified' => 'boolean', 'email_verified_at' => 'datetime'];
    }
}
