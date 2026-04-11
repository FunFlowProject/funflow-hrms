<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ContractType;
use App\Enums\EmployeeStatus;
use App\Enums\SystemRole;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

/**
 * User Model
 *
 * Represents a user in the system.
 *
 * @property int $id
 * @property string $full_name User's full name
 * @property string $email User's email address
 * @property string|null $username User's username
 * @property string|null $phone_number User's phone number
 * @property \Carbon\Carbon|null $date_of_birth User's date of birth
 * @property \Carbon\Carbon|null $hire_date User's hire date
 * @property \App\Enums\ContractType $contract_type User's contract type
 * @property \App\Enums\SystemRole $system_role User's system role
 * @property \App\Enums\EmployeeStatus $status User's employment status
 * @property \Carbon\Carbon|null $email_verified_at Email verification timestamp
 * @property \Carbon\Carbon $created_at Creation timestamp
 * @property \Carbon\Carbon $updated_at Update timestamp
 */
class User extends Authenticatable
{
    use HasFactory;
    use Notifiable;
    use HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'full_name',
        'email',
        'username',
        'phone_number',
        'date_of_birth',
        'hire_date',
        'contract_type',
        'system_role',
        'status',
        'email_verified_at',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'date_of_birth' => 'date',
            'hire_date' => 'date',
            'password' => 'hashed',
            'contract_type' => ContractType::class,
            'system_role' => SystemRole::class,
            'status' => EmployeeStatus::class,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    // Get all employee assignments for the user.
    public function assignments(): HasMany
    {
        return $this->hasMany(EmployeeAssignment::class, 'user_id');
    }

    /**
     * Scope a query to only include employees and HR (excluding Admins).
     */
    #[Scope]
    protected function employees(Builder $query): void
    {
        $query->whereIn('system_role', [SystemRole::Employee, SystemRole::Hr]);
    }

    // Get status history entries for this statusable entity.
    public function statusHistories(): MorphMany
    {
        return $this->morphMany(StatusHistory::class, 'statusable')
            ->orderByDesc('recorded_at');
    }

    // Get status history entries performed by this user.
    public function statusChangesMade(): HasMany
    {
        return $this->hasMany(StatusHistory::class, 'actor_id');
    }

    // Get service requests submitted by this user.
    public function serviceRequests(): HasMany
    {
        return $this->hasMany(ServiceRequest::class, 'requester_id');
    }

    // Get service requests handled by this user.
    public function handledServiceRequests(): HasMany
    {
        return $this->hasMany(ServiceRequest::class, 'handled_by');
    }

    // Get documents acknowledged or viewed by the user.
    public function documents(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Document::class, 'document_user')
            ->using(DocumentUser::class)
            ->withPivot(['status', 'acknowledged_at'])
            ->withTimestamps();
    }

    // Get educational objectives assigned to the user.
    public function educationalObjectives(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(EducationalObjective::class, 'educational_objective_user')
            ->using(EducationalObjectiveUser::class)
            ->withPivot(['status', 'progress_notes', 'completed_at'])
            ->withTimestamps();
    }
}